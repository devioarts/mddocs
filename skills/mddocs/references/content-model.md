# MDDocs Content Model

Canonical MDDocs repository: `https://github.com/devioarts/mddocs`.

## Documentation Directory

A MDDocs documentation set is a directory of Markdown files. The directory name becomes the URL segment in MDDocs.

```text
docs/
  project-name/
    menu.md
    index.md
```

If a user asks for documentation in a custom folder, create the same structure inside that folder.

Choose the directory name from the project, package, repository, or user request. Prefer lowercase hyphenated names for new documentation directories.

## Minimal Documentation

Use minimal documentation for tiny projects:

```text
docs/project-name/README.md
```

or:

```text
docs/project-name/index.md
```

MDDocs can render a directory with Markdown files even when `menu.md` is absent.

Minimal documentation still needs useful content: purpose, prerequisites, setup, usage, and any important configuration or limitations.

## Multi-Page Documentation

Create `menu.md` once there is more than one page, or when reading order matters even for a single page.

Recommended pages:

- `index.md`: what the project is, who it is for, main capabilities
- `quick-start.md`: fastest path to a working local setup
- `configuration.md`: environment variables, config files, secrets, defaults
- `usage.md`: common workflows and examples
- `architecture.md`: important modules, data flow, boundaries, decisions
- `deployment.md`: build, hosting, release, runtime requirements
- `troubleshooting.md`: known failure modes and fixes

Only create pages that have meaningful content.

Use nested directories when a topic family has more than one page:

```text
docs/project-name/
  integrations/
    github.md
    mcp.md
  api/
    authentication.md
    endpoints.md
```

Keep `index.md` focused on orientation. Do not make it carry all setup, architecture, configuration, and deployment detail when separate pages would serve the reader better.

## Depth and Splitting

Use deeper structure when a topic is too large for one readable page. A detailed documentation set is better than one overstuffed page.

Split a topic into nested pages when:

- one page has multiple distinct reader jobs
- a single table would contain many unrelated option groups
- beginners and advanced users need different levels of detail
- setup, customization, reference, and troubleshooting are mixed together
- one page would need many second-level sections to stay understandable

Examples:

```text
docs/project-name/
  configuration.md
  configuration/
    app.md
    security.md
    ui.md
    build.md
    processes.md
  api/
    bridge.md
    events.md
    errors.md
```

Use a parent page such as `configuration.md` as an overview and routing page. It should explain the mental model, required settings, common paths, and links to detailed subpages. Do not duplicate the full reference on both parent and child pages.

## Developer Package Documentation

For complex packages, SDKs, plugins, CLIs, and platform libraries, consider a hierarchy that separates adoption, concepts, task guides, and reference:

```text
docs/package-name/
  index.md
  quick-start.md
  installation.md
  installation/
    web.md
    android.md
    ios.md
    electron.md
  concepts.md
  guides/
    basic-usage.md
    events.md
    errors.md
    advanced-scenarios.md
  api/
    methods.md
    types.md
    events.md
    error-codes.md
  platform-support.md
  troubleshooting.md
  migration/
    v2.md
  examples.md
  contributing.md
```

Use this as a menu of possibilities, not a required template. Include only pages that have verified, useful content. For smaller packages, collapse related pages into clear subsections.

## Configuration Pages

Configuration documentation should be organized by decision area, not as one flat dump of keys.

For small projects, one `configuration.md` may be enough if it has clear subsections:

- Overview: where configuration lives and when it is loaded
- Required settings: values needed before first run
- Environment variables and secrets
- Feature flags or opt-in capabilities
- Build/deployment settings
- Examples: minimal first-run config and realistic production/customized config
- Reference: complete option list, grouped by area

For complex projects, split configuration into subpages and keep `configuration.md` as the guide:

- `configuration/app.md`: identity, runtime mode, paths, default behavior
- `configuration/security.md`: permissions, authentication, CSP, trust boundaries
- `configuration/ui.md`: menus, windows, themes, visible app behavior
- `configuration/build.md`: bundling, packaging, deployment-related settings
- `configuration/processes.md`: workers, external commands, background jobs
- `configuration/integrations.md`: third-party services, tokens, webhooks, APIs

Each configuration option should include what it controls, default value when known, when to change it, and a minimal example when useful.

## AI-First Writing Standards

Write for humans and future agents:

- Prefer precise commands over vague descriptions.
- Name files, directories, classes, scripts, routes, and environment variables exactly.
- Include assumptions and constraints.
- Explain where to make common changes.
- Keep generated claims grounded in inspected project files.
- Mark unknowns as unknown instead of guessing.
- Preserve enough context for the next agent to understand why commands, paths, and config values matter.
- Prefer durable project facts over ephemeral local-machine observations.

## Navigation Quality

`menu.md` is both the sidebar and the reading path. Use it to tell the reader what order to follow.

Good menu entries are short and task-oriented:

```markdown
# Project Name

- [Overview](index.md)
- [Quick start](quick-start.md)
- [Configuration](configuration.md)
- [Content workflows](content.md)
- Integrations
  - [MCP server](integrations/mcp.md)
- [Deployment](deployment.md)
- [Development](development.md)
```

Do not include pages in `menu.md` just because they exist. Include pages that belong in the primary documentation journey.

## Links and Assets

Use relative Markdown links:

```markdown
[Configuration](configuration.md)
[GitHub integration](integrations/github.md)
```

Store documentation assets under `assets/` inside the documentation set when possible:

```text
docs/project-name/assets/diagram.png
```
