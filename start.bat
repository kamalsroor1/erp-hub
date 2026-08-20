@echo off
chcp 65001 >nul
title Sroor Coffee ERP Hub

echo ================================================================
echo    Sroor Coffee ERP - Full System Server (Web + Mobile)
echo ================================================================
echo.
echo Links:
echo   - Desktop Admin: http://localhost:8000/admin/super
echo   - Desktop Store: http://sroor.localhost:8000/login
echo   - Mobile Wi-Fi:  http://10.250.200.55:8000/login
echo.
echo Launching browser...
start "" "http://sroor.localhost:8000/login"
echo.
echo Starting servers for all LAN devices (Host 0.0.0.0:8000)...
echo Press Ctrl+C to stop servers at any time.
echo ================================================================
echo.

npm run dev
