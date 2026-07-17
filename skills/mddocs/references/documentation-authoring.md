# Documentation Authoring Standard

Use this reference when creating, rewriting, or substantially improving project documentation.

## Outcome

Good documentation lets a new maintainer answer these questions quickly:

- What is this project and when should I use it?
- How do I get it running locally?
- What commands are available and what do they do?
- How is the project organized?
- Where do I change common behavior?
- How do I configure, deploy, and troubleshoot it?
- What is known, assumed, or intentionally unsupported?

## Quality Dimensions

Optimize documentation for:

- fast comprehension: the reader can quickly tell where to start
- accuracy: claims are grounded in source, tests, schemas, or existing docs
- completeness: the reader can finish the intended task without hidden steps
- maintainability: pages have stable scope, clear ownership, and limited duplication

Use detailed structure when it improves those qualities. Do not copy a large universal outline into every project. Select the sections that match the audience, project type, and verified source material.

## Discovery Pass

Before drafting, inspect enough source material to write from evidence:

- README and existing docs
- package manifests, Composer scripts, npm scripts, Makefiles, task runners
- environment examples, config files, Docker files, deployment manifests
- source entry points, routes, controllers, commands, services, public APIs
- tests and fixtures when they reveal expected behavior
- database migrations or schemas when relevant
- install scripts, CI workflows, and release/deploy notes

Read environment and config files for variable names, structure, and defaults only. Never copy a real secret, API key, password, token, or connection string from a `.env`, credentials file, or deployed config into documentation — reference the variable name and where it is set instead.

Scale the pass to project size. For a small project, read everything in the list above before drafting. For a large project, start from entry points, routing/config, and the modules most relevant to the request, then go deeper only for the specific pages being written. Reading the entire codebase before drafting is not required.

Create a mental source map from this inspection. The docs should reflect how the project actually works, not what a generic project of that type might do.

## Completeness Over Artificial Brevity

Keep documentation lean by removing filler, not by omitting information the reader needs.

Short documentation is good when the project is small or the reader job is narrow. Short documentation is bad when it leaves the reader guessing about setup, commands, configuration, architecture, deployment, or recovery.

Before choosing a short format, confirm:

- the project has few workflows or the user asked for a narrow document
- the essential commands and configuration are covered
- the reader can reach first success without asking follow-up questions
- important risks, limitations, or unknowns are named
- links point to deeper docs when the information belongs elsewhere

If those conditions are not true, write the extra section or page. Do not compress necessary context into vague prose.

## Reader Jobs

Organize documentation around jobs the reader is trying to complete:

- evaluate the project
- install and run it
- configure it
- use core workflows
- understand how it is built and where things live
- edit content or behavior
- integrate with external systems
- deploy it
- debug common failures
- contribute safely

Prefer pages and headings named after these jobs. Avoid mirroring the code directory structure unless the reader specifically needs that map.

## Clarify Before Writing

Infer the documentation type from the user's words when possible:

- "README", "overview", "project page" -> README or overview
- "API", "methods", "endpoints", "types" -> API reference plus task examples
- "how do I", "guide", "tutorial" -> task guide or user guide
- "runbook", "incident", "operate", "recover" -> runbook or operations guide
- "architecture", "design", "how it works" -> architecture doc
- "onboarding", "new developer", "handoff" -> onboarding guide
- "full docs", "documentation set", "publish docs" -> multi-page documentation set

Ask one concise clarification when the request is broad and the answer changes the structure, for example:

```text
What kind of documentation should this be: quick README, full project docs, API reference, onboarding guide, or operations/runbook?
```

Do not ask when the user already named the document type, provided an output structure, or asked for a small obvious fix. If you can make a safe default, state it briefly and proceed.

## Document Types

Choose the document type from the user's request and the source material. A MDDocs documentation set can contain several of these, but each page should still have a clear job.

A project can match more than one type at once — for example, a published package that also runs as a service with an HTTP API, or an SDK with its own CLI. In that case, do not force a single template. Use the Package/SDK pattern for the adoption path (install, concepts, task guides) and the API Documentation pattern for the contract reference (endpoints, methods, auth), and cross-link them instead of merging both into one page.

### README or Overview

Use for a small project, package, tool, or repository entry point:

- what this is and why it exists
- who it is for
- prerequisites
- quick start in under five minutes when possible
- minimal configuration and usage
- where to find deeper docs
- contribution or development notes only when useful

For packages and libraries, separate the adopter's path from maintainer internals. Start with the smallest working integration, then explain common customizations, then provide API/reference material. Do not make the overview a compressed inventory of every exported feature.

### API Documentation

Use when the project exposes HTTP routes, SDK methods, CLI interfaces, MCP tools, or other public contracts:

- authentication and authorization requirements
- endpoint, method, command, or tool reference
- request parameters and response fields
- examples copied from tests, routes, schemas, fixtures, or existing docs
- error cases and status codes when visible in source
- pagination, rate limits, versioning, and compatibility only when verified

For large APIs, provide task-oriented guides before the full method table. Readers should see "how to do the common thing" before they see every method name.

### Package, Plugin, SDK, or Platform Library Docs

Use this pattern for reusable developer-facing packages, especially when they have platform support, generated types, CLIs, plugins, or native/runtime integration.

Prioritize:

- overview: what problem it solves, supported platforms/runtimes, maturity, repository/issues
- quick start: install, required sync/build step, smallest working example, success signal
- installation/setup: per platform or runtime when requirements differ
- concepts: lifecycle, data model, async behavior, events, persistence, errors, security/trust model
- task guides: common workflows with complete examples
- API reference: signatures, parameters, return values, errors, side effects, platform support, examples
- types and events: explain the meaning of fields and values, not only their syntax
- error model: stable codes, causes, fixes, and recovery guidance
- platform/version differences: tables only after the reader understands the feature groups
- advanced usage: performance, concurrency, limits, production guidance, migration notes when relevant
- examples: minimal, framework-specific, platform-specific, and production-like examples when present or safely derivable

Do not include every item automatically. Include the items that are needed for the package's actual complexity and supported by inspected source. If the package is simple, fold these into fewer pages with clear subsections. If it is complex, split into focused pages.

### Runbook

Use for operational procedures and incident response:

- when to use the runbook
- prerequisites, permissions, and required access
- symptoms or alerts that trigger the procedure
- step-by-step commands or UI actions
- verification after each risky step
- rollback, recovery, and escalation paths when known

### Architecture Doc

Use when the reader needs to understand design, boundaries, or how to make changes safely:

- context and goals
- runtime components and responsibilities
- data flow, storage, integrations, and important interfaces
- key decisions and trade-offs grounded in code or existing docs
- diagrams when they clarify relationships
- extension points and files maintainers are likely to edit

Add a diagram whenever the architecture crosses two or more runtime boundaries — separate processes, a client/server split, trust levels, or a plugin/host boundary. A few boxes and one arrow across the boundary usually replace several paragraphs of prose, and most readers will not otherwise retain which side of a trust or process boundary a piece of code runs on.

### Onboarding Guide

Use when a new contributor or operator needs a guided path into the project:

- environment setup
- main concepts and vocabulary
- how major systems connect
- common first tasks with walkthroughs
- development, test, and review commands
- where to ask or what to inspect next when blocked

### User Guide

Use when documenting product or application workflows:

- user goals and common tasks
- step-by-step workflows
- expected inputs, outputs, screens, URLs, files, or results
- limitations and common mistakes
- links to configuration or troubleshooting when relevant

### Operations or Deployment Guide

Use when the request is about hosting, release, or production maintenance:

- runtime requirements
- build and deployment steps
- server roots, storage, caches, queues, scheduled jobs, and secrets
- post-deploy verification
- rollback or rebuild steps only when supported by source or existing docs

### Changelog

Use when the project needs a record of what changed between releases. Follow [Keep a Changelog](https://keepachangelog.com/en/1.1.0/):

- one file — `CHANGELOG.md` at the project root, or `changelog.md` in the documentation set — linked from the README or `index.md`
- newest version first; each version is its own heading with a release date in `YYYY-MM-DD` format, or `Unreleased` for changes not yet released
- group entries under `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security` — include only the groups that have entries for that version
- write for the person deciding whether to upgrade: describe the observable effect of a change, not the implementation. This is not a commit log or a list of PR titles.
- link each version heading to a diff, tag, or release when the repository supports it
- state whether the project follows Semantic Versioning only when that is verifiable from `package.json`, `composer.json`, or existing tags

Ground each entry in inspected commits, merged PRs, or release notes — not guesses. If only a diff is available and the user-facing effect of a change is not clear from it, ask rather than inferring intent.

## Page Quality

Every page should have:

- one clear `#` heading
- a short opening paragraph that defines the page's purpose
- concrete commands, files, routes, config keys, or examples where useful
- project-specific details grounded in inspected files
- links to adjacent pages when the reader naturally needs them next
- no placeholder sections

Avoid pages that only say "this project supports X" without explaining how to use, change, or verify X.

## Readability and Density

Good documentation is easy to scan before it is read deeply. Avoid dense pages that feel like compressed source notes.

Use this order on substantial pages:

1. Orientation: what this page is for and who needs it.
2. Common path: the main workflow or decision the reader probably came for.
3. Examples: one or two concrete examples before exhaustive detail.
4. Reference: tables, full option lists, edge cases, and internals.
5. Next step: links to adjacent pages or deeper topics.

Split or restructure a page when:

- it mixes beginner setup, advanced internals, and full reference material
- a large table appears before the reader knows why it matters
- the page has many unrelated headings at the same level
- code examples are not introduced by a task or expected outcome
- paragraphs explain multiple concepts at once
- the reader cannot tell what to read first and what to skip

Prefer short sections with descriptive headings over long continuous prose. Use tables for lookup, not as a substitute for explanation. For APIs or large feature surfaces, group by reader task and provide a minimal example before the full reference.

Every sentence is doing one of four jobs — the [Diátaxis](https://diataxis.fr) framework names them well: walking a newcomer through steps (tutorial), solving a specific problem (how-to), stating a fact for lookup (reference), or explaining why something is the way it is (explanation). Mixing two of these within the same paragraph — a lookup fact interrupting a "why" explanation, or background theory breaking into a step-by-step instruction — is a sharper version of "paragraphs explain multiple concepts at once." When restructuring a dense page, ask which of the four jobs each sentence is doing; a sentence doing a different job than its neighbors belongs in its own paragraph, list, or section.

One sentence, one idea. A sentence that stacks a main clause, a parenthetical aside, an em-dash pivot, and a causal clause is describing several ideas at once — split it into two or three sentences or a short list. This is the single most common way accurate, well-researched content still reads as unreadable.

Split this:

> `flushQueue()` (worker) drains every pending job from the in-memory buffer and writes each one to disk (batching writes in groups of 50 to avoid one fsync per job — the batch size is configurable via `queue.batchSize`) before acknowledging the job to the caller — synchronous, so a crash between drain and acknowledge can only lose an already-batched, unwritten group, never a job the caller believes finished.

into this:

> `flushQueue()` (worker) drains every pending job from the in-memory buffer. It writes jobs to disk in batches — 50 per batch by default, configurable via `queue.batchSize` — to avoid one fsync per job. Only after a batch is written does it acknowledge those jobs to the caller. Because acknowledgment happens after the write, a crash can only lose an already-batched, not-yet-written group; it can never lose a job the caller believes finished.

Same facts, same technical precision, but each sentence now does one job. If a paragraph still needs more than 4-5 lines to explain one mechanism after this split, it is a candidate for a short list or a code example instead of more prose.

## Examples

Examples are core documentation, not decoration. Use them to prove that the reader can apply the concept.

If a paragraph describes what a function, API, channel, or mechanism does, and a reader could not tell how to call or trigger it from that paragraph alone, add a short code example or call shape right after it — even two lines. Do not fully describe a registration function, a trust check, a plugin interface, or any other mechanism in prose only; show its call shape, not just its behavior in words.

Include examples for:

- install and first-run flows
- configuration files and environment variables
- CLI commands and expected output or success signals
- API requests, responses, SDK calls, or method usage
- plugin, integration, or extension workflows
- common user tasks
- troubleshooting fixes and verification steps

Prefer examples from real project artifacts:

- tests and fixtures
- templates and sample apps
- existing README snippets that are accurate
- route definitions, schemas, typed interfaces, or config examples
- scripts and package commands

Every example should answer:

- What problem does this solve?
- Where does this code or command go?
- What should happen when it works?
- What config, permission, or prerequisite does it depend on?

Avoid unverified examples. If a useful example is inferred rather than copied from source, label it as illustrative and keep it minimal. Do not fabricate API fields, environment variables, package names, or outputs.

For large APIs, include:

- a minimal happy-path example
- one realistic customization example
- one error-handling or edge-case example when source supports it
- the full reference after those examples

## Recommended Page Roles

`index.md`:

- identify the project in one or two plain sentences
- name the primary audience
- summarize the main capabilities
- link to the fastest next step
- include the most important constraints or runtime requirements

`quick-start.md`:

- give the shortest verified path from fresh checkout to running app or tool
- list prerequisites
- include install, configure, run, and verify steps
- mention expected local URLs, ports, output, or success signals
- include only essential explanation

`configuration.md`:

- list environment variables, config files, defaults, and required secrets
- separate required from optional settings
- explain where each value is consumed when that is discoverable
- avoid inventing variables from conventions

`usage.md` or workflow pages:

- describe real tasks users perform with the project
- include command examples, UI paths, API examples, or file edit examples
- show inputs and expected outputs when useful

`architecture.md`:

- explain runtime boundaries, data flow, storage, integration points, and important modules
- describe why key parts exist when the source makes that clear
- name files and classes that maintainers will likely edit
- avoid exhaustive file listings and unverified design claims

`deployment.md`:

- list runtime requirements and build steps
- explain hosting assumptions, public root, background jobs, caches, storage, and secrets
- include rollback or rebuild steps only when supported by the project

`troubleshooting.md`:

- focus on observable symptoms, likely causes, and fixes
- tie fixes to commands, files, config values, logs, or cache rebuilds
- include "not verified" notes when the failure mode is inferred

## Information Architecture

Use the smallest structure that answers the reader's jobs:

- One page is enough for a tiny script or library with one workflow.
- Three pages often fit a small app: overview, quick start, configuration or usage.
- Larger apps usually need overview, quick start, content/usage, configuration, architecture, deployment, and troubleshooting.
- Add nested folders for distinct domains such as `integrations/`, `api/`, or `operations/`.

Do not create a page just because a template includes it. Create it because it has useful, verified content.

Use enough structure for the topic. If a page becomes a long reference mixed with guidance, split it. Parent pages should orient and route; child pages should go deep.

Common splits:

- Configuration: overview, required settings, security, UI/behavior, build/deploy, processes/workers, integrations, full reference.
- API: quick examples, authentication/trust model, method groups, events, errors, extension points.
- Plugins or integrations: concepts, install, authoring, configuration, lifecycle, examples, troubleshooting.
- Deployment: local build, packaging, server/runtime requirements, secrets, storage/cache, verification, rollback.

When keeping a topic on one page, use strong subsections and put the full reference after the common workflow.

## Writing Style

Use direct maintainer prose:

- Prefer "Run `composer serve`" over "You can easily start the application".
- Prefer "Set `DOCS_STORAGE=github` to read docs from GitHub" over "The app supports remote storage".
- Prefer "The public document root must point to `public/`" over "Configure your web server correctly".

Keep paragraphs short. Use lists and tables when they make scanning easier. Do not over-table simple content.

## Evidence and Uncertainty

When inspected source and a user-provided claim disagree, trust the source. Note the discrepancy to the user rather than silently documenting the user's version — the user's mental model of the project can be stale.

If a detail is visible in code, state it plainly and cite the file path in prose when useful.

If a detail is likely but not verified:

- mark it as an assumption
- tell the user what was not found
- avoid presenting it as a supported workflow

Never fabricate commands, environment variables, URLs, authentication flows, deployment platforms, or tests.

## Rewriting Existing Poor Documentation

When improving generated or weak documentation:

1. Identify duplicated, generic, stale, or unsupported claims.
2. Preserve accurate commands and project-specific facts.
3. Merge thin pages or split overloaded pages around reader jobs.
4. Rewrite introductions so they say what the reader can accomplish.
5. Replace broad claims with concrete workflows.
6. Validate every link, command, and config key that remains.

Do not keep bad structure for compatibility unless the user explicitly requests minimal churn.
