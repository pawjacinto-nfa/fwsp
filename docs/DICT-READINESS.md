# DICT VAPT readiness review

Review date: 2 October 2026 (Asia/Taipei).

**Status: local application hardening completed; production and administrative readiness still require the actions below. This is not a VAPT certificate or a guarantee that the application is vulnerability-free.**

## Scope and source document

Reviewed all three scanned pages of `C:/Users/pawja/Downloads/Olarte-0010.pdf`: the DICT Web Application VAPT Request Form and Annex A test credential list. The document is an assessment request and authorization form, not a prescribed technical security standard. Its twenty-character password requirement applies to the encrypted Annex A file, not to all application passwords.

The workspace `C:/xampp/htdocs/fwsp` redirects to the actual PHP application at `C:/xampp/htdocs/fsr`. Changes were made to that local application and its local database connection. No remote deployment, remote penetration test, DICT submission, email, signature, or real-user password reset was performed. The Electron client was inspected to understand its offline dependency; its installer was not rebuilt.

## Requirements from the form

| Form item | Evidence / remaining action |
| --- | --- |
| Section 01: requestor and agency | Supply the agency/unit, office address and contact details. Requestor must be at least Director level or equivalent. Not inferred or signed here. |
| Section 02: application details | Farmer-Seller Registry; dynamic PHP application with login and PII. It stores farmer/user identifiers, contact and profile information, photos and delivery records. Confirm the actual target URL/IP, accessibility, environment, hosting/maintenance organizations, incident history and installed security appliances. A WAF/CDN checkbox is not itself a mandate to install one. |
| Section 03: vulnerability assessment | Authenticated scanning is recommended by the form for this login-based application. Supply Annex A if selected. DICT and the agency must agree on scope and timing. |
| Section 04: penetration testing | Choose the authorized test model and explicitly allowed techniques. No denial-of-service or destructive tests were performed here. White-box testing of a site with login pages also requires Annex A. |
| Section 05: stack | Observed locally: Windows, Apache 2.4.58, PHP 8.2.12, MariaDB 10.4.32, custom PHP MVC-style application, Composer QR-code dependency. Remote OS/version details remain unverified. |
| Section 06: backups | Complete local application directory copied; FSR database logically exported and restored successfully into an isolated verification database. This is not a full machine/OS image or a backup of the remote server. Obtain the required full backup and administrator attestation before the assessment. |
| Sections 07–08: contacts and authorization | Provide available technical contacts and the required Director-level authorization/signature. The authorization signatory must match Section 01. |
| Annex A | Use dedicated temporary assessment accounts for the required roles. Do not send ordinary employee passwords. Encrypt the credential file using a password of at least 20 characters including alphanumeric and special symbols; the form specifies sending that file password only through SMS or Viber to designated CERT-PH personnel. No credentials were created for submission or transmitted. |

## Changes applied

1. **Password-reset takeover fixed.** Previously an approved reset could be claimed with only a username, including through the login flow. Approval now creates a random 192-bit code, stores only its SHA-256 hash, expires it after 30 minutes, and consumes it with an atomic conditional database update. Missing, wrong, expired and replayed codes cannot update a password. Login no longer grants reset permission. The administrator sees the code once and must verify identity before sharing it through a trusted channel. Existing approvals without codes must be requested again.
2. **Authentication abuse controls.** Authentication and recovery submissions have persistent, locked server-side rate buckets: 60 attempts per source IP and 15 per normalized account identifier per 15 minutes. Cookie replacement does not reset these counters. Requests over the limit receive HTTP 429. Reset requests use a generic response and do not repeatedly replace valid approvals or generate duplicate pending notifications. Reverse proxy address handling must be configured at the server; arbitrary forwarded headers are not trusted.
3. **Session controls.** Dedicated `FSRSESSID` cookie scoped to the application path; strict session IDs; cookies only; HttpOnly and SameSite=Lax; Secure on HTTPS. Sessions expire after 30 minutes without applicable requests or eight hours maximum. Maintenance polling does not extend idle time. Login rotates the session ID and CSRF token. Pre-change sessions require a new login.
4. **Revocation and current roles.** Each authenticated request rechecks active status, current role and a password fingerprint. Account deactivation or a password change invalidates old authenticated sessions. Role changes affect existing sessions immediately. Resetting/changing a password revokes the user's registered offline authorizations on the server.
5. **Password changes.** New passwords require 12–72 bytes, allow passphrases, and reject NUL bytes and bcrypt's silent overlength truncation. Existing passwords are not forcibly changed. Account password changes require the current password. Registration/reset/account-change validation is enforced server-side.
6. **Web file protection.** Directory listings disabled. Internal code, database files, sessions, source-control metadata, logs, configuration, development artifacts and SQL/archive exports are denied by Apache. Upload directories cannot execute PHP/CGI or serve active HTML/SVG/JavaScript content.
7. **Private uploaded media.** Existing farmer image and support screenshot URLs go through authentication instead of direct static delivery. Farmer media uses the application's record-viewing roles; support screenshots additionally require access to their ticket. Profile images require login. Public display images remain public. Files are constrained to the upload directory and checked for permitted image MIME types.
8. **Errors and response headers.** Unexpected exception details and stack traces stay in server logs. Browser responses contain an error reference and generic explanation. Dynamic responses use `Cache-Control: no-store, private`; basic CSP restricts framing, objects, base URLs and form targets. Existing inline scripts mean this is not a full script-src XSS policy. PHP identification header removed. HTTPS responses receive HSTS; the configured sandbox hostname redirects HTTP to HTTPS. Local HTTP remains usable for development.
9. **Database access.** The local app uses `fsr_app@127.0.0.1` with a generated password in `C:/xampp/fsr-private/database.php`, outside the web root. Grants are limited to `fsr.*`: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX and REFERENCES. Schema permissions are still necessary because the application performs migrations during requests. No global, FILE, GRANT or other-schema rights were granted. The shared root account and other applications were not changed.
10. **Offline cache exposure.** The service worker previously cached arbitrary signed-in responses. It now caches only public CSS/JS/images and deletes old `fsr-*` caches when activated. Authenticated pages, uploaded records and tokens are not cached. Offline page installation is disabled and the account screen explains this. **This also affects the desktop client's cached pages.** Existing pending submissions are not deleted; reconnect to upload them. Offline page access must be redesigned with user-bound encrypted storage and revocation before it is re-enabled. Existing installations receive the updated worker only when they reconnect; already downloaded old installers/caches cannot be remotely guaranteed erased.

## Verification completed

- 11 model/security checks: hashed reset tokens; missing/wrong/valid/expired tokens; wrong-token update rejection; valid reset; replay rejection; expired-update rejection; password boundaries; persistent rate limiting.
- 9 HTTP authentication checks using synthetic accounts in an isolated database: missing CSRF rejected; anonymous administration denied; administrator login and session rotation; non-administrator restriction; current role enforcement; deactivation revocation; idle expiry; password-change revocation; HTTP 429 after repeated attempts.
- 4 HTTP recovery workflow checks: username-only takeover denied; administrator-issued code accepted; reset followed by successful login; reused code rejected.
- Local Apache smoke checks: home and stylesheet return 200; `.git/config`, SQL and compressed SQL exports, internal PHP, session directory and Composer lockfile return 403; anonymous private media requests return 403; an invalid schedule token returns 404.
- Local HTTPS returns Secure cookies and HSTS. Certificate trust was bypassed only for this localhost header check; a trusted production certificate was not validated.
- All application PHP files passed syntax checks. Service-worker JavaScript passed syntax checking. Apache configuration syntax passed. Composer audit of the lockfile reported no known dependency advisories at the time checked; this does not cover PHP, Apache, MariaDB, Electron, or CDN resources.
- The local app connection and schema migration were verified under the dedicated database user. MySQL remains running on port 13306.
- Full FSR database export restored successfully into a separate verification database; test accounts and changes were confined there. This verifies logical database restoration, not full disaster recovery.

Regression scripts are in `tests/security-model.php`, `tests/security-http.py`, and `tests/security-reset-http.py`. They expect the explicitly named isolated database and loopback test server, never a deployed target.

Cleanup limitation: automatic approval review blocked the combined deletion command without a detailed reason. The temporary HTTP server was subsequently stopped and is no longer listening on port 18089. The isolated database `fsr_security_verify_20261002` (including synthetic test accounts and a restored copy of application data), `C:/xampp/fsr-private/test-data`, test configuration/logs in that private directory, and the named PDF/script intermediates under `C:/xampp/htdocs/fwsp/tmp` remain. The private directory is protected by Windows permissions; the fwsp paths redirect to the denied FSR tmp path. Arrange removal of these test artifacts through the administrator's normal cleanup process. Keep the actual pre-change backup and live private database configuration.

## Backup and rollback

Pre-change backup: `C:/xampp/security-backups/fsr-before-dict-20261002-105102`.

- `application/`: all 3,872 original files, 1,420,898,615 bytes, including uploads and existing local artifacts.
- `fsr.sql`: logical FSR database export including triggers, routines and events, 26,709,954 bytes.
- The backup directory and private configuration directory have Windows permissions restricted to the current Apache/user account, Administrators and SYSTEM. Preserve an encrypted off-host copy according to agency policy; the local backup itself is not encrypted.

To roll back a code regression, stop application writes and restore only the changed source/configuration files from `application/`. The database changes add nullable reset-token columns; leave these in place for a code rollback. Do not overwrite the current database with the backup after new work has been entered. Restoring the old reset implementation reintroduces account takeover and is not recommended. The dedicated database account can be retained. Do not remove it while the app is using it.

## Remaining before deployment / submission

1. **Upgrade the hosting stack.** The local XAMPP components are old: Apache 2.4.58 and PHP 8.2.12 need current security-patched replacements. MariaDB 10.4 maintenance ended 18 June 2024. Plan and test migration to a maintained version with backups and a rollback window. This shared installation hosts other databases; its binaries were not replaced during this application change. The desktop package also pins Electron 28.3.3 and needs a separately tested update/rebuild before further distribution.
2. **Deploy and verify on the actual assessment host.** Local changes are not automatically present on `sandbox.nfa.gov.ph`. Deploy code and additive schema changes, configure a dedicated database account and secret storage, and verify Apache AllowOverride/rewrite/header behavior. `FSR_DB_CONFIG_FILE` can point to a private PHP array with username/password, or use the existing `FSR_DB_HOST`, `FSR_DB_PORT`, `FSR_DB_NAME`, `FSR_DB_USER`, `FSR_DB_PASSWORD` environment variables. `FSR_DATA_PATH` can place writable session/rate-limit data outside the web root. Do not copy local secrets or database exports into the public deployment.
3. **Check host controls.** Validate the public certificate, TLS settings, redirect behavior, proxy handling, firewall exposure, database binding, operating-system patches, file permissions, and production PHP settings (`display_errors=Off`, `log_errors=On`, `expose_php=Off`). Remove or restrict development dashboards/phpMyAdmin on the assessment host. Local phpMyAdmin was observed configured for local access, but remote hosting controls are unverified.
4. **Confirm authorization policy.** Current record-viewing roles can view records across the scopes permitted by the existing application, and account settings allow location reassignment. If office-level record isolation is required, define that policy and enforce it consistently in queries, record updates, media and reporting. This review did not assume that location defaults constituted an access-control policy.
5. **Operational readiness.** Take fresh source/database/full-host backups of the actual assessment environment, test recovery, and obtain the administrator attestation. Agree with DICT on test schedule, sensitive-data use, contacts, allowed techniques and assessment accounts. Use sanitized staging data where feasible and revoke temporary accounts after testing. No director signature, production backup or credential submission can be inferred from this local work.

## References

- DICT form and Annex A: supplied PDF, pages 1–3.
- [OWASP password recovery guidance](https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html): random, expiring, single-use recovery tokens.
- [OWASP session guidance](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html): server-side idle and absolute expiry.
- [MariaDB maintenance dates](https://mariadb.org/about/).
- [PHP supported branches](https://www.php.net/supported-versions.php): PHP 8.2 security support ends 31 December 2026; support for a branch does not make an old patch build current.
- [Apache security advisories](https://httpd.apache.org/security/vulnerabilities_24.html).
