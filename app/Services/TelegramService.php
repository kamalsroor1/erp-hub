<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\CashShift;
use App\Models\Store;
use App\Models\Item;
use App\Models\StoreStock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Get the active bot token from DB settings or config.
     */
    public function getBotToken(): ?string
    {
        return Setting::get('telegram_bot_token') 
            ?: config('services.telegram.bot_token');
    }

    /**
     * Get the default chat ID from DB settings or config.
     */
    public function getDefaultChatId(): ?string
    {
        return Setting::get('telegram_chat_id') 
            ?: config('services.telegram.chat_id');
    }

    /**
     * Check if Telegram notifications are enabled.
     */
    public function isEnabled(): bool
    {
        $settingVal = Setting::get('telegram_notifications_enabled');
        if ($settingVal !== null) {
            return Setting::getBool('telegram_notifications_enabled', true);
        }
        return (bool)config('services.telegram.enabled', true);
    }

    /**
     * Send a raw HTML-formatted message to Telegram.
     */
    public function sendMessage(string $htmlText, ?string $chatId = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'خدمة إشعارات تيليجرام معطلة حالياً.'];
        }

        $token = $this->getBotToken();
        $targetChatId = $chatId ?: $this->getDefaultChatId();

        if (empty($token) || empty($targetChatId)) {
            return ['success' => false, 'message' => 'لم يتم ضبط Bot Token أو Chat ID في الإعدادات.'];
        }

        $chatIds = array_filter(array_map('trim', explode(',', $targetChatId)));
        if (empty($chatIds)) {
            return ['success' => false, 'message' => 'معرف المحادثة Chat ID غير صالح.'];
        }

        $successCount = 0;
        $errors = [];

        foreach ($chatIds as $cid) {
            try {
                $url = "https://api.telegram.org/bot{$token}/sendMessage";
                $response = Http::timeout(10)->post($url, [
                    'chat_id'                  => $cid,
                    'text'                     => $htmlText,
                    'parse_mode'               => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $desc = $response->json('description') ?? $response->status();
                    $errors[] = "Chat {$cid}: {$desc}";
                    Log::error("Telegram notification error for {$cid}: " . $response->body());
                }
            } catch (\Throwable $e) {
                $errors[] = "Chat {$cid}: " . $e->getMessage();
                Log::error("Telegram exception for {$cid}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $msg = ($successCount === 1) 
                ? 'تم إرسال الإشعار بنجاح عبر تيليجرام!' 
                : "تم إرسال الإشعار بنجاح إلى {$successCount} محادثة/جروب!";
            return ['success' => true, 'message' => $msg];
        }

        return [
            'success' => false,
            'message' => 'فشل الإرسال: ' . implode(' | ', $errors)
        ];
    }

    /**
     * Send a test notification to verify bot connection.
     */
    public function sendTestNotification(?string $chatId = null): array
    {
        $companyName = Setting::get('company_name', 'نظام إدارة الفواتير والمخزون');
        $now = now()->format('Y-m-d h:i A');

        $message = "🤖 <b>اختبار ربط إشعارات تيليجرام</b>\n\n";
        $message .= "🏢 <b>المنشأة:</b> {$companyName}\n";
        $message .= "⏰ <b>التاريخ والوقت:</b> {$now}\n";
        $message .= "✅ تم الربط وتفعيل خدمة الإشعارات التلقائية الذكية بنجاح!\n\n";
        $message .= "📡 <i>ستصلك هنا تقارير اليومية، تنبيهات النواقص، وإنذارات الشفتات تلقائياً.</i>";

        return $this->sendMessage($message, $chatId);
    }

    /**
     * Send daily EOD business summary report (Sales, Cash, Debts, Expenses, Profits).
     */
    public function sendDailySummaryNotification(?string $date = null): array
    {
        $targetDate = $date ?: now()->toDateString();
        $companyName = Setting::get('company_name', 'المركز الرئيسي');

        // Confirmed Invoices for today
        $invoices = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', $targetDate)
            ->get();

        $totalSales = '0.000';
        $cashSales  = '0.000';
        $creditSales = '0.000';
        $invoicesCount = $invoices->count();

        foreach ($invoices as $inv) {
            $totalSales = bcadd($totalSales, (string)$inv->net_total, 3);
            $cashSales  = bcadd($cashSales, (string)$inv->paid_amount, 3);
            $creditSales = bcadd($creditSales, (string)$inv->remaining_amount, 3);
        }

        // Expenses for today
        $expenses = Expense::whereDate('expense_date', $targetDate)->get();
        $totalExpenses = '0.000';
        foreach ($expenses as $exp) {
            $totalExpenses = bcadd($totalExpenses, (string)$exp->amount, 3);
        }

        // Open shifts count
        $openShiftsCount = CashShift::where('status', 'open')->count();

        // Format Message
        $msg  = "📊 <b>تقرير ملخص اليومية الإداري (EOD)</b>\n";
        $msg .= "🏢 <b>المنشأة:</b> {$companyName}\n";
        $msg .= "📅 <b>تاريخ اليوم:</b> {$targetDate}\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        $msg .= "🧾 <b>عدد الفواتير الصادرة:</b> {$invoicesCount} فاتورة\n";
        $msg .= "💰 <b>إجمالي مبيعات اليوم:</b> <code>" . number_format((float)$totalSales, 2) . " ج.م</code>\n";
        $msg .= "💵 <b>النقدية المحصلة (كاش):</b> <code>" . number_format((float)$cashSales, 2) . " ج.م</code>\n";
        $msg .= "⏳ <b>المبيعات الآجلة (ديون):</b> <code>" . number_format((float)$creditSales, 2) . " ج.م</code>\n";
        $msg .= "💸 <b>إجمالي المصروفات:</b> <code>" . number_format((float)$totalExpenses, 2) . " ج.م</code>\n";
        $netCash = bcsub($cashSales, $totalExpenses, 2);
        $msg .= "🪙 <b>صافي النقدية المتوفرة:</b> <code>" . number_format((float)$netCash, 2) . " ج.م</code>\n\n";

        // Store breakdown
        $stores = Store::active()->get();
        if ($stores->count() > 1) {
            $msg .= "🏬 <b>مبيعات الفروع وعربات التوزيع:</b>\n";
            foreach ($stores as $st) {
                $stSales = Invoice::where('status', 'confirmed')
                    ->where('store_id', $st->id)
                    ->whereDate('invoice_date', $targetDate)
                    ->sum('net_total') ?: '0.000';
                
                $icon = ($st->type === 'wholesale_van') ? '🚚' : '🏢';
                $msg .= "  {$icon} {$st->name}: <code>" . number_format((float)$stSales, 2) . " ج.م</code>\n";
            }
            $msg .= "\n";
        }

        if ($openShiftsCount > 0) {
            $msg .= "⚠️ <b>تنبيه:</b> يوجد <b>{$openShiftsCount} وردية/درج كاشير</b> ما زالت مفتوحة لم تُقفل بعد.\n\n";
        } else {
            $msg .= "✅ <b>كافة ورديات وشفتات اليوم تم إغلاقها بنجاح.</b>\n\n";
        }

        $msg .= "⏰ <i>تم الإنشاء تلقائياً: " . now()->format('h:i A') . "</i>";

        return $this->sendMessage($msg);
    }

    /**
     * Send low stock alert notification across all branches.
     */
    public function sendLowStockNotification(bool $previewSample = false): array
    {
        $lowStocks = StoreStock::with(['item', 'store'])
            ->whereColumn('quantity', '<=', 'min_stock')
            ->whereHas('item', fn($q) => $q->where('is_active', true))
            ->get();

        if ($lowStocks->isEmpty()) {
            if ($previewSample) {
                // Send a formatted sample to demonstrate the layout
                $msg  = "⚠️ <b>[معاينة تجريبية] إنذار نواقص وقرب نفاد المخزون</b>\n";
                $msg .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d h:i A') . "\n";
                $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
                $msg .= "الأصناف التالية وصلت إلى أو أقل من حد الأمان:\n\n";
                $msg .= "<b>1. بن برازيلي كولومبي وسط</b>\n";
                $msg .= "   🏬 الفرع: المخزن الرئيسي\n";
                $msg .= "   📦 الرصيد الحالي: <code>4.50 كجم</code> (حد الإنذار: 10.0)\n\n";
                $msg .= "<b>2. أكياس تعبئة بن 1 كجم صمام</b>\n";
                $msg .= "   🏬 الفرع: فرع المحل\n";
                $msg .= "   📦 الرصيد الحالي: <code>15.00 قطعة</code> (حد الإنذار: 50.0)\n\n";
                $msg .= "🚨 <i>يرجى التنسيق لإصدار فواتير شراء أو شحن تحويلات للمخازن.</i>";
                return $this->sendMessage($msg);
            }
            return ['success' => true, 'message' => 'لا توجد أي أصناف ناقصة حالياً.'];
        }

        $msg  = "⚠️ <b>إنذار نواقص وقرب نفاد المخزون</b>\n";
        $msg .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d h:i A') . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "الأصناف التالية وصلت إلى أو أقل من حد الأمان:\n\n";

        $count = 1;
        foreach ($lowStocks->take(15) as $stk) {
            $storeName = $stk->store?->name ?? 'الفرع';
            $itemName  = $stk->item?->name ?? 'صنف';
            $unit      = $stk->item?->unit ?? '';
            $qty       = number_format((float)$stk->quantity, 2);
            $min       = number_format((float)$stk->min_stock, 1);

            $msg .= "<b>{$count}. {$itemName}</b>\n";
            $msg .= "   🏬 الفرع: {$storeName}\n";
            $msg .= "   📦 الرصيد الحالي: <code>{$qty} {$unit}</code> (حد الإنذار: {$min})\n\n";
            $count++;
        }

        if ($lowStocks->count() > 15) {
            $remaining = $lowStocks->count() - 15;
            $msg .= "<i>+ يوجد {$remaining} صنف آخر ناقص... يرجى مراجعة شاشة النواقص في النظام.</i>\n\n";
        }

        $msg .= "🚨 <i>يرجى التنسيق لإصدار فواتير شراء أو شحن تحويلات للمخازن.</i>";

        return $this->sendMessage($msg);
    }

    /**
     * Send alert for cash shifts open for more than 24 hours.
     */
    public function sendOverdueShiftNotification(bool $previewSample = false): array
    {
        $threshold = now()->subHours(24);

        $overdueShifts = CashShift::with(['user', 'store'])
            ->where('status', 'open')
            ->where('opened_at', '<=', $threshold)
            ->get();

        if ($overdueShifts->isEmpty()) {
            if ($previewSample) {
                // Send a formatted sample to demonstrate the layout
                $msg  = "🚨 <b>[معاينة تجريبية] تحذير عاجل: شفتات كاشير مفتوحة لأكثر من 24 ساعة!</b>\n";
                $msg .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d h:i A') . "\n";
                $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
                $msg .= "الورديات التالية لم يتم تقفيلها منذ أكثر من يوم:\n\n";
                $msg .= "👤 <b>الكاشير:</b> كاشير الصباح\n";
                $msg .= "🏢 <b>الفرع/الدرج:</b> المخزن الرئيسي\n";
                $msg .= "⏱️ <b>وقت الفتح:</b> " . now()->subHours(26)->format('Y-m-d h:i A') . " (مفتوح منذ 26 ساعة)\n";
                $msg .= "💰 <b>رصيد البداية:</b> 500.00 ج.م\n\n";
                $msg .= "⚠️ <i>يُرجى التواصل مع الكاشير فوراً لتقفيل اليومية ومراجعة عهدة الدرج.</i>";
                return $this->sendMessage($msg);
            }
            return ['success' => true, 'message' => 'لا توجد أي شفتات معلقة لأكثر من 24 ساعة.'];
        }

        $msg  = "🚨 <b>تحذير عاجل: شفتات كاشير مفتوحة لأكثر من 24 ساعة!</b>\n";
        $msg .= "📅 <b>التاريخ:</b> " . now()->format('Y-m-d h:i A') . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "الورديات التالية لم يتم تقفيلها منذ أكثر من يوم:\n\n";

        foreach ($overdueShifts as $shift) {
            $cashierName = $shift->user?->name ?? 'غير محدد';
            $storeName   = $shift->store?->name ?? 'المركز الرئيسي';
            $openTime    = $shift->opened_at ? $shift->opened_at->format('Y-m-d h:i A') : 'غير محدد';
            $hours       = $shift->opened_at ? (int)$shift->opened_at->diffInHours(now()) : 24;

            $msg .= "👤 <b>الكاشير:</b> {$cashierName}\n";
            $msg .= "🏢 <b>الفرع/الدرج:</b> {$storeName}\n";
            $msg .= "⏱️ <b>وقت الفتح:</b> {$openTime} (مفتوح منذ {$hours} ساعة)\n";
            $msg .= "💰 <b>رصيد البداية:</b> " . number_format((float)$shift->opening_cash, 2) . " ج.م\n\n";
        }

        $msg .= "⚠️ <i>يُرجى التواصل مع الكاشير فوراً لتقفيل اليومية ومراجعة عهدة الدرج.</i>";

        return $this->sendMessage($msg);
    }
}
