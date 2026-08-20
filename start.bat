@echo off
chcp 65001 > nul
title ERP HUB - نظام سرور كوفي المتكامل

echo ================================================================
echo    🚀 تشغيل نظام سرور كوفي ERP المتكامل (Hub + Web + Mobile)
echo ================================================================
echo.
echo [1/3] فحص السيرفر والروابط المتاحة...
echo   - لوحة تحكم السوبر أدمن: http://localhost:8000/admin/super
echo   - متجر سرور كوفي (المستأجر): http://sroor.localhost:8000/login
echo   - خادم فرونت إند الباك: http://localhost:5173
echo   - خادم واجهة الموبايل: http://localhost:5174
echo.
echo [2/3] فتح المتصفح تلقائياً على النظام...
start "" "http://localhost:8000/admin/super"
start "" "http://sroor.localhost:8000/login"
echo.
echo [3/3] جاري تشغيل كافة السيرفرات في نافذة واحدة...
echo اضغط Ctrl+C لإيقاف كافة السيرفرات في أي وقت.
echo ================================================================
echo.

npm run dev
