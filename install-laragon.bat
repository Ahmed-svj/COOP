@echo off
setlocal EnableExtensions
rem ===================================================================
rem  COOP - one-click setup for Laragon (Windows)
rem  Run it from the Laragon Terminal (Menu > Terminal) inside C:\laragon\www\coop :
rem      install-laragon.bat
rem  If your MySQL root user has a password:  set COOP_MYSQL_ROOT_PASSWORD=yourpass
rem ===================================================================
cd /d "%~dp0"
echo.
echo  COOP setup for Laragon
echo  ----------------------
where php >nul 2>&1 || goto :noterminal
where mysql >nul 2>&1 || goto :noterminal

php -r "exit(version_compare(PHP_VERSION, '8.1.0', '>=') ? 0 : 1);"
if errorlevel 1 (echo  PHP 8.1 or newer is needed. In Laragon: Menu ^> PHP ^> Version. & goto :fail)
php -m | findstr /i /x "pdo_mysql" >nul || (echo  Turn on the pdo_mysql extension: Menu ^> PHP ^> Extensions ^> pdo_mysql. & goto :fail)
php -m | findstr /i /x "fileinfo" >nul || (echo  Turn on the fileinfo extension: Menu ^> PHP ^> Extensions ^> fileinfo. & goto :fail)
php -m | findstr /i /x "mbstring" >nul || (echo  Turn on the mbstring extension: Menu ^> PHP ^> Extensions ^> mbstring. & goto :fail)

set "MYSQLAUTH=-uroot"
if defined COOP_MYSQL_ROOT_PASSWORD set "MYSQLAUTH=-uroot -p%COOP_MYSQL_ROOT_PASSWORD%"
mysql %MYSQLAUTH% -e "SELECT 1" >nul 2>&1 || (echo  Cannot connect to MySQL as root. Press "Start All" in Laragon, or set COOP_MYSQL_ROOT_PASSWORD. & goto :fail)

echo.
echo  This creates the database "coop_db" (an existing coop_db is replaced) and loads the demo data.
choice /c YN /m " Continue"
if errorlevel 2 goto :eof

echo  [1/4] Creating tables, views, triggers and the app account...
mysql %MYSQLAUTH% < database\schema.sql || goto :fail
mysql %MYSQLAUTH% -e "CREATE USER IF NOT EXISTS 'coop_app'@'127.0.0.1' IDENTIFIED BY 'ChangeThisStrongPassword!'; GRANT SELECT, INSERT, UPDATE, DELETE ON coop_db.* TO 'coop_app'@'127.0.0.1'; FLUSH PRIVILEGES;" || goto :fail

echo  [2/4] Writing .env ...
if not exist .env copy /y .env.example .env >nul

echo  [3/4] Preparing storage folders...
for %%d in (storage\uploads\cv storage\uploads\reports storage\uploads\avatars storage\logs storage\backups) do if not exist "%%d" mkdir "%%d"

echo  [4/4] Loading demo data...
php database\seed.php || goto :fail

echo.
echo  Done. Open  http://coop.test   (Laragon: right-click ^> Apache ^> reload if the name is new)
echo  Every demo account uses the password  Coop2026!   e.g. admin@ecoop.test, student@ecoop.test
echo  Optional: cron\register-tasks.bat  creates the scheduled jobs in Windows Task Scheduler.
echo.
goto :eof

:noterminal
echo  php or mysql was not found. Open this from the Laragon Terminal (Laragon window ^> Terminal),
echo  so Laragon's PHP and MySQL are on the PATH, then run install-laragon.bat again.
:fail
echo.
echo  Setup stopped. Fix the message above and run install-laragon.bat again.
exit /b 1
