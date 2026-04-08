---
name: feature
description: >
  TDD feature implementation pipeline. Orchestrates test-writer and code-reviewer
  subagents. Use after /spec approval or directly for small features.
  Trigger: "implement", "build feature", "TDD".
disable-model-invocation: true
---

# Feature development pipeline

## Rules
- Always plan before coding. No exceptions.
- Tests before implementation (TDD). Red → Green → Refactor.
- Code review before commit. Always.
- Commits on feature branches only. Never main.

## Pipeline

### 1. Plan
Switch to Plan mode. If a spec exists in `specs/`, read it. Outline:
- Files to create/modify
- Action classes for business logic
- Form Requests for validation
- Routes and controller methods

Present plan. Wait for approval.

### 2. Test (RED)
Delegate to **test-writer** agent:
> "Write failing tests for: [feature]. Tests should fail — implementation doesn't exist yet."

Confirm tests are written and failing.

### 3. Implement (GREEN)
Write minimal code to pass tests. Run test suite to confirm green.

### 4. Refactor
- Split methods > 20 lines
- Extract repeated logic
- Full type coverage
- Remove dead code

### 5. Review
Delegate to **code-reviewer** agent:
> "Review all changed files against CLAUDE.md and org conventions."

Fix any blocking issues.

### 6. Commit
- Branch: `feature/<short-description>`
- Message: imperative mood, max 72 chars, body explains why
- Stage only related files

### 7. Report
Summarize: files changed, tests written, pass rate, review result, branch name.
