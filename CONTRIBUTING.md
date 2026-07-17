# Contributing to MDDocs

Thanks for helping improve MDDocs.

## Local setup

```bash
git clone https://github.com/devioarts/mddocs.git
cd mddocs
composer install
composer serve
```

## Checks

Run these before opening a pull request:

```bash
composer validate --strict
composer lint
composer build-search
```

## Deployment templates

HestiaCP templates live in `deploy/hestiacp/`. If a deployment template changes, update both the deploy README and the MDDocs deployment documentation.

## Documentation changes

- Keep documentation in English unless a page is explicitly language-specific.
- Use `menu.md` for multi-page documentation.
- Use relative Markdown links between documentation pages.
- Update the MDDocs skill in [devioarts/skills](https://github.com/devioarts/skills/tree/main/mddocs) when changing documentation standards or AI workflows.

## Pull requests

Prefer focused pull requests with a short explanation of the behavior or documentation changed.
