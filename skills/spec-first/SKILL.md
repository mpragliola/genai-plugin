---
name: spec
description: >
  Specification-first development. Use BEFORE writing any code for non-trivial features.
  Creates a structured spec, gets approval, then delegates to implementation.
  Trigger: "spec", "plan feature", "design", "architect".
disable-model-invocation: true
---

# Spec-first development

Unguided AI coding succeeds ~33% of the time. Spec-first raises it to >80%.

## Rules
- Never write implementation code before a spec is approved.
- Specs live in `specs/<feature-name>.md` and are committed alongside code.
- Specs are living documents — update them when requirements change.

## Step 1: Gather requirements
Use AskUserQuestion. Ask 3-5 questions max:
- What problem does this solve? Who uses it?
- What's the input? What's the expected output?
- Edge cases the user already knows about?
- Constraints? (performance, backward compatibility, specific libraries)

## Step 2: Write the spec
Create `specs/<feature-name>.md`:
```markdown
# Feature: <n>
## Problem
One sentence.
## Solution
2-3 sentences.
## Files to create/modify
- path → what changes
## Acceptance criteria
- [ ] Criterion (testable)
## Edge cases
- Case → expected behavior
## Out of scope
- What this does NOT do
```

## Step 3: Present and wait
Show the spec. Ask: "Proceed or adjust?" Do NOT write code until approved.

## Step 4: Implement
Once approved, invoke `/genai-enabler:feature` with the spec as context.
