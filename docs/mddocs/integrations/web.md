---
title: Web endpoints
---

# Web endpoints

The web side of MDDocs is a small PHP application with a single entry point: `public/index.php`.

## URL

| URL | Description |
| --- | --- |
| `/` | Documentation list or redirect to the only documentation set. |
| `/{doc}` | Home page of a documentation set. |
| `/{doc}/{page}` | Specific Markdown page. |
| `/search?doc={doc}&q={query}` | JSON search endpoint. |
| `/_asset/{doc}/{asset}` | Asset stored in documentation. |
| `/mcp.php` | HTTP MCP endpoint. |

## Routing

Apache rewrite rules are in:

```text
public/.htaccess
```

When using another web server, route all unknown requests to:

```text
public/index.php
```

Static files from `public/assets/` are served directly.

## Search endpoint

Example:

```text
/search?doc=mddocs&q=mcp
```

The response is a JSON array of results:

```json
[
  {
    "title": "MCP server",
    "path": "integrations/mcp.md",
    "url": "/mddocs/integrations/mcp",
    "breadcrumb": "Integrations / MCP server",
    "excerpt": "...",
    "score": 40
  }
]
```
