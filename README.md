# MDDocs

MDDocs is a PHP 8.4 documentation viewer for Markdown files, with an MCP server for AI-assisted documentation editing.

Canonical repository: [devioarts/mddocs](https://github.com/devioarts/mddocs)

## Stack

- PHP 8.4
- Composer
- `league/commonmark`
- `mcp/sdk`
- Plain CSS and vanilla JavaScript

## Run locally

```bash
git clone https://github.com/devioarts/mddocs.git
cd mddocs
composer install
composer serve
```

Open `http://127.0.0.1:8080/mddocs`.

## Documentation structure

```text
docs/
  mddocs/
    menu.md
    index.md
    quick-start.md
    integrations/
      mcp.md
```

`menu.md` controls navigation, breadcrumb, and previous/next links.

For very small documentation sets, `menu.md` is optional. If a documentation directory contains only `README.md`, `index.md`, or other Markdown files, MDDocs generates a simple navigation automatically.

## Agent Skill

MDDocs has a portable Agent Skill for Codex, Claude, and other `SKILL.md`-aware agents. It now lives in a separate skills repository:

```text
https://github.com/devioarts/skills/tree/main/mddocs
```

Use it when asking an agent to create or maintain MDDocs-compatible project documentation.

Install it with the [`skills`](https://www.npmjs.com/package/skills) CLI, run ad hoc via `npx`:

```bash
# Claude Code
npx skills add devioarts/skills --skill mddocs -a claude-code

# Codex
npx skills add devioarts/skills --skill mddocs -a codex
```

Add `-g` to install into your user/global skills directory instead of the current project.

Update the skill later with:

```bash
npx skills update mddocs
```

You can also copy the `mddocs` folder directly into your agent's skills directory (`~/.claude/skills/mddocs`, `.claude/skills/mddocs`, `~/.codex/skills/mddocs`, or `.agents/skills/mddocs`). Keep the whole folder together, including `references/`.

Create documentation locally in the project being documented, review it, then publish the finished documentation folder to the MDDocs server and run `composer build-search`.

## MCP stdio server

```bash
php bin/mcp-server.php
```

Client config example:

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

## MCP HTTP endpoint

Endpoint: `/mcp.php`

Optional token:

```bash
MCP_BEARER_TOKEN=long-secret-token
```

## GitHub storage

```bash
DOCS_STORAGE=github
GITHUB_OWNER=owner
GITHUB_REPO=repo
GITHUB_BRANCH=main
GITHUB_DOCS_PATH=docs
GITHUB_TOKEN=github_pat_...
```

Without these variables, the app uses local `docs/` storage.

## HestiaCP deployment

HestiaCP Nginx + PHP-FPM templates are included in:

```text
deploy/hestiacp/
```

Install MDDocs directly in a Hestia domain directory:

```text
/home/<user>/web/<domain>/public_html/
```

Set the Hestia custom document root to:

```text
/home/<user>/web/<domain>/public_html/public
```

Then use the `mddocs` Nginx template. This keeps application files outside the web root.

Direct install from GitHub:

```bash
cd /home/<user>/web/<domain>/public_html
git clone https://github.com/devioarts/mddocs.git .
composer install --no-dev --optimize-autoloader
composer build-search
```

Local upload is also supported: prepare the project locally with Composer, upload the project contents including `vendor/`, and keep `.git/`, `.idea/`, and runtime server files out of the upload.

## Contributing

Repository: [devioarts/mddocs](https://github.com/devioarts/mddocs)

Before opening a pull request, run:

```bash
composer validate --strict
composer lint
composer build-search
```
