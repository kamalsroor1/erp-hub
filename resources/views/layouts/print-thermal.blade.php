<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال - {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&display=swap" rel="stylesheet">
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
            * {
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        body {
            font-family: 'Cairo', sans-serif;
            font-size: 12px;
            font-weight: 700;
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
        .border-t { border-top: 1.5px dashed #000; }
        .border-b { border-bottom: 1.5px dashed #000; }
        .py-1 { padding-top: 3px; padding-bottom: 3px; }
        .py-2 { padding-top: 6px; padding-bottom: 6px; }
        .my-2 { margin-top: 6px; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 1px; font-size: 11px; font-weight: 700; }
        th { font-weight: 900; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 18px; font-family: 'Cairo'; background: #10b981; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 900;">🖨️ طباعة الإيصال</button>
        <button onclick="window.history.back()" style="padding: 8px 14px; font-family: 'Cairo'; background: #475569; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 700;">رجوع</button>
    </div>

    @php
        $companyName = \App\Models\Setting::get('company_name', 'سرور كوفي');
        $companySubtitle = \App\Models\Setting::get('company_subtitle', 'لتوزيع خامات مطاحن البن');
        $showSubtitle = \App\Models\Setting::getBool('show_print_subtitle', true);
    @endphp

    <!-- Header -->
    <div class="text-center">
        <img src="{{ asset('logo.png') }}" alt="{{ $companyName }}" style="max-height: 52px; max-width: 50mm; margin: 0 auto 4px auto; display: block; object-fit: contain;">
        <h2 class="font-black" style="font-size: 17px; margin: 0; color: #000;">{{ $companyName }}</h2>
        @if($showSubtitle && !empty($companySubtitle))
            <p style="margin: 2px 0; font-size: 11px; font-weight: 800;">{{ $companySubtitle }}</p>
        @endif
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
                    <strong style="font-weight: 900;">{{ $item->item->name }}</strong>
                </td>
                <td class="text-center" style="font-weight: 800;">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-center" style="font-weight: 800;">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-left font-black">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="border-t py-1 my-2">
        <div style="display: flex; justify-content: space-between;">
            <span>المجموع:</span>
            <span style="font-weight: 800;">{{ number_format($invoice->subtotal, 2) }} ج.م</span>
        </div>
        @if(bccomp($invoice->discount_amount, '0.000', 3) > 0)
        <div style="display: flex; justify-content: space-between;">
            <span>الخصم:</span>
            <span>-{{ number_format($invoice->discount_amount, 2) }} ج.م</span>
        </div>
        @endif
        <div style="display: flex; justify-content: space-between; font-size: 14px;" class="font-black">
            <span>الصافي:</span>
            <span>{{ number_format($invoice->net_total, 2) }} ج.م</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>المدفوع:</span>
            <span>{{ number_format($invoice->paid_amount, 2) }} ج.م</span>
        </div>
        <div style="display: flex; justify-content: space-between;" class="font-bold">
            <span>المتبقي:</span>
            <span>{{ number_format($invoice->remaining_amount, 2) }} ج.م</span>
        </div>
    </div>

    <div class="text-center py-2 border-t" style="margin-top: 8px;">
        <p style="margin: 0; font-size: 11px; font-weight: 800;">شكراً لتعاملكم معنا</p>
    </div>

</body>
</html>
