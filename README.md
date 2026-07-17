# MDDocs

MDDocs is a PHP 8.4 documentation viewer for Markdown files, with an MCP server for AI-assisted documentation editing.

Full documentation: **[docs.devioarts.com/mddocs](https://docs.devioarts.com/mddocs)**

Canonical repository: [devioarts/mddocs](https://github.com/devioarts/mddocs)

## Quick start

```bash
git clone https://github.com/devioarts/mddocs.git
cd mddocs
composer install
composer serve
```

Open `http://127.0.0.1:8080/mddocs`.

## Contributing

Before opening a pull request, run:

```bash
composer validate --strict
composer lint
composer build-search
```
