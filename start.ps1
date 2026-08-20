# ERP HUB All-in-One Launcher
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$Host.UI.RawUI.WindowTitle = "ERP HUB - تشغيل النظام المتكامل"

Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "   🚀 تشغيل نظام سرور كوفي ERP المتكامل (Hub + Web + Mobile)" -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  - لوحة تحكم السوبر أدمن: " -NoNewline -ForegroundColor Yellow
Write-Host "http://localhost:8000/admin/super" -ForegroundColor White
Write-Host "  - متجر سرور كوفي (المستأجر): " -NoNewline -ForegroundColor Yellow
Write-Host "http://sroor.localhost:8000/login" -ForegroundColor White
Write-Host "  - خادم فرونت إند الباك: " -NoNewline -ForegroundColor Yellow
Write-Host "http://localhost:5173" -ForegroundColor White
Write-Host "  - خادم واجهة الموبايل: " -NoNewline -ForegroundColor Yellow
Write-Host "http://localhost:5174" -ForegroundColor White
Write-Host ""
Write-Host "🌐 جاري فتح المتصفح..." -ForegroundColor Cyan

Start-Process "http://localhost:8000/admin/super"
Start-Process "http://sroor.localhost:8000/login"

Write-Host "⚡ جاري تشغيل كافة السيرفرات..." -ForegroundColor Green
Write-Host "اضغط Ctrl+C لإيقاف كافة السيرفرات في أي وقت." -ForegroundColor Gray
Write-Host ""

npm run dev
