---
name: context-rescue
description: >
  Saves session progress to a checkpoint file before context degrades.
  Use at 50-60% context, before task switches, or before /compact.
disable-model-invocation: true
---

# Context rescue

Auto-compaction fires at ~83.5% and retains only 20-30% of details.
This skill saves a structured checkpoint to disk YOU control.

## When to use
- Context bar approaching 50-60%
- Switching to a different task
- Before /compact
- Before ending a long session you'll resume later

## Process
Create `checkpoints/<timestamp>-<task>.md`:
```markdown
# Checkpoint: <task>
Created: <timestamp>

## Done
- File changes (list with description)
- Decisions made and why

## Current state
- Working / broken / blocked

## Next steps
- Immediate next action
- Remaining work

## Key context
- Important names, endpoints, config values
- Anything that would take time to re-discover
```

Commit: `git add checkpoints/ && git commit -m "checkpoint: <task>"`

Tell user: "Checkpoint saved. /clear safely. Resume with: 'Read checkpoints/<file> and continue.'"
