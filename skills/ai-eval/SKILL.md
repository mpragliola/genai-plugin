---
name: ai-eval
description: >
  Creates evaluation suites for AI-powered features using LLM-as-judge pattern.
  Use when an AI feature needs systematic testing.
  Trigger: "evaluate", "eval", "test AI feature", "benchmark".
disable-model-invocation: true
---

# AI feature evaluation

## Rules
- Every AI feature ships with an evaluation suite. No exceptions.
- Minimum 12 test cases across 6 categories.
- Assert on structure (valid JSON, required fields, types) not exact wording.
- Use a cheaper model (Haiku) as judge. Never the same model that produced output.

## Categories (all required)

| Category | Min | Tests |
|---|---|---|
| happy_path | 3 | Clear unambiguous input |
| edge_case | 2 | Empty, very long, special chars |
| multilingual | 2 | German + English minimum |
| sarcasm | 1 | Sarcastic or ironic input |
| adversarial | 2 | Prompt injection, HTML injection |
| ambiguous | 2 | Multiple valid interpretations |

## Process

### 1. Identify feature under test
Read the Action class. Map input/output/LLM call/validation.

### 2. Generate fixtures
Create `tests/Evaluation/fixtures/<feature>.json` with 12+ test cases.

### 3. Write evaluation test
Create `tests/Evaluation/<Feature>EvalTest.php`:
- Structural assertions (always): valid JSON, required fields, correct types
- Semantic assertions (with --judge flag): second LLM evaluates correctness

### 4. Run and report
Present accuracy table by category.
Threshold: >90% go, 80-90% review, <80% iterate.
