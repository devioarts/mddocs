# MCP Server Workflow

Use this reference when an MCP client is connected to a MDDocs server (stdio via `bin/mcp-server.php` or HTTP via `public/mcp.php`) and MDDocs's own tools are available, instead of editing documentation files by hand.

## Available Tools

- `list_documentations`: list documentation roots. Call this first in any session against an unfamiliar server.
- `list_pages`: list Markdown pages inside one documentation root.
- `get_page`: read a page's content, resolved title, and heading outline. Always read a page before editing it.
- `search_docs`: full-text search inside one documentation root. Use it before creating a page to check whether the topic is already covered somewhere else.
- `get_menu` / `update_menu`: read or replace `menu.md`. Update it only when navigation actually changes — a page was added, removed, or renamed.
- `create_page`: create a new page. Fails if the page already exists; use `update_page` or `append_to_page` for existing pages.
- `update_page`: replace a page's full content. Use for a substantial rewrite or when the existing structure is stale throughout.
- `append_to_page`: add Markdown content to the end of an existing page. Prefer this over `update_page` for small, additive changes — it preserves everything else on the page.
- `upload_asset`: upload a base64-encoded file below `assets/`.
- `validate_documentation`: check that every `menu.md` link resolves to an existing page. Run this as the last step of any change instead of manually cross-checking links — see `references/validation.md` for the full manual checklist to use when this tool is not available.
- `rebuild_search_index`: rebuild the JSON search index for one documentation root. The write tools (`create_page`, `update_page`, `append_to_page`, `update_menu`) already rebuild it automatically; call this directly only after documentation files changed outside these tools, for example a direct filesystem edit on the server.

Every write tool keeps a timestamped backup and an audit log entry server-side. This does not replace reviewing the change before reporting it as done.

## Maintaining Existing Documentation

Use this sequence when documentation already exists for the project and the task is to update it rather than create it from scratch:

1. `list_pages` and `get_menu` to see what already exists before assuming a page is missing.
2. Identify what changed in the source project — a specific feature, config key, command, or file — that the docs need to reflect.
3. `search_docs` or `get_page` to find the specific page(s) the change affects. Do not create a new page when an existing one already owns the topic.
4. Prefer `append_to_page` for adding a new section to an otherwise-accurate page. Use `update_page` only when the page's existing structure or claims are wrong throughout, not just missing one section.
5. Update `menu.md` with `update_menu` only if the page set or navigation order actually changed.
6. Run `validate_documentation` before reporting the change as finished.
7. Report which pages were created, appended to, or fully rewritten, and why.

## Working Without an MCP Connection

When no MDDocs MCP server is connected, apply the same discipline manually: read existing files before writing, prefer a targeted edit over a full rewrite when only part of a page is stale, and apply the `references/validation.md` checklist by hand since `validate_documentation` is not available.
