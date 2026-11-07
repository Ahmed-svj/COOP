# Run COOP in Visual Studio Code

Open the **`coop`** folder in VS Code (**File → Open Folder…**), the folder that contains `public`, `app` and `database`. When VS Code offers the **recommended extensions**, press **Install**:
- **PHP Debug** and **PHP IntelliSense**, for running and debugging
- **Dev Containers**, for option A
- **MySQL client**, to browse the database

Then choose **one** of the two ways below.

---

## Option A: Dev Container (nothing to install except Docker)

1. Install and start **Docker Desktop**.
2. In VS Code, press **F1** and choose **Dev Containers: Reopen in Container**. A pop-up offering this may also appear.
3. Wait about 2–3 minutes the first time. PHP 8.3 and MySQL 8.0 start, the database and demo data are created, and the site opens in your browser.
4. If it doesn't open, open the **PORTS** panel and click the globe next to **8080**.

It's the same setup GitHub Codespaces uses.

---

## Option B: Your own PHP and MySQL (Laragon, XAMPP or a normal install)

You need **PHP 8.1+**, with the `pdo_mysql`, `mbstring` and `fileinfo` extensions, and **MySQL 8.0.16+**, running. In a terminal, `php -v` must work; with Laragon, add `C:\laragon\bin\php\php-8.x` to your PATH or use the Laragon Terminal.

1. **Install the database:** **Terminal → Run Task… → "COOP: Install database + demo data"**.
   - It asks for the MySQL admin user (`root`) and password. Laragon and XAMPP use an **empty** password, so just press Enter.
   - It creates `coop_db` with all tables, views, triggers, the midnight event and the app account, writes `.env` and loads the demo data. It replaces an existing `coop_db`.
   - Without VS Code: `php database/install.php` (add `--root-pass=...` if root has a password).
2. **Start the site:** press **F5** ("COOP: Run website"), or use **Terminal → Run Build Task** (Ctrl+Shift+B). The site starts on **http://127.0.0.1:8080** and opens in your browser.
   - **F5** also attaches the debugger. Set a breakpoint in any controller, for example `app/controllers/Student/DashboardController.php`, and reload the page. This needs the Xdebug PHP extension; without it, the site still runs.
3. **Stop it:** the red ■ in the debug bar, or the trash-can icon on the "COOP: Start website" terminal.

---

## Log in

Every demo account uses the password **`Coop2026!`**.

| Email | Role |
|---|---|
| admin@ecoop.test | COOP office admin |
| supervisor@ecoop.test | Academic supervisor (Dr. Khalid Omar) |
| company@ecoop.test | Company (Nova Systems) |
| student@ecoop.test | Student placed in Backend Developer Intern |
| newstudent@ecoop.test | Student with no placement (try applying) |

Emails such as password resets and notifications are written to `storage/logs/mail.log`.

## Tests (optional)

Run **"COOP: Install test tools (once)"**, then, with the site running, **Terminal → Run Test Task** ("COOP: Run tests"). This checks every page for every role. `npm run test:all` runs the other suites too; those also need the `mysql` command-line client on your PATH.

## Where things are

| Folder | What |
|---|---|
| `app/controllers/` | One folder per role (Student, Company, Supervisor, Admin) |
| `app/views/` | Page templates |
| `public/assets/` | CSS, JavaScript and images |
| `database/` | `schema.sql`, `install.php`, `seed.php` |
| `.vscode/` | The tasks, F5 settings and recommended extensions used above |
