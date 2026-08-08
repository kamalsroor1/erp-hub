<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال حراري - {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            body {
                width: 78mm;
                margin: 0 auto;
                padding: 3mm;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: 'Cairo', sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            width: 78mm;
            margin: 0 auto;
            padding: 4mm;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-black { font-weight: 900; }
        .border-t { border-top: 1px dashed #000; }
        .border-b { border-bottom: 1px dashed #000; }
        .py-1 { padding-top: 2px; padding-bottom: 2px; }
        .py-2 { padding-top: 5px; padding-bottom: 5px; }
        .my-2 { margin-top: 5px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 1px; font-size: 10px; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 16px; font-family: 'Cairo'; background: #10b981; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">🖨️ طباعة الإيصال (80mm)</button>
        <button onclick="window.history.back()" style="padding: 6px 12px; font-family: 'Cairo'; background: #64748b; color: #fff; border: none; border-radius: 4px; cursor: pointer;">رجوع</button>
    </div>

    <!-- Header -->
    <div class="text-center">
        <h2 class="font-black" style="font-size: 16px; margin: 0;">مؤسسة سرور التجارية</h2>
        <p style="margin: 2px 0;">لتجارة التجزئة والجملة</p>
        <p style="margin: 2px 0;">هاتف: 01000000000 | الرقم الضريبي: 123456789</p>
    </div>

    <div class="border-t border-b py-1 my-2">
        <div style="display: flex; justify-content: space-between;">
            <span><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span><strong>التاريخ:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</span>
            <span><strong>الوقت:</strong> {{ $invoice->created_at->format('H:i') }}</span>
        </div>
        <div><strong>العميل:</strong> {{ $invoice->customer->name }}</div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr class="border-b">
                <th class="text-right">الصنف</th>
                <th class="text-center">الكمية</th>
                <th class="text-center">السعر</th>
                <th class="text-left">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td class="text-right">
                    <strong>{{ $item->item->name }}</strong>
                </td>
                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-center">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-left font-bold">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="border-t py-1 my-2">
        <div style="display: flex; justify-content: space-between;">
            <span>المجموع:</span>
            <span>{{ number_format($invoice->subtotal, 2) }} ج.م</span>
        </div>
        @if(bccomp($invoice->discount_amount, '0.000', 3) > 0)
        <div style="display: flex; justify-content: space-between;">
            <span>الخصم:</span>
            <span>-{{ number_format($invoice->discount_amount, 2) }} ج.م</span>
        </div>
        @endif
        <div style="display: flex; justify-content: space-between; font-size: 13px;" class="font-black border-t py-1">
            <span>الصافي المطلوب:</span>
            <span>{{ number_format($invoice->net_total, 2) }} ج.م</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>المدفوع:</span>
            <span>{{ number_format($invoice->paid_amount, 2) }} ج.م</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>المتبقي:</span>
            <span>{{ number_format($invoice->remaining_amount, 2) }} ج.م</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center border-t py-2">
        <p style="margin: 0;" class="font-bold">شكراً لتعاملكم معنا!</p>
        <p style="margin: 2px 0; font-size: 9px; color: #555;">البضاعة المباعة لا ترد ولا تستبدل إلا بالفاتورة خلال 14 يوماً</p>
    </div>
</body>
</html>
