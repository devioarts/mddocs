---
title: Configuration
---

# Configuration

The main configuration lives in `config/app.php`. Most values can be overridden with environment variables.

## Storage

MDDocs supports two storage modes:

| Mode | Description |
| --- | --- |
| `local` | Reads and writes files directly in the local `docs/` directory. |
| `github` | Reads and writes files through the GitHub Contents API. |

The default mode is `local`.

```bash
DOCS_STORAGE=local
```

## Local storage

Local storage uses:

```text
docs/
```

This mode is ideal for development, local documentation, and repositories where documentation is part of the project.

## GitHub storage

Enable GitHub storage with:

```bash
DOCS_STORAGE=github
GITHUB_OWNER=owner
GITHUB_REPO=repository
GITHUB_BRANCH=main
GITHUB_DOCS_PATH=docs
GITHUB_TOKEN=github_pat_...
```

The token must be allowed to read and write repository contents.

Optional commit author overrides for writes made through GitHub storage:

```bash
GITHUB_COMMITTER_NAME="Docs MCP"
GITHUB_COMMITTER_EMAIL=docs-mcp@example.com
```

These default to `Docs MCP` and `docs-mcp@example.com` when unset.

## MCP HTTP token

The HTTP MCP endpoint can be protected with a bearer token:

```bash
MCP_BEARER_TOKEN=long-secret-token
```

The client then sends:

```http
Authorization: Bearer long-secret-token
```

If `MCP_BEARER_TOKEN` is not set, the HTTP MCP endpoint is not protected by an application-level token. In production, add protection through a reverse proxy, hosting controls, or VPN.

## Web error logging

Unhandled errors in the web application always return HTTP 500. By default the response body only says "Something went wrong. Check the server log for details." and the full exception (message, file, line, trace) is written to `var/log/app-error.log`.

To show the exception message directly in the response instead, for local debugging only:

```bash
APP_DEBUG=1
```

Do not enable `APP_DEBUG` in production; it can expose internal file paths and error details to visitors.

## Runtime directories

MDDocs uses `var/` for runtime data:

| Path | Purpose |
| --- | --- |
| `var/cache/` | search indexes |
| `var/backups/` | backups before AI edits |
| `var/log/` | MCP action audit log and web error log (`app-error.log`) |
| `var/mcp-sessions/` | HTTP MCP session state |
