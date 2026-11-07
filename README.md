# COOP – Cooperative Training Management System

MIS 451 capstone, Jubail Industrial College. Built from the *COOP Development Guide* (Deliverables 1–3) with the look of the **COOP Design System**.

**Stack:** HTML5 · CSS3 + Bootstrap 5.3.3 (LTR + RTL) · vanilla JavaScript · PHP 8.1+ (no framework, PDO) · MySQL 8.0.16+ · Chart.js 4.4.1

## Run it locally

1. Install XAMPP / Laragon (PHP 8.1+ with `pdo_mysql`, `mbstring`, `fileinfo`) and MySQL 8.
2. Create the database, tables, views, triggers, event and the `coop_app` account:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
3. Copy `.env.example` to `.env`. Set `DB_PASS` to the `coop_app` password from `schema.sql`, and change that password before going live.
4. Load demo data, or create only the first admin:
   ```bash
   php database/seed.php
   php database/create_admin.php "COOP Admin" admin@ecoop.test "Coop2026!"
   ```
5. Serve `public/` as the web root:
   ```bash
   php -S 127.0.0.1:8080 -t public public/index.php
   ```
   With Apache, point the `DocumentRoot` (or a virtual host) at `coop/public`; `.htaccess` routes every request to `index.php`.
6. Make `storage/` writable by the web server.
7. Schedule the cron jobs from `cron/` (see each file's first line).

## Demo accounts (after `seed.php`)

Every demo account uses the password **`Coop2026!`**.

| Email | Role |
| --- | --- |
| `admin@ecoop.test` | COOP office admin |
| `supervisor@ecoop.test` | Academic supervisor (Dr. Khalid Omar) |
| `company@ecoop.test` | Company (Nova Systems) |
| `student@ecoop.test` | Student placed in Backend Developer Intern |
| `newstudent@ecoop.test` | Student with no placement (try applying) |

More sample accounts use the same password:
- Companies: `maaden@ecoop.test` (approved) and `gulftech@ecoop.test` (waiting for approval)
- Supervisor: `noura@ecoop.test`
- Students: `abdullah@ecoop.test`, `yazan@ecoop.test`, `reem@ecoop.test`, `lina@ecoop.test`

Emails (password reset, notifications) are written to `storage/logs/mail.log` while `MAIL_DRIVER=log`.

## Tests

With the server running on port 8080 and a fresh seed:

```bash
node tests/e2e-pages.js
```
Opens all 46 role pages and checks that cross-role URLs return 403.

```bash
mysql -u root < database/schema.sql && php database/seed.php && node tests/e2e-flow.js
```
Runs 39 checks covering the whole workflow and the security checklist in guide §3.9.

## What is implemented

Every use case from UC-01 to UC-23, plus:

- Messages and notifications, with an unread badge that polls every 30 s
- Settings: language, dark mode, email notifications, password and photo
- The public pages: home, about with the privacy notice, and the contact form
- An English and Arabic (RTL) interface

**Security:**
- bcrypt passwords
- PDO prepared statements only
- Escaped output and a Content-Security-Policy with no inline scripts
- CSRF tokens on every POST
- Session: ID regenerated at login, HttpOnly + SameSite cookies, 30-minute idle timeout
- Account locked for 15 minutes after 5 failed logins
- Check-in blocked for 10 minutes after 5 wrong codes
- Role checks on every route, plus record-level checks
- Suspended users are logged out immediately; an admin cannot suspend their own account
- Uploads: file type checked from the contents, 10 MB limit, random names, stored outside `public/`
- Append-only audit log, protected by database triggers
- A transaction with a row lock so accepting applicants can never exceed capacity

## Differences from the guide

- **No Composer packages.** A small `.env` loader replaces phpdotenv, and `MailService` logs emails instead of using PHPMailer. Install PHPMailer to send real SMTP email.
- **`database/seed.php` instead of `seed.sql`.** It hashes the passwords and creates sample PDF files.
- **One model file per table, as listed in the guide.** Most queries live in services and controllers.

## Logo and static HTML preview

- **Logo files** are in `public/assets/img/`:
  - `logo.png`: the full logo with a transparent background
  - `logo-mark.png`: the hexagon mark, used in the dashboard sidebar and as the app icon
  - `favicon.png`: the browser-tab icon
- **`coop-website.html`** is a single self-contained file holding **every page of the site for every role, in English and Arabic** (122 pages). It opens in any browser without PHP.
  - Every link leads to a real page, and CV and report links download the embedded PDFs.
  - To see a role, log in on the preview's login page with that role's demo email. Log in, Log out and Register move between pages; other forms show a "preview only" message.
- **Rebuild it after changes** while the PHP server is running with demo data:
  ```bash
  python3 tools/export_html.py
  ```

## Profile photo and language

- **Change your photo:** press the round photo (or initials) at the top right of any dashboard page, or the large photo on the Settings page. Pick a JPG, PNG or WebP up to 2 MB. It shows everywhere straight away and uploads in the background. "Change photo" is also in the account menu.
- **Switch language:** use the العربية / English button in the top bar. The same page reloads in the other language and the choice is remembered for your account.
- **In `coop-website.html`:** both work the same way. The chosen photo is saved in your browser only, because the file has no server.
- **Note on registration:** the self-registration form still requires the college domain set by `STUDENT_EMAIL_DOMAIN` (default `jic.edu.sa`). Set it to `ecoop.test` in `.env` if you want new demo sign-ups to use that domain.

## Run it on GitHub

**Live preview (GitHub Pages).** `docs/index.html` is the static preview of every page in English and Arabic.
1. After pushing, open the repository's **Settings → Pages → Source** and choose **GitHub Actions**. This is needed only once.
2. The **Deploy preview to GitHub Pages** workflow publishes it at `https://<user>.github.io/<repo>/`.
3. Pages cannot run PHP or MySQL, so forms only show a "preview only" message there.

**The real PHP + MySQL site (GitHub Codespaces).**
1. Press **Code → Codespaces → Create codespace on main**.
2. `.devcontainer/` starts PHP 8.3 and MySQL 8.0, imports `schema.sql`, loads the demo data and serves the site on port 8080.
3. The site opens in your browser. Log in with any demo account; the password is `Coop2026!`.

**Tests (GitHub Actions).** `.github/workflows/tests.yml` runs the three browser test suites against a real MySQL 8 on every push.

After changing pages, rebuild the preview while the site is running, then commit `docs/index.html`:

```bash
python3 tools/export_html.py docs/index.html
```
