import { readFileSync, existsSync } from 'fs';
import { extname } from 'path';
import { execSync } from 'child_process';

const input = JSON.parse(readFileSync('/dev/stdin', 'utf8'));
const filePath = input.tool_input?.file_path || input.tool_input?.path || '';
if (!filePath) process.exit(0);

const ext = extname(filePath).toLowerCase();

if (ext === '.php') {
  // 1. Auto-fix style — never blocks
  try {
    execSync(`./vendor/bin/php-cs-fixer fix "${filePath}" --rules=@PSR12 --quiet 2>/dev/null`, {
      timeout: 15000, stdio: 'pipe'
    });
  } catch (e) {}

  // 2. Lint with phpcs — blocks on violations
  if (existsSync('./vendor/bin/phpcs')) {
    const hasProjectConfig = existsSync('.phpcs.xml') || existsSync('phpcs.xml.dist');
    const standardFlag = hasProjectConfig ? '' : '--standard=PSR12';
    try {
      execSync(`./vendor/bin/phpcs ${standardFlag} "${filePath}"`, {
        timeout: 15000, stdio: 'pipe'
      });
    } catch (e) {
      process.stderr.write(e.stdout?.toString() ?? e.message);
      process.exit(1);
    }
  }
} else if (['.js', '.mjs', '.ts', '.jsx', '.tsx', '.json', '.css', '.scss'].includes(ext)) {
  try {
    execSync(`npx prettier --write "${filePath}" 2>/dev/null`, {
      timeout: 15000, stdio: 'pipe'
    });
  } catch (e) {}
}

process.exit(0);
