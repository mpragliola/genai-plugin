import { readFileSync } from 'fs';

const input = JSON.parse(readFileSync('/dev/stdin', 'utf8'));
const cmd = input.tool_input?.command || '';

const DANGEROUS = [
  /rm\s+-rf\s+\//,
  /git\s+reset\s+--hard/,
  /git\s+push\s+.*--force/,
  /git\s+push\s+.*-f\b/,
  /DROP\s+(TABLE|DATABASE)/i,
  /TRUNCATE\s+TABLE/i,
  /chmod\s+-R\s+777/,
  /curl\s+.*\|\s*(bash|sh|zsh)/,
];

if (DANGEROUS.some(p => p.test(cmd))) {
  process.stderr.write(JSON.stringify({
    decision: "block",
    reason: `Blocked destructive command. Ask user for explicit confirmation.`
  }));
  process.exit(2);
}
process.exit(0);
