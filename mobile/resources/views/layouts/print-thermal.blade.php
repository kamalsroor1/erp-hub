<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال - {{ $invoice['invoice_number'] ?? $invoice->invoice_number ?? '' }}</title>
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
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

    <div class="no-print" style="margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; align-items: center;">
        <button onclick="window.print()" style="padding: 7px 14px; font-family: 'Cairo'; background: #10b981; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 900; font-size: 12px;">🖨️ طباعة الإيصال</button>
        <button onclick="downloadReceiptAsImage()" id="btn-thermal-img" style="padding: 7px 14px; font-family: 'Cairo'; background: #d97706; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 900; font-size: 12px;">📸 تحميل صورة</button>
        <button onclick="window.history.back()" style="padding: 7px 12px; font-family: 'Cairo'; background: #475569; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 12px;">رجوع</button>
    </div>

    <div id="receipt-container" style="background: #ffffff; padding: 4px;">

    <!-- Header -->
    <div class="text-center">
        <h2 class="font-black" style="font-size: 17px; margin: 0; color: #000;">سرور كوفي</h2>
        <p style="margin: 2px 0; font-size: 11px; font-weight: 800;">لتوريدات خامات مطاحن البن</p>
        <p style="margin: 0; font-size: 10px; color: #333;">الفرع: {{ $invoice['store']['name'] ?? $invoice->store->name ?? 'الفرع الرئيسي' }}</p>
    </div>

    <div class="border-t border-b py-1 my-2">
        <div style="display: flex; justify-content: space-between;">
            <span><strong>رقم الفاتورة:</strong> {{ $invoice['invoice_number'] ?? $invoice->invoice_number ?? '' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span><strong>التاريخ:</strong> {{ substr($invoice['invoice_date'] ?? $invoice->invoice_date ?? now()->toDateString(), 0, 10) }}</span>
            <span><strong>الوقت:</strong> {{ substr($invoice['created_at'] ?? now()->toTimeString(), 11, 5) }}</span>
        </div>
        <div>
            <span><strong>العميل:</strong> {{ $invoice['customer']['name'] ?? $invoice->customer->name ?? 'عميل نقدي' }}</span>
        </div>
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
            @foreach($invoice['items'] ?? $invoice->items ?? [] as $item)
            @php
                $itemName = is_array($item) ? ($item['item']['name'] ?? $item['name'] ?? '') : ($item->item->name ?? $item->name ?? '');
                $itemQty = is_array($item) ? ($item['quantity'] ?? 1) : ($item->quantity ?? 1);
                $itemPrice = is_array($item) ? ($item['unit_price'] ?? 0) : ($item->unit_price ?? 0);
                $itemTotal = is_array($item) ? ($item['total_price'] ?? ($itemQty * $itemPrice)) : ($item->total_price ?? ($itemQty * $itemPrice));
            @endphp
            <tr>
                <td class="text-right">
                    <strong style="font-weight: 900;">{{ $itemName }}</strong>
                </td>
                <td class="text-center" style="font-weight: 800;">{{ number_format((float)$itemQty, 2) }}</td>
                <td class="text-center" style="font-weight: 800;">{{ number_format((float)$itemPrice, 2) }}</td>
                <td class="text-left font-black">{{ number_format((float)$itemTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="border-t py-1 my-2">
        <div style="display: flex; justify-content: space-between;">
            <span>المجموع:</span>
            <span style="font-weight: 800;">{{ number_format((float)($invoice['subtotal'] ?? $invoice->subtotal ?? 0), 2) }} ج.م</span>
        </div>
        @if((float)($invoice['discount_amount'] ?? $invoice->discount_amount ?? 0) > 0)
        <div style="display: flex; justify-content: space-between;">
            <span>الخصم:</span>
            <span>-{{ number_format((float)($invoice['discount_amount'] ?? $invoice->discount_amount ?? 0), 2) }} ج.م</span>
        </div>
        @endif
        <div style="display: flex; justify-content: space-between; font-size: 14px;" class="font-black">
            <span>الصافي المطلوب:</span>
            <span>{{ number_format((float)($invoice['net_total'] ?? $invoice->net_total ?? 0), 2) }} ج.م</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>المدفوع:</span>
            <span>{{ number_format((float)($invoice['paid_amount'] ?? $invoice->paid_amount ?? 0), 2) }} ج.م</span>
        </div>
        <div style="display: flex; justify-content: space-between;" class="font-bold">
            <span>المتبقي:</span>
            <span>{{ number_format((float)($invoice['remaining_amount'] ?? $invoice->remaining_amount ?? 0), 2) }} ج.م</span>
        </div>
    </div>

    <div class="text-center py-2 border-t" style="margin-top: 8px;">
        <p style="margin: 0; font-size: 11px; font-weight: 800;">شكراً لتعاملكم مع سرور كوفي ☕</p>
    </div>

    </div>

    <script>
        function downloadReceiptAsImage() {
            const btn = document.getElementById('btn-thermal-img');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>جاري التجهيز... ⏳</span>';
            btn.style.opacity = '0.7';

            const card = document.getElementById('receipt-container');
            if (!card) {
                alert('تعذر العثور على الإيصال');
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                return;
            }

            html2canvas(card, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'إيصال-{{ $invoice["invoice_number"] ?? $invoice->invoice_number ?? "فاتورة" }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.innerHTML = '<span>تم التحميل ✅</span>';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.opacity = '1';
                }, 2000);
            }).catch(err => {
                console.error(err);
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                alert('حدث خطأ أثناء حفظ الإيصال.');
            });
        }
    </script>
</body>
</html>
