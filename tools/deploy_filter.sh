#!/usr/bin/env bash
set -euo pipefail

# deploy_filter.sh - Build a production-ready zip excluding sensitive files
# Usage: ./tools/deploy_filter.sh [output.zip]

OUTPUT_ZIP=${1:-deploy-artifact.zip}
ROOT_DIR=$(cd "$(dirname "$0")/.." && pwd)
TMP_DIR=$(mktemp -d)

# Files and patterns to exclude from production artifacts
EXCLUDES=(
  ".git" ".github" ".devcontainer" "node_modules" "vendor" # adjust if using composer vendor in prod
  "*.map" "*.sql" "*.md" "*.ps1" "*.bat" "*.sh" "database_commands.txt"
  ".env" ".env.local" ".env.*" "ENV" "Dockerfile" "docker-compose.yml"
  "tools" "storage" "tests" "MOBILE_TESTING_*" "ORACLE_CLOUD_*" "REVIEW_CENTER_README.md"
)

echo "Preparing filtered copy..."
rsync -a --delete --exclude-from=<(printf "%s\n" "${EXCLUDES[@]}") "$ROOT_DIR/" "$TMP_DIR/"

# Optional: keep storage structure but no files
mkdir -p "$TMP_DIR/storage"
> "$TMP_DIR/storage/.gitkeep"

# Optional: composer vendor include (uncomment if needed)
# if [ -f "$ROOT_DIR/composer.json" ]; then
#   pushd "$ROOT_DIR" >/dev/null
#   composer install --no-dev --prefer-dist --no-interaction
#   popd >/dev/null
#   rsync -a "$ROOT_DIR/vendor/" "$TMP_DIR/vendor/"
# fi

# Create zip
pushd "$TMP_DIR" >/dev/null
zip -r9 "${OUTPUT_ZIP}" . >/dev/null
popd >/dev/null

mv "$TMP_DIR/${OUTPUT_ZIP}" "$ROOT_DIR/${OUTPUT_ZIP}"
rm -rf "$TMP_DIR"

echo "Created: ${OUTPUT_ZIP}"
echo "Excluded patterns:"
printf " - %s\n" "${EXCLUDES[@]}"
