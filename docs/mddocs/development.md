---
title: Development
---

# Development

MDDocs is intentionally small and has no frontend build step. This keeps local development simple and makes deployment friendly to ordinary PHP hosting.

The canonical repository is:

```text
https://github.com/devioarts/mddocs
```

For local development:

```bash
git clone https://github.com/devioarts/mddocs.git
cd mddocs
composer install
```

## Composer scripts

| Command | Purpose |
| --- | --- |
| `composer serve` | Starts the local web server on `127.0.0.1:8080`. |
| `composer lint` | Checks project PHP files with `php -l`. |
| `composer test` | Runs the PHPUnit test suite. |
| `composer build-search` | Builds the search index for documentation. |
| `composer mcp` | Starts the MCP stdio server. |

## Where the code lives

| Path | Description |
| --- | --- |
| `app/Docs/` | Documentation domain, menu parser, renderer, and search. |
| `app/Http/` | Web router and HTML layout. |
| `app/Mcp/` | MCP tools and server factory. |
| `bin/` | CLI entry points. |
| `public/` | Web root, CSS, JS, and HTTP entry points. |

## Skill installer

The MDDocs Agent Skill is maintained separately in [devioarts/skills](https://github.com/devioarts/skills/tree/main/mddocs), not in this repository. Install or update it with the [`skills`](https://www.npmjs.com/package/skills) CLI:

```bash
# Claude Code
npx skills add devioarts/skills --skill mddocs -a claude-code

# Codex
npx skills add devioarts/skills --skill mddocs -a codex
```

Add `-g` to install into your user/global skills directory instead of the current project. See [Agent skill](integrations/skill.md) for details.

## Recommended change workflow

1. Edit PHP or Markdown files.
2. Run `composer lint`.
3. If PHP logic changed, run `composer test`.
4. If documentation content changed, run `composer build-search`.
5. Run `composer serve`.
6. Manually check main pages, search, and the mobile menu.

## GitHub workflow

The repository includes a GitHub Actions workflow at `.github/workflows/ci.yml`.

It validates Composer metadata, installs dependencies, lints PHP files, runs the PHPUnit test suite, and builds the search index on pushes and pull requests.

## Design principles

- documentation should be fast and readable
- content should remain in Markdown
- navigation should be explicit through `menu.md`
- AI edits should be traceable through the audit log
- production deployment should not require a complex build pipeline
