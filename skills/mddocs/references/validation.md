# MDDocs Validation Checklist

Before finishing substantial MDDocs documentation work, check:

- The documentation is in English unless the user requested another language or the existing documentation being extended is already in another language.
- Every page has one clear `#` heading.
- The output matches the requested document type: README, API docs, runbook, architecture doc, onboarding guide, user guide, operations guide, or broader documentation set.
- Ambiguous requests were either clarified with the user or handled with an explicitly stated, reasonable default.
- `menu.md` exists for multi-page documentation.
- Large topics are split into readable pages or clear subsections; configuration is grouped by decision area instead of one flat option dump.
- Package, plugin, SDK, CLI, and platform-library docs explain concepts and common tasks before exhaustive API/reference material.
- Every linked page in `menu.md` exists.
- Relative Markdown links point to real files.
- Commands are copied from actual project scripts or verified source files.
- Configuration values and environment variables match the code or existing docs.
- Commands, configuration, APIs, plugins, integrations, and workflows include verified examples when source material supports them.
- The documentation is complete enough for the requested reader job, even if that requires more than the smallest possible page.
- Dense pages are structured for scanning: orientation first, common path next, examples before exhaustive reference.
- No placeholder sections remain.
- No empty pages were created just to fill a template.
- Generated documentation does not claim unverified behavior.

## Quality Gate

Reject and revise the documentation before finishing if any of these are true:

- It reads like a generic README for any project using the same stack.
- It ignores the requested document type or turns every request into the same default page set.
- It guesses the documentation type for an ambiguous broad request and produces the wrong structure without asking or stating a default.
- It mostly lists files without explaining maintainer workflows.
- It describes features without showing how to run, configure, use, change, or verify them.
- It provides reference tables for commands, config, APIs, or plugins without examples that show real use.
- It documents APIs or exported methods but omits the mental model, task guides, error model, or platform/version constraints needed to use them correctly.
- It creates many thin pages with little project-specific value.
- It is short because it omits necessary setup, configuration, usage, operational, or troubleshooting detail.
- It is dense, hard to scan, or front-loads exhaustive tables and internals before explaining the reader's path.
- A paragraph explaining one mechanism runs as a single sentence longer than 4-5 lines, or stacks more than one parenthetical or em-dash aside — split it into shorter sentences or a list (see `references/documentation-authoring.md`, Readability and Density).
- A function, API, channel, or mechanism is fully described in prose without a code example or call shape showing how to use it.
- The architecture crosses a process, trust, or client/server boundary without a diagram.
- It keeps configuration, API, plugin, integration, or deployment detail on one overstuffed page when focused subpages would be clearer.
- It repeats the same overview content across pages.
- It makes claims about deployment, authentication, storage, APIs, or integrations that were not found in source or existing docs.
- It hides important setup, config, or operational details in prose instead of commands, tables, or clear lists.
- It lacks a clear first-run path for projects that can be run locally.
- It lacks troubleshooting or known limitations for non-trivial apps.

## Manual Link and Content Checks

For every new or edited documentation set:

- Compare `menu.md` entries against files on disk.
- Search the docs for TODO, placeholder, "coming soon", and generic filler phrases.
- Check that each page's opening paragraph says what the page helps the reader do.
- Check that each command has a source in project scripts, existing docs, or inspected files.
- Check that each env var or config key appears in code, examples, or existing docs.
- Check that overview, quick start, configuration, architecture, and deployment pages do not duplicate the same paragraphs.

When working inside a MDDocs app repository:

- Run `composer build-search` after documentation changes.
- Run `composer lint` after PHP changes.
- Do not edit `vendor/`, `var/cache/`, generated search indexes, or runtime logs unless explicitly asked.

When working outside a MDDocs app repository:

- Create MDDocs-compatible Markdown structure only.
- Do not invent Composer scripts or MDDocs runtime commands unless the project actually has them.
- Tell the user if validation commands were not available.
- Treat production publishing as a separate step unless the user explicitly asks to deploy.
