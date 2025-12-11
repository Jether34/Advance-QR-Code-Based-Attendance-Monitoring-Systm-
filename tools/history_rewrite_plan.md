History rewrite & credential rotation plan

Purpose:
- If `detect-secrets` or other scans find real secrets in the repository history, follow this plan to remove them, replace credentials, and rotate secrets safely.

Summary steps (high-level):
1) Confirm which files/commits contain secrets.
2) Coordinate with team: inform maintainers and schedule a maintenance window; rewriting history requires force-push and will affect all clones.
3) Export a list of affected credentials and prepare new credentials before scrubbing (so you can rotate immediately after rewrite).
4) Rewrite history using `git filter-repo` (preferred) or BFG.
5) Force-push the rewritten history to the remote and notify all contributors to reclone or follow rebase instructions.
6) Rotate any leaked credentials (database, API keys, tokens) and revoke old keys.
7) Add preventing measures: pre-commit hooks (detect-secrets), CI secrets scanning, `.gitignore` rules, and secret management (Vault/Secrets Manager).

Detailed commands (example using `git filter-repo`):

# Install git-filter-repo (system package or pip)
# Debian/Ubuntu (example):
# sudo apt-get install git-filter-repo

# Example: remove a literal secret or filename (replace PATTERN/FILE accordingly)
# Backup current repo first
git clone --mirror <repo_url> repo-mirror.git
cd repo-mirror.git

# Remove a file entirely from history (e.g., secrets.txt)
git filter-repo --invert-paths --paths "secrets.txt"

# Remove specific secrets via replace
# Create a file mappings.txt with lines: "OLD_SECRET==>REDACTED"
# Then run:
git filter-repo --replace-text mappings.txt

# After successful rewrite:
# Push rewritten history (force) to origin
git remote add origin <remote_url>
git push --force --all
git push --force --tags

# Post-rewrite steps:
# - Notify all contributors to reclone: rm -rf repo && git clone <remote_url>
# - Rotate affected credentials immediately

Using BFG Repo-Cleaner (alternative):
# Install BFG (Java jar) and run:
java -jar bfg.jar --delete-files secrets.txt
java -jar bfg.jar --replace-text passwords.txt

# Verify changes and push --force

Recommendations & safe-guards:
- Never perform this alone — coordinate with team and CI owners.
- Prepare new credentials before rewriting so you can rotate them immediately.
- Use `detect-secrets` to create a baseline and add it to the repo (as a way to track allowed secrets).
- Add pre-commit hook with `detect-secrets` to prevent future commits with secrets.

If you'd like, I can:
- (1) run `detect-secrets audit` interactively to help verify each finding, or
- (2) prepare `mappings.txt` for `git filter-repo` for any confirmed leaked secrets found in the baseline.
