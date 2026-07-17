---
title: Agent skill
---

# Agent skill

MDDocs includes a portable Agent Skill that teaches AI agents how to create, improve, and maintain high-quality MDDocs-compatible project documentation.

The skill lives in:

```text
skills/mddocs/SKILL.md
```

It is intentionally vendor-neutral. The same folder shape can be used by Codex, Claude, or another agent system that understands `SKILL.md`-based skills.

Canonical source:

```text
https://github.com/devioarts/mddocs/tree/main/skills/mddocs
```

## Purpose

The skill defines the MDDocs documentation standard:

- store documentation as plain Markdown
- use `menu.md` for explicit multi-page navigation
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

## Install or update from GitHub

Install or update the skill from the canonical GitHub repository so the local agent always uses the current MDDocs standard.

For Codex:

```bash
curl -fsSL https://raw.githubusercontent.com/devioarts/mddocs/main/scripts/install-codex-skill.sh | bash
```

From a local MDDocs checkout:

```bash
bash scripts/install-codex-skill.sh
```

Run the same command again to update the skill.

For other agents, copy the whole `skills/mddocs` directory from the repository into that agent's skill directory. Keep the `references/` directory with `SKILL.md`.

For Claude Code:

```bash
curl -fsSL https://raw.githubusercontent.com/devioarts/mddocs/main/scripts/install-claude-skill.sh | bash
```

From a local MDDocs checkout:

```bash
bash scripts/install-claude-skill.sh
```

The default Claude target is:

```text
~/.claude/skills/mddocs
```

For a project-local Claude skill, copy `skills/mddocs` into `.claude/skills/mddocs` inside the target repository.

## Documentation workflow

There are two supported flows, depending on whether the agent has an MCP connection to a running MDDocs server.

### Creating documentation locally

Generate documentation locally in the project being documented. Do not write directly into the production MDDocs server first.

Recommended flow:

1. Install or update the MDDocs skill from GitHub.
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
Use the MDDocs skill.
Create MDDocs-compatible documentation for this project in ./docs/my-project.
Inspect the project first, then create practical maintainer documentation.
Choose the right document type for each page: README, API docs, runbook, architecture doc, onboarding guide, user guide, or operations guide.
Avoid generic README-style prose; organize pages around setup, configuration, usage, architecture, deployment, and troubleshooting where the source supports them.
```

The agent should inspect the project, choose a suitable documentation structure, write the Markdown files, create `menu.md` when useful, and report validation steps.

## References

The skill includes focused references:

| File | Purpose |
| --- | --- |
| `references/documentation-authoring.md` | How to inspect a project, choose reader-focused pages, and write useful documentation. |
| `references/content-model.md` | Structure, page conventions, and AI-first writing standards. |
| `references/validation.md` | Checklist before finishing documentation changes. |
| `references/mcp-workflow.md` | Which MCP tool to use for each task, and how to maintain existing documentation instead of only creating new pages. |

## Repository role

The repo-local skill is the source of truth for the MDDocs standard. It can be copied, installed, or packaged for different agent environments without changing the documentation format.

When using it outside this repository, copy the whole `skills/mddocs` folder, not just `SKILL.md`, so the `references/` files stay available.
