# Run COOP in Laragon (Windows)

About 5 minutes. You need **Laragon 6 or newer** (it includes **PHP 8.1+** and **MySQL 8.0**).

## 1. Put the project in Laragon's `www` folder

Unzip `coop-laragon.zip` so that you have **`C:\laragon\www\coop`**, with `public\`, `app\` and `database\` inside it. Alternatively, clone the repository:

```bash
cd C:\laragon\www
```

```bash
git clone https://github.com/Ahmed-svj/COOP.git coop
```

## 2. Start Laragon

1. Open Laragon and press **Start All** (Apache and MySQL).
2. Check the versions: **Menu → PHP → Version** must be **8.1 or newer**, and **Menu → MySQL** must be **8.0**. MySQL 5.7 is not enough.
3. Make sure **Menu → Preferences → General → Auto virtual hosts** is on. Laragon then serves the folder as **http://coop.test**.

## 3. Run the installer

In Laragon press **Terminal**, then:

```bash
cd C:\laragon\www\coop
```

```bash
install-laragon.bat
```

The installer:
- checks PHP and its extensions (pdo_mysql, fileinfo, mbstring)
- creates the `coop_db` database with all tables, views, triggers, the midnight event and the app account
- writes `.env`
- loads the demo data

If your MySQL root user has a password, run `set COOP_MYSQL_ROOT_PASSWORD=yourpass` first.

## 4. Open the site

Go to **http://coop.test**. If the name doesn't open the first time, right-click Laragon → **Apache → Reload**.

Every demo account uses the password **`Coop2026!`**.

| Email | Role |
|---|---|
| admin@ecoop.test | COOP office admin |
| supervisor@ecoop.test | Academic supervisor (Dr. Khalid Omar) |
| company@ecoop.test | Company (Nova Systems) |
| student@ecoop.test | Student placed in Backend Developer Intern |
| newstudent@ecoop.test | Student with no placement (try applying) |

Emails such as password resets and notifications are written to `storage\logs\mail.log`.

## 5. Optional: scheduled jobs

These are the Windows version of the cron jobs: expire daily codes, complete placements, attendance reminders, weekly summary, and a daily backup into `storage\backups`. From the Laragon Terminal:

```bash
cron\register-tasks.bat
```

They appear in **Task Scheduler** under a **COOP** folder. The midnight expiry of daily codes also runs inside MySQL itself, as long as the event scheduler is on (the installer turns it on).

## Good to know

- **Use http://coop.test, not http://localhost/coop/.** The site expects to sit at the root of its own address.
- **Private files stay private whether Laragon serves `coop` or `coop\public`.** The root `.htaccess` sends every request into `public\`, so `.env`, `app\`, `database\` and `storage\` can't be opened from the browser.
- **To start again with fresh demo data, run `install-laragon.bat` again.** It replaces `coop_db`.
- **To use your own data instead of the demo:** import `database\schema.sql` only, then create the first admin:

```bash
php database\create_admin.php "Your Name" you@jic.edu.sa "YourPass123"
```
