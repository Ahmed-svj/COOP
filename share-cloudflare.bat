@echo off
setlocal EnableExtensions
rem ===================================================================
rem  COOP - share the running system with a public Cloudflare link
rem  (https://....trycloudflare.com, no Cloudflare account needed).
rem  Double-click, or run from the Laragon Terminal / VS Code terminal.
rem  Keep this window open while people use the link; Ctrl+C stops sharing.
rem ===================================================================
cd /d "%~dp0"
echo.
echo  COOP - share with a Cloudflare link
echo  -----------------------------------
set "CF="
if exist "%~dp0cloudflared.exe" set "CF=%~dp0cloudflared.exe"
if not defined CF for /f "delims=" %%p in ('where cloudflared 2^>nul') do if not defined CF set "CF=%%p"
if not defined CF (
  echo  cloudflared is not installed yet. Installing it with winget ...
  winget install --id Cloudflare.cloudflared -e --accept-source-agreements --accept-package-agreements && (
    echo.
    echo  Installed. Close this window and run share-cloudflare.bat again so Windows finds it.
    goto :end
  )
  goto :manual
)

rem Is COOP already running on port 8080 (VS Code F5 / task, or an earlier run)?
curl -s -o NUL http://127.0.0.1:8080/ && goto :tunnel
where php >nul 2>&1 || (echo  php was not found. Run this from the Laragon Terminal, or start COOP in VS Code first ^(F5^). & goto :end)
if not exist .env (echo  COOP is not installed yet. Run install-laragon.bat ^(or: php database\install.php^) first. & goto :end)
echo  Starting COOP on http://127.0.0.1:8080 in a separate window ...
start "COOP website - close this window to stop" /min php -S 127.0.0.1:8080 -t public public/index.php
timeout /t 2 /nobreak >nul
curl -s -o NUL http://127.0.0.1:8080/ || (echo  COOP did not start. Is MySQL running ^(Laragon: Start All^)? & goto :end)

:tunnel
echo.
echo  Your public link appears below in a box:   https://....trycloudflare.com
echo  Anyone with the link can use COOP while this window stays open. Press Ctrl+C to stop sharing.
echo  Note: the demo accounts use the password Coop2026! - change them before sharing widely.
echo.
"%CF%" tunnel --no-autoupdate --url http://127.0.0.1:8080
goto :end

:manual
echo.
echo  Could not install automatically. Download "cloudflared-windows-amd64.exe" from
echo     https://github.com/cloudflare/cloudflared/releases/latest
echo  rename it to cloudflared.exe, put it in this folder, and run share-cloudflare.bat again.
:end
echo.
pause
