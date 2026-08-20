@echo off
setlocal enabledelayedexpansion
echo ====================================================
echo   Building NativePHP Android APK - Sroor Coffee ERP
echo ====================================================

:: Set Java 17 Home & Android SDK
set "JAVA_HOME=C:\Program Files\Microsoft\jdk-17.0.20.8-hotspot"
set "ANDROID_HOME=C:\Android\Sdk"
set "ANDROID_SDK_ROOT=C:\Android\Sdk"
set "NATIVEPHP_ANDROID_SDK_LOCATION=C:\Android\Sdk"
set "PATH=%JAVA_HOME%\bin;C:\Program Files\7-Zip;C:\Android\Sdk\cmdline-tools\latest\bin;C:\Android\Sdk\platform-tools;%PATH%"

echo sdk.dir=C:\\Android\\Sdk> "nativephp\android\local.properties"

echo [1/2] Checking Java and Android environment...
java -version

echo.
echo [2/2] Running NativePHP Android packaging...
php artisan native:package android %*

echo.
if exist "nativephp\android\app\build\outputs\apk\release\app-release.apk" (
    if not exist "dist" mkdir dist
    copy /y "nativephp\android\app\build\outputs\apk\release\app-release.apk" "dist\sroor-coffee-erp-v1.0.apk" >nul
    copy /y "nativephp\android\app\build\outputs\apk\release\app-release.apk" "sroor-coffee-erp-v1.0.apk" >nul
    echo ====================================================
    echo   BUILD SUCCESS! APK ready at:
    echo   - I:\projects\erp-2026\mobile\sroor-coffee-erp-v1.0.apk
    echo   - I:\projects\erp-2026\mobile\dist\sroor-coffee-erp-v1.0.apk
    echo ====================================================
)

pause
