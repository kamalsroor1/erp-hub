[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

# ==============================================================================
# 🚀 سكربت تهيئة وتحديث نطاقات مخزني ERP في ملف Windows Hosts وخادم Laragon
# ==============================================================================

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$logsDir = Join-Path $scriptDir "..\storage\logs"
if (-not (Test-Path $logsDir)) {
    New-Item -ItemType Directory -Path $logsDir -Force | Out-Null
}

$logFile = Join-Path $logsDir "setup-hosts.log"
$errFile = Join-Path $logsDir "setup-hosts-error.log"

function Log-Info([string]$msg) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "[$timestamp] [INFO] $msg" | Out-File -FilePath $logFile -Append -Encoding utf8
}

function Log-Error([string]$msg, $exception = $null) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $errText = "[$timestamp] [ERROR] $msg"
    if ($exception) {
        $errText += " | Exception: $($exception.ToString())"
    }
    $errText | Out-File -FilePath $errFile -Append -Encoding utf8
    $errText | Out-File -FilePath $logFile -Append -Encoding utf8
    Write-Host "❌ $msg" -ForegroundColor Red
}

Log-Info "=== Starting setup-hosts execution ==="

$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"

# التحقق من صلاحيات المسؤول
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Log-Info "Not running as Administrator. Elevating permissions..."
    Write-Host "⚠️ جاري طلب صلاحيات المسؤول (Administrator)..." -ForegroundColor Yellow
    try {
        $process = Start-Process powershell.exe -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs -PassThru -Wait
        exit $process.ExitCode
    } catch {
        Log-Error "فشل في طلب صلاحيات المسؤول أو تم رفض الإذن من المستخدم." $_.Exception
        Write-Host "📄 سجل الخطأ: $errFile" -ForegroundColor Yellow
        Write-Host "`r`nاضغط أي زر للخروج..."
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
        exit 1
    }
}

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " 🏢 مخزني ERP - سكربت تهيئة الدومينات المحلية (*.test) " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

try {
    # 1. التحقق من وجود ملف hosts
    if (-not (Test-Path $hostsPath)) {
        throw "لم يتم العثور على ملف hosts في المسار: $hostsPath"
    }

    # 2. إنشاء نسخة احتياطية من ملف hosts
    $hostsBackup = "$hostsPath.bak"
    Copy-Item -Path $hostsPath -Destination $hostsBackup -Force
    Log-Info "Created hosts backup at: $hostsBackup"

    # 3. قراءة محتوى hosts الحالي
    $hostsContent = Get-Content $hostsPath -Raw
    if ($null -eq $hostsContent) { $hostsContent = "" }

    $domainsToAdd = @(
        "makhzani.test",
        "super.makhzani.test",
        "admin.makhzani.test",
        "sroor.makhzani.test",
        "cairo.makhzani.test",
        "alex.makhzani.test",
        "riyadh.makhzani.test",
        "demo.makhzani.test",
        "pos.makhzani.test"
    )

    $addedCount = 0
    $blockHeader = "`r`n# === MAKHZANI ERP MULTI-TENANT LOCAL DOMAINS ==="

    if ($hostsContent -notlike "*MAKHZANI ERP MULTI-TENANT LOCAL DOMAINS*") {
        Add-Content -Path $hostsPath -Value $blockHeader -Encoding utf8
        Log-Info "Added MAKHZANI ERP header to hosts file."
    }

    foreach ($domain in $domainsToAdd) {
        $entry = "127.0.0.1    $domain"
        if ($hostsContent -notlike "*$domain*") {
            Add-Content -Path $hostsPath -Value $entry -Encoding utf8
            Write-Host "  [+] تم إضافة النطاق: $domain" -ForegroundColor Green
            Log-Info "Added domain: $domain"
            $addedCount++
        } else {
            Write-Host "  [=] النطاق مسجل مسبقاً: $domain" -ForegroundColor DarkGray
        }
    }

    # 4. تهيئة خادم Laragon الافتراضي على بورت 80 (إن وجد)
    $laragonVhostsDir = "C:\laragon\etc\apache2\sites-enabled"
    if (Test-Path $laragonVhostsDir) {
        $laragonConf = Join-Path $laragonVhostsDir "auto.makhzani.test.conf"
        $backendPublic = Resolve-Path (Join-Path $scriptDir "..\public") | Select-Object -ExpandProperty Path
        $backendPublic = $backendPublic.Replace('\', '/')

        $vhostContent = @"
<VirtualHost *:80> 
    DocumentRoot "$backendPublic"
    ServerName makhzani.test
    ServerAlias *.makhzani.test
    <Directory "$backendPublic">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@
        Set-Content -Path $laragonConf -Value $vhostContent -Encoding utf8
        Write-Host "`r`n  [✓] تم تهيئة إعدادات Laragon Apache على بورت 80 تلقائياً!" -ForegroundColor Green
        Log-Info "Configured Laragon VirtualHost at $laragonConf pointing to $backendPublic"
    }

    # 5. تنشيط ذاكرة DNS (Flush DNS)
    Write-Host "`r`n🔄 جاري تنشيط ذاكرة DNS (Flush DNS)..." -ForegroundColor Yellow
    ipconfig /flushdns | Out-Null
    Log-Info "Executed ipconfig /flushdns successfully."

    Write-Host "`r`n✅ اكتملت العملية بنجاح! تم تسجيل كافة النطاقات المحلية." -ForegroundColor Green
    Write-Host "📄 ملف السجل: $logFile" -ForegroundColor DarkGray
    Write-Host "`r`n💡 الروابط المتاحة الآن على جهازك:" -ForegroundColor Cyan
    Write-Host "   - http://makhzani.test (بدون بورت عبر Laragon) أو http://makhzani.test:8000" -ForegroundColor White
    Write-Host "   - http://makhzani.test/admin/super (لوحة السوبر أدمن)" -ForegroundColor White
    Write-Host "   - http://makhzani.test/pos (كاشير نقاط البيع)" -ForegroundColor White

    Log-Info "Setup hosts completed successfully with $addedCount new domains added."

} catch {
    Log-Error "حدث خطأ أثناء معالجة ملف hosts: $($_.Exception.Message)" $_.Exception
    Write-Host "`r`n❌ حدث خطأ أثناء تنفيذ السكربت. يرجى مراجعة سجل الأخطاء:" -ForegroundColor Red
    Write-Host "📄 سجل الأخطاء: $errFile" -ForegroundColor Yellow
}

Write-Host "`r`nاضغط أي زر للخروج..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")