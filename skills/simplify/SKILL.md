---
name: simplify
description: >
  Second-pass simplification of AI-generated code. Catches verbosity,
  over-engineering, dead code. Use after any significant code generation.
---

# Simplify

AI-generated code trends verbose. Run this after generation to trim the fat.

## What to cut
1. Dead code: unused imports, unreachable branches, commented-out code
2. Over-abstraction: interfaces with one implementor, unnecessary factories
3. Redundant null checks at multiple levels
4. Verbose patterns: `if ($x === true)` → `if ($x)`
5. Copy-paste artifacts that should be extracted
6. Type widening: `mixed` where a specific type exists

## Process
1. `git diff --name-only` → list changed files
2. Per file: identify simplifications
3. Apply. Never alter behavior. Tests must still pass.
4. Run test suite to verify.

## Rule
If you can't explain why a simplification improves the code, don't make it.
