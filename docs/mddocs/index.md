---
title: Overview
---

# MDDocs

MDDocs is a small PHP application for publishing documentation from Markdown files. Its purpose is to keep documentation as plain text, render it as a clean web experience, and expose the same content to AI tools through an MCP server.

Canonical repository:

```text
https://github.com/devioarts/mddocs
```

## What it is for

MDDocs is a good fit for project documentation, internal guides, plugin documentation, API references, release notes, and technical handoffs. The content stays in the repository, so it can be versioned, reviewed in pull requests, and edited with the same workflow as code.

## Core features

- Markdown rendering through CommonMark
- navigation controlled by `menu.md`
- breadcrumb, sidebar navigation, and page outline
- full-text search across documentation
- local storage in the `docs/` directory
- optional GitHub storage through the GitHub Contents API
- MCP stdio server for AI clients
- MCP HTTP endpoint with an optional bearer token
- portable Agent Skill for Codex, Claude, and other skill-aware agents
- simple frontend with no build step

## Project layout

```text
app/        PHP application code
bin/        CLI scripts
config/     configuration
docs/       Markdown documentation
public/     web root
skills/     portable Agent Skills
var/        cache, logs, and backups
```

## Documentation model

Each documentation set is a separate directory in `docs/`. The directory name is used in the URL.

```text
docs/
  mddocs/
    menu.md
    index.md
    quick-start.md
```

The main MDDocs documentation URL is:

```text
/mddocs
```

When there is only one documentation set, the homepage `/` redirects to it automatically.
