---
name: mddocs
description: Create, rewrite, restructure, validate, and maintain high-quality MDDocs-compatible technical documentation. Use when asked to write docs for a project, document this, create a README, write API docs, architecture docs, runbooks, onboarding guides, maintainer docs, user guides, improve poor documentation, turn README notes into a documentation set, design documentation structure, update menu.md navigation, keep documentation agent-friendly, or install/reuse the MDDocs documentation skill in Codex, Claude, or another agent environment.
---

# MDDocs

Use this skill to create portable, AI-first project documentation that can be rendered by MDDocs and maintained by agents over time.

Canonical source: `https://github.com/devioarts/mddocs/tree/main/skills/mddocs`.

When using this skill outside the MDDocs repository, keep the whole skill folder together, including `references/`.

## Core Promise

Create documentation that helps a real maintainer get work done. Do not merely describe files or repackage the README. Explain what the project is, how to run it, how it is shaped, where to change it, how to deploy it, and what can go wrong.

Ground every claim in inspected source files, scripts, config, routes, docs, or user-provided facts. When something cannot be verified, either omit it or mark it as an assumption or unknown.

Support the documentation type the user actually asked for: README, API reference, runbook, architecture doc, onboarding guide, operations guide, user guide, or a multi-page project documentation set. Do not force every request into the same page template.

Keep documentation concise, but never make it shallow for the sake of brevity. A short page is acceptable only when it fully answers the reader's job with verified, project-specific details.

Do not mechanically split an existing README into pages. Reshape the material around reader journeys, examples, decisions, and reference sections so each page adds value beyond the source document.

For packages, plugins, SDKs, CLIs, and platform libraries, teach the mental model and common tasks before the full API surface. Include install/setup, concepts, examples, errors, platform or version limits, and reference material when the inspected project supports them.

## MDDocs Format

Create documentation as plain Markdown files inside a documentation directory:

```text
docs/
  project-name/
    menu.md
    index.md
```

For very small projects, `menu.md` is optional. A single `README.md` or `index.md` is valid MDDocs documentation. Once there is more than one page, create `menu.md`. See `references/content-model.md` for the recommended page set, nested topics, and when to split a page into subpages.

Keep documentation in English unless the user explicitly requests another language. When extending or editing an existing documentation set, match its existing language instead of defaulting to English.

## Documentation Workflow

If an MCP client is connected to a MDDocs server, read `references/mcp-workflow.md` and prefer its tools (`get_page`, `search_docs`, `update_page`, `append_to_page`, `validate_documentation`, ...) over manual file edits — especially when documentation for the project already exists and the task is to maintain it. The steps below apply either way.

1. Inspect before writing. Read the existing README, docs, package manifests, lockfiles when useful, config, env examples, source entry points, routes, public APIs, CLI commands, tests, Docker/deployment files, and any generated documentation the user dislikes. Read environment and config files for variable names and structure only — never copy a real secret, key, password, or token value into documentation. Match inspection depth to project size: read everything relevant in a small project; in a large one, start from entry points, routing/config, and the most-referenced modules, and go deeper only for the pages actually being written.
2. Identify the intended documentation type and audience. Infer it from the request and project when clear. If the request is ambiguous and the choice would materially change the structure, ask one concise clarification before writing.
3. Build a source map. Note the commands, configuration keys, entry points, modules, workflows, deployment steps, and risks that are actually supported by the project. When inspected source and a user-provided claim disagree, trust the source and note the discrepancy instead of silently following the claim.
4. Choose the clearest useful information architecture. Create pages around reader jobs, not around the source tree. Keep small topics small, but split large topics into readable pages or subsections.
5. Read `references/documentation-authoring.md` before creating new documentation, rewriting poor documentation, or substantially restructuring a documentation set. Use its document-type guidance to decide whether the output should be a README, API reference, runbook, architecture doc, onboarding guide, or broader docs set.
6. Read `references/content-model.md` when deciding the MDDocs folder shape, page list, asset locations, and `menu.md` structure.
7. For a new multi-page documentation set, or when a requested update spans many existing pages, propose the page list (or updated `menu.md`) and get confirmation before drafting full content — restructuring after full pages are written is expensive. For a single page or a small edit, skip this and draft directly. Also do this on request, whenever the user asks to see a plan first.
8. Draft pages from the source map. Prefer concrete commands, examples, file paths, env vars, routes, extension points, and troubleshooting signals over generic prose.
9. Cross-link pages with relative Markdown links. Create or update `menu.md` once there is more than one page.
10. Read `references/validation.md` before finishing substantial documentation work and revise until the docs pass the quality checklist. Run the `validate_documentation` MCP tool instead of the manual link check when it is available.
11. If working inside a MDDocs project, run `composer build-search` after content changes and `composer lint` after PHP changes.
12. Report what changed and any commands that were or were not run.

## Writing Rules

- Write for the next maintainer first, then for the current user, then for future agents.
- Start each page with what the reader can do after reading it.
- Prefer task-oriented sections: install, configure, run, edit content, add an integration, deploy, debug.
- Split large topics such as configuration, APIs, plugins, integrations, or deployment into focused subpages when one page becomes dense.
- Lead with orientation and common paths before exhaustive reference tables.
- Make pages scannable: short sections, clear headings, examples before full inventories, and links to deeper detail.
- Include verified examples for commands, configuration, APIs, plugins, integrations, and common workflows whenever the source supports them.
- Use exact commands from project scripts and exact names from code.
- Explain project-specific concepts only after confirming them from source.
- Keep pages concise, but not thin. A short page is good only when it answers a real reader question.
- Prefer complete and useful over artificially small. Remove filler, not necessary context.
- Avoid marketing copy, filler, vague claims, and architectural storytelling that cannot be tied to code.
- Avoid dumping a file tree unless it explains where maintainers make common changes.
- Preserve useful existing content, but rewrite weak content instead of copying it forward.

If the user asks to install or reuse the MDDocs skill, use the canonical repository `https://github.com/devioarts/mddocs` as the source unless they provide another source.

For normal documentation generation, work locally in the target project first. Do not write directly to a production MDDocs server unless the user explicitly asks for that deployment step. Produce a MDDocs-compatible documentation folder that can later be uploaded, committed, or copied into a MDDocs server's `docs/` directory.

Default personal install locations:

- Codex: `~/.codex/skills/mddocs`
- Claude Code: `~/.claude/skills/mddocs`

Claude Code project-local skills can live under `.claude/skills/mddocs` in the target repository.

## menu.md

`menu.md` is the explicit navigation map: a `#` title followed by pages in reading order, with nested bullets for sections.

```markdown
# Project Name

- [Overview](index.md)
- [Quick start](quick-start.md)
```

See `references/content-model.md` (Navigation Quality) for a fuller example with nested sections.

## Page Conventions

Each page should have one `#` heading. Optional front matter is allowed, but do not depend on it for navigation:

```markdown
---
title: Quick start
---

# Quick start
```

Prefer clear file names:

- `index.md`
- `quick-start.md`
- `configuration.md`
- `usage.md`
- `architecture.md`
- `deployment.md`
- `troubleshooting.md`
- `changelog.md`
- `integrations/github.md`
- `api/authentication.md`

## When More Detail Is Needed

Read `references/documentation-authoring.md` before writing or improving project documentation.

Read `references/content-model.md` when deciding how to structure a documentation set.

Read `references/validation.md` before finishing substantial documentation work.

Read `references/mcp-workflow.md` when an MCP client is connected to a MDDocs server, or when maintaining documentation that already exists.
