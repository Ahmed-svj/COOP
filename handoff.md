# Handoff — COOP website

## 1. Goal

Build the COOP Cooperative Training Management System website described in `COOP_Development_Guide.md` (MIS 451, Jubail Industrial College). Use the guide's stack: HTML, CSS with Bootstrap 5 (left-to-right and Arabic right-to-left), plain JavaScript, PHP 8 with PDO, and MySQL 8. Give it the look of the reference dashboard through the **COOP Design System** artifact (https://claude.ai/artifact/VoAPnAqEypXipfKxeKk6je).

## 2. Current State

- **Working site in `/home/user/coop`.** It follows the guide's folder tree: front controller, router, middleware, controllers per role, models, services, views, en/ar language files, `storage/` outside the web root, and cron jobs.
- **Tested against MySQL 8.0.46 and PHP 8.3** in this session, using the guide's `schema.sql` imported unchanged. The app connects as the least-privilege `coop_app` account.
- **All 23 use cases work**, plus messages, notifications, settings, the public pages, the contact inbox, Arabic right-to-left layout, dark mode and the mobile layout.
- **Tests pass:**
  - `tests/e2e-pages.js`: all 46 role pages load with no PHP or JavaScript errors, and all 4 cross-role URL checks return 403.
  - `tests/e2e-flow.js`: 39 of 39 checks pass, covering the full workflow and every item in the guide's §3.9 testing checklist.
- **Logo:** your logo is used in the header, dashboard sidebar, login card, footer and browser tab (`public/assets/img/logo.png`, `logo-mark.png`, `favicon.png`).
- **Static preview:** `coop-website.html` is one self-contained file holding all 61 pages in English and Arabic (122 in total). All 1,674 links lead to real pages, and the CV and report downloads are embedded. Rebuild it with `python3 tools/export_html.py`.
- **Demo accounts:** every account uses the password `Coop2026!`: `admin@ecoop.test`, `supervisor@ecoop.test` (Dr. Khalid Omar), `company@ecoop.test` (Nova Systems), `student@ecoop.test` (placed in Backend Developer Intern) and `newstudent@ecoop.test` (no placement).
- **Profile photo and language:** pressing the avatar opens a picker and the photo updates everywhere at once; an العربية / English button sits in every dashboard's top bar. Both also work in the HTML file. Tested by `tests/e2e-avatar-lang.js` (10 of 10 pass).
- **Bug fixed:** going back to the previous page ignored the port number, so on addresses like `127.0.0.1:8080` the language switch and other actions went to the home page.
- **Student dashboard:** the Settings card was removed and replaced with the latest notifications. Settings stays on its own page.
- **Not deployed.** It is not on a public URL and not in a git repository. A zip of the project is at `/home/user/coop/coop-website.zip`.

## 3. Active Files

| Area | Files |
|---|---|
| Entry and core | `public/index.php`, `app/bootstrap.php`, `app/routes.php`, `app/core/*` (Database, Router, View, Session, Auth, Csrf, Validator, Upload, Lang, Security, helpers) |
| Business logic | `app/services/` (PlacementService, DailyCodeService, AttendanceService, NotificationService, AuditService, MailService, ReportService) |
| Pages | `app/controllers/{Student,Company,Supervisor,Admin}/*`, `app/views/**` |
| Look | `public/assets/css/main.css` (tokens copied from the design system), `dashboard.css` (component classes from the design system plus the app's layout), `auth.css`, `rtl.css` |
| Front-end behaviour | `public/assets/js/` (main, validation, opportunities, attendance, notifications, messages, charts) |
| Data | `database/schema.sql` (from the guide), `seed.php`, `create_admin.php` |
| Jobs | `cron/expire_daily_codes.php`, `complete_placements.php`, `backup.sh` |
| Tests | `tests/e2e-pages.js`, `tests/e2e-flow.js` |

## 4. Changes Made

1. Installed MySQL 8 in the session container and imported the guide's schema. That created 18 tables, 3 views, 2 audit-log triggers, the midnight event and the `coop_app` user.
2. **Core framework:**
   - Router with `{id}` parameters and middleware (auth, guest, role, and CSRF on every POST).
   - Secure session: ID regenerated at login, HttpOnly and SameSite cookies, 30-minute idle timeout.
   - Security headers, including a Content-Security-Policy with no inline scripts.
3. **The main workflow:**
   - Company registers, admin approves it.
   - Company posts an opportunity, admin approves it.
   - Student applies, company accepts in a transaction with a row lock, so capacity is never exceeded. The first acceptance creates the placement and asks for its dates.
   - Admin assigns a supervisor; the assignment screen shows each supervisor's load.
   - Company generates the daily code using a secure random generator; the student checks in; the supervisor approves or rejects (rejecting needs a comment).
   - Student uploads the report; the supervisor approves it or requests a revision; the student re-uploads.
   - Company and academic evaluations, scored 0–100. The academic one is allowed only after the placement is completed and the report approved.
4. Every status change creates a notification (plus an email if the user wants one) and an audit-log entry.
5. Built every page from the design system's components. Charts use Chart.js and take their colours from the design tokens.
6. Wrote complete English and Arabic text files (458 keys).
7. **Bugs found by testing and fixed:**
   - Page titles and scripts never reached the layout, so the analytics charts were blank and every page had the default title.
   - Password-reset and notification emails contained relative links.
   - Latin-script names were punctuated wrongly in the Arabic layout.
   - The hero "Today's code" card spilled out of its box.
   - The logo showed in the mobile bottom bar.

## 5. Failed Attempts

- **MySQL stopped once** between sessions, which broke the first re-export. Fixed with `service mysql start`; check that first if anything fails to connect.

- **CSRF status code.** I first expected a POST without a CSRF token to return 419. PHP turns it into a 302 because the response also redirects back with a message. The request is still refused, so the test now checks that nothing changed in the database.
- **Test-script fixes, not app bugs:**
  - Playwright's dropdown helper doesn't accept a regular expression; the test now selects by value.
  - One CV-permission check used a student who had in fact applied to that company, so the download was correctly allowed. The test now uses a student who never applied there.
- **A typo I introduced and fixed.** A `sed` edit removed a closing bracket in `NotificationService.php`; the page test caught it.
- **Connectors.** GitHub, Figma and the other design connectors failed to connect in this session (the proxy refused with 403). Nothing was pushed to a repository.

## 6. Next Steps

1. **Put it in your team's repository** and deploy it on XAMPP or your server (steps in `README.md`). Change the `coop_app` password in `schema.sql` and `.env`.
2. **Real email.** Install PHPMailer with Composer and replace `MailService::send()`. Until then, emails go to `storage/logs/mail.log`.
3. **Enable the MySQL event scheduler** (`event_scheduler=ON` in `my.cnf`) or schedule the cron files. Also schedule `backup.sh`.
4. **Production:** HTTPS only, `APP_DEBUG=false`, and an absolute `APP_URL` so email links point at the real domain.
5. **Optional extras:**
   - Group conversations (the schema supports them; the interface only does direct messages).
   - An editor for the required training days (currently `REQUIRED_TRAINING_DAYS` in `.env`).
   - Admin-side editing of supervisor load.
   - PDF export with dompdf.
6. **Before the capstone demo:** run both test scripts again, and do a manual check at 360px wide and in Arabic.
