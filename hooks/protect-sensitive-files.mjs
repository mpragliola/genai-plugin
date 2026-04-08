import { readFileSync } from 'fs';
import { basename } from 'path';

const input = JSON.parse(readFileSync('/dev/stdin', 'utf8'));
const filePath = input.tool_input?.file_path || input.tool_input?.path || '';
if (!filePath) process.exit(0);

const filename = basename(filePath);
const BLOCKED = [
  /^\.env($|\.)/i,
  /\.pem$/i, /\.key$/i, /^id_rsa/i,
  /composer\.lock$/i, /package-lock\.json$/i, /pnpm-lock\.yaml$/i,
];

if (BLOCKED.some(p => p.test(filename))) {
  process.stderr.write(JSON.stringify({
    decision: "block",
    reason: `Blocked: "${filename}" is sensitive/lock file. Ask user before modifying.`
  }));
  process.exit(2);
}
process.exit(0);
