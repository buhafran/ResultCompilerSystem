#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "[1/7] PHP syntax"
find "$ROOT/backend/app" "$ROOT/backend/bootstrap" "$ROOT/backend/config" "$ROOT/backend/database" "$ROOT/backend/routes" "$ROOT/backend/tests" -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null

echo "[2/7] JSON and Composer metadata syntax"
node -e "for (const p of process.argv.slice(1)) JSON.parse(require('fs').readFileSync(p,'utf8'))" \
  "$ROOT/backend/composer.json" "$ROOT/backend/package.json" \
  "$ROOT/mobile/package.json" "$ROOT/mobile/app.json" "$ROOT/mobile/eas.json"
if command -v composer >/dev/null 2>&1; then
  (cd "$ROOT/backend" && composer validate --no-check-publish --strict)
else
  echo "Composer executable not installed; Composer schema validation skipped."
fi


echo "[3/7] Deployment YAML and release metadata"
python - "$ROOT" <<'PY'
import json, pathlib, sys
root = pathlib.Path(sys.argv[1])
try:
    import yaml
except ImportError:
    yaml = None
if yaml is not None:
    for file in [root / 'deploy/docker-compose.yml']:
        with file.open() as handle:
            yaml.safe_load(handle)
else:
    print('PyYAML not installed; YAML syntax validation skipped.')
version = (root / 'VERSION').read_text().strip()
mobile_version = json.loads((root / 'mobile/package.json').read_text())['version']
if mobile_version != version:
    raise SystemExit(f'Mobile version {mobile_version} does not match release {version}.')
for unwanted in root.rglob('*'):
    if unwanted.is_file() and unwanted.name == '.env':
        raise SystemExit(f'Production .env file must not be packaged: {unwanted}')
print(f'Release metadata and deployment YAML validated for version {version}.')
PY

echo "[4/7] TypeScript/TSX syntax"
if command -v npm >/dev/null 2>&1; then
  (cd "$ROOT/mobile" && node <<'NODE'
const fs = require('fs');
const path = require('path');
const cp = require('child_process');
const ts = require(path.join(cp.execSync('npm root -g').toString().trim(), 'typescript'));
const files = [];
function walk(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const file = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(file);
    else if (/\.tsx?$/.test(entry.name)) files.push(file);
  }
}
walk('app');
walk('src');
let failed = false;
for (const file of files) {
  const result = ts.transpileModule(fs.readFileSync(file, 'utf8'), {
    compilerOptions: { target: ts.ScriptTarget.ES2022, module: ts.ModuleKind.ESNext, jsx: ts.JsxEmit.ReactJSX },
    fileName: file,
    reportDiagnostics: true,
  });
  for (const diagnostic of result.diagnostics ?? []) {
    if (diagnostic.category === ts.DiagnosticCategory.Error) {
      failed = true;
      console.error(`${file}: ${ts.flattenDiagnosticMessageText(diagnostic.messageText, ' ')}`);
    }
  }
}
if (failed) process.exit(1);
console.log(`${files.length} TypeScript/TSX files syntax-transpiled successfully.`);
NODE
  )
else
  echo "Node/npm not installed; TypeScript syntax validation skipped."
fi

echo "[5/7] Pure result logic"
php -r "require '$ROOT/backend/app/Support/CompetitionRanker.php'; if (App\\Support\\CompetitionRanker::rank(['a'=>100,'b'=>90,'c'=>90,'d'=>80]) !== ['a'=>1,'b'=>2,'c'=>2,'d'=>4]) exit(1);"
php -r "function config(\$k,\$d=null){return \$d;} require '$ROOT/backend/app/Support/GradeScale.php'; \$s=App\\Support\\GradeScale::from([['grade'=>'A','min'=>70,'remark'=>'Excellent'],['grade'=>'D','min'=>45,'remark'=>'Fair'],['grade'=>'F','min'=>0,'remark'=>'Fail']]); if(\$s->evaluate(70)['grade']!=='A'||\$s->evaluate(45)['grade']!=='D'||\$s->evaluate(44.99)['grade']!=='F')exit(1);"

echo "[6/7] Static workflow safeguards"
if grep -R --include='*.php' -n -- '->form(' "$ROOT/backend/app/Filament"; then
  echo "Filament 5 custom actions must use schema(), not form()." >&2
  exit 1
fi
python - "$ROOT/backend/routes" <<'PY'
import pathlib, re, sys
root = pathlib.Path(sys.argv[1])
seen = {}
for file in root.glob('*.php'):
    text = file.read_text()
    for name in re.findall(r"->name\(['\"]([^'\"]+)['\"]\)", text):
        if name in seen:
            raise SystemExit(f"Duplicate route name {name!r}: {seen[name]} and {file}")
        seen[name] = file
print(f"{len(seen)} named routes checked; no duplicates.")
PY

echo "[7/7] Dependency-aware runtime checks"
if [ -f "$ROOT/backend/vendor/autoload.php" ]; then
  (cd "$ROOT/backend" && php artisan test)
else
  echo "Composer dependencies not installed; Laravel runtime tests skipped."
fi
if [ -d "$ROOT/mobile/node_modules" ]; then
  (cd "$ROOT/mobile" && npm run typecheck)
else
  echo "Mobile dependencies not installed; full TypeScript typecheck skipped."
fi

echo "Validation completed."
