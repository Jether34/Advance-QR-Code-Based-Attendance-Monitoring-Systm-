#!/bin/bash
# Instructions helper: how to safely rewrite git history to remove confirmed secrets.
# WARNING: This rewrites history and requires coordination with all collaborators.
# Run these steps from a separate clone/mirror, not your working branch clone.

set -euo pipefail

echo "1) Create a bare mirror clone"
echo "   git clone --mirror <repo_url> repo-mirror.git"

echo "2) Enter the mirror and run git-filter-repo with mappings.txt"
echo "   cd repo-mirror.git"
echo "   # Make sure git-filter-repo is installed: https://github.com/newren/git-filter-repo"
echo "   git filter-repo --replace-text ../path/to/mappings.txt"

echo "3) Verify the rewrite locally (inspect commits, logs, tags)"
echo "   git log --all --grep 'student123' || true"

echo "4) Force-push rewritten history to origin (coordinate with team)
   git push --force --all
   git push --force --tags"

echo "5) Post-rewrite tasks:"
echo "   - Rotate any affected credentials (DB, API keys)
   - Notify all contributors to reclone the repository: rm -rf repo && git clone <repo_url>
   - Update CI tokens/secrets if needed"

echo "Notes:"
echo " - Replace '../path/to/mappings.txt' with the actual path to tools/mappings.txt in your clone."
echo " - Test the replace on a local mirror first; ensure the mapping only contains confirmed secrets."

echo "Example run (replace REPO_URL):"
echo "  git clone --mirror REPO_URL repo-mirror.git"
echo "  cd repo-mirror.git"
echo "  git filter-repo --replace-text ../Advance-QR-Code-Based-Attendance-Monitoring-Systm-/tools/mappings.txt"
echo "  git push --force --all"
