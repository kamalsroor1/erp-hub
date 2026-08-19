@echo off
setlocal
set "SCRIPT_DIR=%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_DIR%setup-hosts.ps1"
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Script failed with exit code %ERRORLEVEL%
    pause
)