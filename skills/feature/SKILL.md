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

Delegate to **test-writer** agent with two parallel tasks:

**Task A — Behat (acceptance layer):**
> "Write failing Behat scenarios for: [feature]. Suite: api.
>  Run `vendor/bin/behat --suite=api`, confirm all scenarios fail."

**Task B — PHPUnit (unit + feature layer):**
> "Write failing PHPUnit tests for: [feature].
>  Run `php artisan test` (Laravel) or `vendor/bin/phpunit` (Symfony/agnostic),
>  confirm all tests fail."

Do NOT proceed to Step 3 until:
- All Behat scenarios are written and confirmed failing
- All PHPUnit tests are written and confirmed failing

Report: "X Behat scenarios written (all failing). Y PHPUnit tests written (all failing)."

### 3. Implement (GREEN)

Write minimal code to pass tests. Implementation is complete when BOTH pass:
- `vendor/bin/behat --suite=api` exits 0
- `php artisan test` (Laravel) or `vendor/bin/phpunit` (Symfony/agnostic) exits 0

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
