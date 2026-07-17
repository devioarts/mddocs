---
title: MCP server
---

# MCP server

MDDocs includes an MCP server so an AI client can read, search, edit, and validate documentation through the same services used by the web application.

## Stdio server

For local MCP clients, use the stdio server:

```bash
composer mcp
```

The command runs:

```bash
php bin/mcp-server.php
```

## Client configuration example

```json
{
  "mcpServers": {
    "mddocs": {
      "command": "php",
      "args": ["/absolute/path/to/mddocs/bin/mcp-server.php"]
    }
  }
}
```

## HTTP MCP endpoint

The HTTP endpoint is available at:

```text
/mcp.php
```

For production or shared environments, set `MCP_BEARER_TOKEN`.

## Available tools

| Tool | Purpose |
| --- | --- |
| `list_documentations` | Lists documentation sets. |
| `list_pages` | Lists Markdown pages in a documentation set. |
| `get_page` | Reads a specific page. |
| `search_docs` | Searches documentation content. |
| `create_page` | Creates a new Markdown page. |
| `update_page` | Replaces an existing page. |
| `append_to_page` | Appends content to a page. |
| `get_menu` | Reads `menu.md`. |
| `update_menu` | Updates `menu.md`. |
| `upload_asset` | Uploads an asset to documentation. |
| `validate_documentation` | Validates links and menu entries. |
| `rebuild_search_index` | Rebuilds the search index. |

## Audit and backups

Edits made through MCP create audit log entries and local backups. This makes it easier to inspect what AI changed and return to a previous file state if needed.
