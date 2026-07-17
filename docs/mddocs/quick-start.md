---
title: Quick start
---

# Quick start

This page shows the shortest path from a fresh checkout to a running MDDocs documentation site.

## Requirements

- PHP 8.4 or newer
- Composer 2
- a terminal in the project directory

## Install dependencies

```bash
git clone https://github.com/devioarts/mddocs.git
cd mddocs
composer install
```

## Start the web server

```bash
composer serve
```

Then open:

```text
http://127.0.0.1:8080/mddocs
```

## Build the search index

The search index can be built automatically on first search. To rebuild it manually, run:

```bash
composer build-search
```

## Lint PHP files

```bash
composer lint
```

## Start the MCP server

```bash
composer mcp
```

This command starts the stdio MCP server used by MCP clients.
