# Sroor Coffee ERP - Automated Capacitor Android Build Script
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "  Sroor Coffee ERP - Building Mobile Android Package" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Cyan

$backendDir = Join-Path $PSScriptRoot "backend"

# 1. Compile Vue 3 & Vite assets
Write-Host "[1/3] Compiling Frontend Web Assets (Vite + Tailwind)..." -ForegroundColor Yellow
Set-Location $backendDir
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "Frontend compilation failed!" -ForegroundColor Red
    Set-Location $PSScriptRoot
    exit $LASTEXITCODE
}

# 2. Sync Capacitor Android project
Write-Host "[2/3] Syncing Web Assets and Plugins with Android Project..." -ForegroundColor Yellow
npx cap sync android
if ($LASTEXITCODE -ne 0) {
    Write-Host "Capacitor Android sync failed!" -ForegroundColor Red
    Set-Location $PSScriptRoot
    exit $LASTEXITCODE
}

# 3. Assemble Android APK via Gradle
$androidDir = Join-Path $backendDir "android"
if (Test-Path $androidDir) {
    Write-Host "[3/3] Checking Android Studio and Gradle project..." -ForegroundColor Yellow
    Set-Location $androidDir
    if (Test-Path ".\gradlew.bat") {
        Write-Host "Native Android project is synced. You can assemble APK or open in Android Studio." -ForegroundColor Cyan
    }
}

Set-Location $PSScriptRoot
Write-Host "Done! Capacitor Android Project is fully synchronized and ready." -ForegroundColor Green
