<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AppUpdateController extends Controller
{
    /**
     * Check for new mobile app updates (OTA / In-House Forced Updater)
     */
    public function checkVersion(Request $request)
    {
        $currentAppVersion = $request->input('current_version', '1.0.0');
        $currentVersionCode = (int)$request->input('version_code', 1);

        // Fetch configured latest version or default to config/env
        $latestVersion = env('MOBILE_LATEST_VERSION', '1.1.0');
        $latestVersionCode = (int)env('MOBILE_LATEST_VERSION_CODE', 2);
        $minSupportedVersion = env('MOBILE_MIN_VERSION', '1.0.0');
        $forceUpdate = env('MOBILE_FORCE_UPDATE', true);

        // Release notes in Arabic
        $releaseNotes = [
            'إضافة نظام القوائم الذكية Action Sheet لجميع الشاشات لتسهيل الاستخدام',
            'إمكانية إلغاء الفواتير وتعديل العملاء فورياً مع عكس المخزن والحسابات بأمان',
            'تحسينات كبيرة في سرعة الكاشير وإدارة السلة وتعديل الأوزان',
            'إصلاحات وتحديثات أمنية وتوافقية شاملة'
        ];

        $hasUpdate = version_compare($latestVersion, $currentAppVersion, '>');
        $isForced = $forceUpdate && $hasUpdate;

        // Path of the APK file
        $apkPath = base_path('../sroor-coffee-erp-v1.0.apk');
        $fileSizeMb = File::exists($apkPath) ? round(File::size($apkPath) / 1024 / 1024, 1) : 285.0;

        $host = $request->getSchemeAndHttpHost();
        $downloadUrl = "{$host}/api/v1/app/download-apk";

        return response()->json([
            'success'               => true,
            'has_update'            => $hasUpdate,
            'force_update'          => $isForced,
            'current_app_version'   => $currentAppVersion,
            'latest_version'        => $latestVersion,
            'latest_version_code'   => $latestVersionCode,
            'min_supported_version' => $minSupportedVersion,
            'download_url'          => $downloadUrl,
            'file_size_mb'          => $fileSizeMb,
            'release_date'          => now()->toDateString(),
            'release_notes'         => $releaseNotes,
            'title'                 => 'تحديث إلزامي جديد متاح 🚀',
            'message'               => "يتوفر إصدار جديد ({$latestVersion}) من تطبيق سرور كوفي ERP. يرجى التحديث للمتابعة والتمتع بأحدث الميزات والأمان.",
        ]);
    }

    /**
     * Download the latest APK file directly
     */
    public function downloadApk()
    {
        $possiblePaths = [
            base_path('../sroor-coffee-erp-v1.0.apk'),
            base_path('../../sroor-coffee-erp-v1.0.apk'),
            public_path('downloads/sroor-coffee-erp-latest.apk'),
            'I:/projects/erp-2026/sroor-coffee-erp-v1.0.apk',
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return response()->download($path, 'sroor-coffee-erp-latest.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'ملف الـ APK غير موجود حالياً على السيرفر',
        ], 404);
    }
}
