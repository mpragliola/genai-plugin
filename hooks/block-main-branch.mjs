import { execSync } from 'child_process';

try {
  const branch = execSync('git branch --show-current 2>/dev/null', { encoding: 'utf8' }).trim();
  if (branch === 'main' || branch === 'master') {
    process.stderr.write(JSON.stringify({
      decision: "block",
      reason: `Blocked: cannot edit on ${branch}. Create a feature branch first.`
    }));
    process.exit(2);
  }
} catch (e) {}
process.exit(0);
