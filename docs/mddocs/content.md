---
title: Content model
---

# Content model

MDDocs content is plain Markdown stored in the `docs/` directory. The application does not depend on a database, so documentation is easy to version, move, review, and edit in Git.

## Documentation structure

Each documentation set has its own directory:

```text
docs/
  mddocs/
    .menu.md
    index.md
    configuration.md
    integrations/
      mcp.md
```

`.menu.md` is recommended for real documentation. If it is missing, MDDocs can still render a simple documentation set from `index.md`, `README.md`, `readme.md`, or any other Markdown files in the directory.

For generated navigation, MDDocs uses the first `# Heading` from each file as the page title. If a file has no heading, the title is derived from the file name.

## .menu.md

The leading dot marks `.menu.md` as reserved: MCP tools reject `create_page`/`update_page`/`append_to_page` calls targeting it, and it is excluded from `list_pages`. This keeps a real content page named `menu.md` (documenting an actual menu feature, for example) from colliding with navigation.

`.menu.md` controls:

- the documentation title shown in the sidebar
- page order
- navigation hierarchy
- previous and next page links
- breadcrumb

Example:

```markdown
# MDDocs

- [Overview](index.md)
- [Quick start](quick-start.md)
- Integrations
  - [MCP server](integrations/mcp.md)
```

Files that are not listed in `.menu.md` may still exist, but they will not appear in the main navigation.

## Default page

When opening a documentation root such as `/mddocs`, MDDocs chooses the default page in this order:

1. `index.md`
2. `README.md`
3. `readme.md`
4. the first Markdown file alphabetically

With an explicit `.menu.md`, this still gives you a predictable root page while keeping the menu order under your control.

## Front matter

Pages may use simple front matter:

```markdown
---
title: Quick start
---
```

The `title` value is used as a fallback for the page title and search index.

## Links between pages

Use relative links to `.md` files in Markdown:

```markdown
[Configuration](configuration.md)
[MCP server](integrations/mcp.md)
```

MDDocs rewrites them to web URLs during rendering.

## Assets

Documentation assets belong in the `assets/` directory inside the documentation set:

```text
docs/
  mddocs/
    assets/
      diagram.png
```

Reference them with relative paths from Markdown. The web application serves them through its internal asset route.

## Markdown safety

The Markdown renderer is configured so raw HTML from documentation content is not executed as HTML. This is a practical default for documentation that may be edited by AI or by multiple people on a team.
