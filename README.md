# genai-enabler

Agentic development plugin for PHP/Laravel teams. Codifies architectural decisions,
Docker conventions, TDD workflows, and AI evaluation patterns into reusable skills
that every developer on the team gets automatically.

## Install

```bash
# Local testing
claude --plugin-dir ./genai-enabler-plugin

# Team distribution via marketplace
/plugin marketplace add your-org/claude-plugins
/plugin install genai-enabler@your-org/claude-plugins
```

## What's inside

### Skills (7)

| Skill | Invocation | Purpose |
|---|---|---|
| **api-scaffold** | `/genai-enabler:api-scaffold` | Scaffolds Laravel API endpoints following org architecture: thin controllers, Action classes, Repositories, Form Requests. Includes 6 example files as code style reference. |
| **docker-php** | `/genai-enabler:docker-php` | Scaffolds production Docker setup: multi-stage php-fpm + nginx, dev/test/prod targets. Includes 9 template files. |
| **spec-first** | `/genai-enabler:spec` | Writes a structured spec BEFORE code. Interviews the user, produces a spec file, waits for approval. |
| **feature** | `/genai-enabler:feature` | TDD pipeline: plan → test (RED) → implement (GREEN) → refactor → review → commit. Orchestrates test-writer and code-reviewer agents. |
| **ai-eval** | `/genai-enabler:ai-eval` | Creates LLM-as-judge evaluation suites. 12+ test cases across 6 categories. Accuracy reporting by category. |
| **simplify** | `/genai-enabler:simplify` | Second-pass refinement of AI-generated code. Cuts dead code, over-abstraction, verbosity. |
| **context-rescue** | `/genai-enabler:context-rescue` | Saves session checkpoint to disk before context degrades. Prevents losing work to auto-compaction. |

### Agents (3)

| Agent | Tools | Role |
|---|---|---|
| **code-reviewer** | Read, Grep, Glob, LS | Reviews against org conventions. Read-only — cannot modify code. |
| **test-writer** | Read, Edit, Bash, Write | TDD specialist. Writes tests, runs them, reports results. |
| **security-reviewer** | Read, Grep, Glob, LS | OWASP + AI-specific vulnerability scanning. Read-only. |

### Hooks (4 command + 1 prompt)

| Hook | Event | What it enforces |
|---|---|---|
| protect-sensitive-files | PreToolUse | Blocks .env, .pem, lock files |
| block-main-branch | PreToolUse | Blocks edits on main/master |
| block-dangerous-commands | PreToolUse | Blocks rm -rf, force push, DROP TABLE |
| auto-format | PostToolUse | PSR-12 for PHP, Prettier for JS/TS |
| quality-gate (prompt) | Stop | Haiku checks for untested code after every turn |

## Architecture: how org rules are distributed

```
Plugin (this repo)                          ← org-wide rules
  └─ skills/api-scaffold/
       ├─ SKILL.md                          ← rules: "controllers are thin", "use Actions"
       └─ examples/Controller.php           ← reference code: "this is what good looks like"

Project .claude/ directory                  ← project-specific overrides
  ├─ CLAUDE.md                             ← under 60 lines, build commands, what Claude gets wrong
  └─ .mcp.json                             ← project-scoped MCP servers

Developer ~/.claude/                        ← personal preferences
  └─ settings.json                         ← model choice, output style
```

Skills carry the **rules** (non-negotiable conventions) and **examples** (reference
implementations). Claude reads the SKILL.md for instructions and the examples/ directory
for code style. This is how you distribute architectural decisions across an organization
without bloating every project's CLAUDE.md.

The key insight: **skills are org-wide rules that load on demand.** A 200-line API
scaffolding guide would kill a CLAUDE.md. As a skill, it costs zero context until
someone actually scaffolds an API — then it loads in full.

## What each project still needs

The plugin handles org-wide patterns. Each project adds its own:

```
CLAUDE.md                ← Short. Stack, build commands, known issues.
.mcp.json                ← Project DB, Sentry, etc.
specs/                   ← Feature specs (from /spec)
checkpoints/             ← Session saves (from /context-rescue)
```

## File inventory

```
.claude-plugin/plugin.json
skills/
  api-scaffold/SKILL.md + examples/ (6 PHP files)
  docker-php/SKILL.md + templates/ (9 files)
  spec-first/SKILL.md
  feature/SKILL.md
  ai-eval/SKILL.md
  simplify/SKILL.md
  context-rescue/SKILL.md
agents/
  code-reviewer.md
  test-writer.md
  security-reviewer.md
hooks/
  hooks.json
  protect-sensitive-files.mjs
  block-main-branch.mjs
  block-dangerous-commands.mjs
  auto-format.mjs
```
