---
title: Agent skill
---

# Agent skill

MDDocs includes a portable Agent Skill that teaches AI agents how to create, improve, and maintain high-quality MDDocs-compatible project documentation.

The skill is maintained in a separate skills repository, not in this repository:

```text
https://github.com/devioarts/skills/tree/main/mddocs
```

It is intentionally vendor-neutral. The same folder shape can be used by Codex, Claude, or another agent system that understands `SKILL.md`-based skills.

## Purpose

The skill defines the MDDocs documentation standard:

- store documentation as plain Markdown
- use `.menu.md` for explicit multi-page navigation
- allow small documentation sets with only `README.md` or `index.md`
- keep links relative
- inspect source files before writing
- clarify the intended documentation type when the request is broad
- organize pages around maintainer and user jobs
- support README, API docs, runbooks, architecture docs, onboarding guides, user guides, and operations guides
- keep docs practical for humans and future agents
- keep documentation concise without omitting necessary reader context
- validate documentation quality after edits
- when connected to a MDDocs MCP server, use its tools directly (`get_page`, `search_docs`, `update_page`, `append_to_page`, `validate_documentation`, ...) and prefer targeted updates over full rewrites when documentation already exists

## Install or update

Install or update the skill with the [`skills`](https://www.npmjs.com/package/skills) CLI so the local agent always uses the current MDDocs standard. Run it ad hoc via `npx`, no install required:

```bash
# Claude Code
npx skills add devioarts/skills --skill mddocs -a claude-code

# Codex
npx skills add devioarts/skills --skill mddocs -a codex
```

Add `-g` before `-a` to install into your user/global skills directory instead of the current project:

```bash
npx skills add devioarts/skills --skill mddocs -g -a claude-code
```

Update the installed skill later with:

```bash
npx skills update mddocs
```

For other agents, or to install manually, copy the whole `mddocs` folder from [devioarts/skills](https://github.com/devioarts/skills/tree/main/mddocs) into that agent's skill directory. Keep the `references/` directory with `SKILL.md`:

- Claude Code (personal): `~/.claude/skills/mddocs`
- Claude Code (project-local): `.claude/skills/mddocs`
- Codex (personal): `~/.codex/skills/mddocs`
- Codex (project-local): `.agents/skills/mddocs`

## Documentation workflow

There are two supported flows, depending on whether the agent has an MCP connection to a running MDDocs server.

### Creating documentation locally

Generate documentation locally in the project being documented. Do not write directly into the production MDDocs server first.

Recommended flow:

1. Install or update the MDDocs skill from the [devioarts/skills](https://github.com/devioarts/skills) repository.
2. Open the target project locally.
3. Ask the agent to inspect the project and create MDDocs-compatible documentation in a local output folder.
4. Have the agent validate links, commands, configuration values, and page quality.
5. Review and adjust the generated documentation.
6. Upload or commit the finished documentation folder to the MDDocs server.
7. Run `composer build-search` on the MDDocs server.

### Maintaining documentation through the MCP server

When an MCP client is already connected to a MDDocs server (see [MCP server](mcp.md)) and documentation for the project exists, the agent should read `references/mcp-workflow.md` from the skill and work directly against the live documentation set: list existing pages, find the page a change affects with `search_docs` or `get_page`, prefer `append_to_page` over a full `update_page` rewrite for small additions, and finish with `validate_documentation`. This skips the local-folder-then-upload step entirely.

## Example request

```text
Use the MDDocs skill to document this project in ./docs/my-project.
```

The rest is the skill's job, not the prompt's: inspecting the project, picking the right document type per page (README, API docs, runbook, architecture doc, onboarding guide, user guide, operations guide), avoiding generic README-style prose, writing `.menu.md` when useful, and reporting validation steps.

## References

The skill includes focused references:

| File | Purpose |
| --- | --- |
| `references/documentation-authoring.md` | How to inspect a project, choose reader-focused pages, and write useful documentation. |
| `references/content-model.md` | Structure, page conventions, and AI-first writing standards. |
| `references/validation.md` | Checklist before finishing documentation changes. |
| `references/mcp-workflow.md` | Which MCP tool to use for each task, and how to maintain existing documentation instead of only creating new pages. |

## Repository role

The [devioarts/skills](https://github.com/devioarts/skills) repository is the source of truth for the MDDocs skill. It can be installed via the `skills` CLI, copied, or packaged for different agent environments without changing the documentation format.

When installing manually, copy the whole `mddocs` folder, not just `SKILL.md`, so the `references/` files stay available.
