@echo off
setlocal
rem Creates COOP's scheduled jobs in Windows Task Scheduler (the Windows version of cron).
rem Run once from the Laragon Terminal, inside the coop folder:   cron\register-tasks.bat
cd /d "%~dp0\.."
set "ROOT=%CD%"
set "PHP="
for /f "delims=" %%p in ('where php 2^>nul') do if not defined PHP set "PHP=%%p"
if not defined PHP (echo php not found - run this from the Laragon Terminal. & exit /b 1)
for /f "delims=" %%p in ('where mysqldump 2^>nul') do if not defined DUMPDIR set "DUMPDIR=%%~dpp"
echo Using PHP: %PHP%
schtasks /create /f /tn "COOP\Expire daily codes"     /sc daily  /st 00:00 /tr "\"%PHP%\" \"%ROOT%\cron\expire_daily_codes.php\""   >nul && echo  + Expire daily codes      (every day 00:00)
schtasks /create /f /tn "COOP\Complete placements"    /sc daily  /st 00:05 /tr "\"%PHP%\" \"%ROOT%\cron\complete_placements.php\""  >nul && echo  + Complete placements     (every day 00:05)
schtasks /create /f /tn "COOP\Attendance reminders"   /sc weekly /d SUN,MON,TUE,WED,THU /st 06:30 /tr "\"%PHP%\" \"%ROOT%\cron\attendance_reminders.php\"" >nul && echo  + Attendance reminders    (Sun-Thu 06:30)
schtasks /create /f /tn "COOP\Weekly summary"         /sc weekly /d THU /st 15:00 /tr "\"%PHP%\" \"%ROOT%\cron\weekly_summary.php\""  >nul && echo  + Weekly summary          (Thursday 15:00)
schtasks /create /f /tn "COOP\Backup"                 /sc daily  /st 02:00 /tr "cmd /c set PATH=%DUMPDIR%;%%PATH%% ^&^& \"%ROOT%\cron\backup.bat\"" >nul && echo  + Backup                  (every day 02:00)
echo Done. See them in Task Scheduler under the "COOP" folder.  Remove them with:  schtasks /delete /tn "COOP\Backup" /f  (etc.)
