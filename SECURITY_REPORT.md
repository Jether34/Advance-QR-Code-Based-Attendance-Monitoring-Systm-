# Security Hardening Report

Date: 2025-12-10
Repository: Advance-QR-Code-Based-Attendance-Monitoring-Systm-
Branch: update-2025-11-dev-qr

## Summary
I performed a focused security hardening of the application addressing session management, CSRF, SQL injection vectors, file upload/LLM prompt hygiene, and storage protections. High-risk issues were patched; a small set of repo-wide mechanical follow-ups are noted.

## What I changed (high-level)
- Centralized session initialization in `bootstrap.php` and replaced top-level `session_start()` in application pages with `require_once __DIR__ . '/bootstrap.php';` (core pages updated). Files that intentionally start sessions inside helper functions (e.g., `page_security.php`, `security_utils.php`) were left unchanged.
- Hardened CSV/LLM pipeline in `pdf_to_csv_converter.php`: sanitized extracted text, deterministic LLM options, rate-limiting, server-side CSV validation & sanitization, saved outputs to non-web `storage/data/` with randomized filenames and strict permissions.
- Added `tools/csv_validation_helper.php` to validate and sanitize AI CSV output and `tools/test_csv_validation.php` CLI smoke tests.
- Added storage protection artifacts: `storage/.htaccess` and `storage/nginx_deny.conf` (deny direct HTTP access).
- Converted several unsafe SQL usages to prepared statements / validated inputs where user input was present (several developer pages updated previously).
- Made CLI-only guards for scripts that executed shell commands (done earlier in audit).
- Moved DB credentials usage to environment accessors in `api_config.php` (earlier change).
- Added CSRF tokens to key state-changing handlers in earlier work (already present in core pages).

## Files changed in this session
- `bootstrap.php` (new) — centralized session startup and secure cookie params
- `recent_conversation.php` — use `bootstrap.php`
- `scanner_mobile.php` — replaced `session_start()` with `bootstrap` include
- `developer_ai_assistant.php` — replaced `session_start()` with `bootstrap` include
- `update_attendance_status.php` — replaced `session_start()` with `bootstrap` include
- `export_sf2_excel.php` — replaced `session_start()` with `bootstrap` include
- `developer_dashboard.php` — replaced `session_start()` with `bootstrap` include
- `record_attendance.php` — replaced `session_start()` with `bootstrap` include
- `export_conversation_pdf.php` — replaced `session_start()` with `bootstrap` include
- `logout.php` — replaced `session_start()` with `bootstrap` include before `session_destroy()`
- `review_center.php` — replaced `session_start()` with `bootstrap` include
- `export_sf2_pdf.php` — replaced `session_start()` with `bootstrap` include
- `developer_traffic.php` — replaced `session_start()` with `bootstrap` include
- `export_sf2_pdf_proper.php` — replaced `session_start()` with `bootstrap` include
- `edit_profile.php` — replaced `session_start()` with `bootstrap` include
- `developer_logout.php` — replaced `session_start()` with `bootstrap` include
- `wall.php` — replaced `session_start()` with `bootstrap` include
- `student_dashboard.php` — replaced `session_start()` with `bootstrap` include
- `developer_login.php` — replaced `session_start()` with `bootstrap` include
- `login.php` — replaced `session_start()` with `bootstrap` include
- `student_qr.php` — replaced `session_start()` with `bootstrap` include
- `print_monthly_sf2.php` — replaced `session_start()` with `bootstrap` include
- `review_ai.php` — replaced `session_start()` with `bootstrap` include
- `pdf_to_csv_converter.php` — added server-side CSV sanitization + uses helper; saves sanitized CSV
- `tools/csv_validation_helper.php` (new) — validates/sanitizes AI CSV output
- `tools/test_csv_validation.php` (new) — CLI smoke tests for CSV validation
- `storage/.htaccess` (new) — Apache deny rule (created earlier)
- `storage/nginx_deny.conf` (new) — Nginx deny snippet (created earlier)

Note: other security changes were applied earlier in the audit (CSRF, prepared statements, CLI-only guards, DB env usage). This report focuses on the "All" tasks executed now.

## Validation performed
- Ran `php tools/test_csv_validation.php` (CLI) to exercise the CSV validator. Results included:
  - Valid CSV: PASS
  - Malformed rows: FAIL (expected)
  - Invalid difficulty values: normalized to `Medium` with warning (configurable behavior)

## Remaining recommendations / follow-ups
1. Sweep the repository for remaining `PDO->query()` instances and convert to prepared statements where user input is present. Some analytics/developer reports still use `query()` where input is internal — audit those for potential injection paths.
2. Harden CSP in `config.php` to remove `unsafe-inline`/`unsafe-eval` where possible and replace with nonces or hashed inline scripts.
3. Secrets hygiene: move any remaining hard-coded secrets into environment variables, add `.env.example`, and remove secrets from git history using `git filter-repo` or `bfg`. Coordinate this with all operators because it rewrites history.
4. Integration smoke tests: exercise login flows (student/teacher/developer), PDF→CSV conversion end-to-end, teacher imports, and key exports in a staging environment.
5. Webserver config: deploy `storage/nginx_deny.conf` for Nginx or ensure `storage/.htaccess` is honored under Apache. Also configure `open_basedir` and PHP-FPM pools appropriately.
6. Consider rate-limiting and authentication for local LLM endpoints; if Ollama is exposed beyond localhost, require firewall rules.
7. Optionally persist AI-converted CSV rows into a normalized DB table (with validation) instead of storing CSV files directly.

## How to test locally (smoke tests)
- Run CSV validator CLI tests:

```bash
php tools/test_csv_validation.php
```

- Test PDF→CSV (manual):
  - Ensure Ollama is running locally and reachable at the configured `OLLAMA_API_URL`.
  - From the app, use the Review Center PDF upload and confirm a sanitized CSV is created under `storage/data/`.

## Deployment notes
- Add `storage/` deny rules to your webserver config.
- Ensure PHP runs with `session.cookie_secure=1` on HTTPS hosts; `bootstrap.php` sets this conditionally but verify in production.
- Create a `.env` file with DB credentials and ensure they are loaded by environment before starting the webserver / PHP-FPM.

## Contact / next steps
If you want, I can:
- A: Sweep and convert remaining `PDO->query()` usages to prepared statements and open a PR.
- B: Harden CSP across `config.php` and list pages that require inline scripts so we can replace them with safe alternatives.
- C: Run an automated scan for secrets and prepare a safe history-rewrite plan.

Select one or more and I will proceed.
