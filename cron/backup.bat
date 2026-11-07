@echo off
rem COOP daily backup for Windows/Laragon (keeps 30 days). Scheduled by cron\register-tasks.bat.
cd /d "%~dp0\.."
if not exist storage\backups mkdir storage\backups
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd"') do set "TODAY=%%i"
set "MYSQLAUTH=-uroot"
if defined COOP_MYSQL_ROOT_PASSWORD set "MYSQLAUTH=-uroot -p%COOP_MYSQL_ROOT_PASSWORD%"
mysqldump %MYSQLAUTH% --single-transaction --routines --triggers --events coop_db > "storage\backups\coop_db-%TODAY%.sql" || exit /b 1
forfiles /p storage\backups /m coop_db-*.sql /d -30 /c "cmd /c del @path" >nul 2>&1
echo Backup written to storage\backups\coop_db-%TODAY%.sql
