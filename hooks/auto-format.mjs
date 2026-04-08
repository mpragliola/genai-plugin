import { readFileSync } from 'fs';
import { extname } from 'path';
import { execSync } from 'child_process';

const input = JSON.parse(readFileSync('/dev/stdin', 'utf8'));
const filePath = input.tool_input?.file_path || input.tool_input?.path || '';
if (!filePath) process.exit(0);

const ext = extname(filePath).toLowerCase();

try {
  if (ext === '.php') {
    execSync(`./vendor/bin/php-cs-fixer fix "${filePath}" --rules=@PSR12 --quiet 2>/dev/null`, {
      timeout: 15000, stdio: 'pipe'
    });
  } else if (['.js','.mjs','.ts','.jsx','.tsx','.json','.css','.scss'].includes(ext)) {
    execSync(`npx prettier --write "${filePath}" 2>/dev/null`, {
      timeout: 15000, stdio: 'pipe'
    });
  }
} catch (e) {}
process.exit(0);
