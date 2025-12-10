#!/usr/bin/env bash
set -euo pipefail

# Install helper for pre-commit + detect-secrets
echo "Installing pre-commit and detect-secrets (user-level)..."
python3 -m pip install --user --upgrade pre-commit detect-secrets || true

echo "Installing pre-commit hooks in this repo..."
python3 -m pip install --user pre-commit || true
~/.local/bin/pre-commit install || pre-commit install || true

echo
echo "Done. To run hooks against all files now, run:"
echo "  ~/.local/bin/pre-commit run --all-files"
echo "Or if you have pre-commit on your PATH simply:"
echo "  pre-commit run --all-files"

echo
echo "Notes:"
echo " - The detect-secrets hook uses the baseline at 'tools/detect_secrets.baseline'."
echo " - If you update the baseline intentionally, commit the updated baseline.
"