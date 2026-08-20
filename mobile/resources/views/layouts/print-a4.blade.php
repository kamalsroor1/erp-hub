<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة مبيعات - {{ $invoice['invoice_number'] ?? $invoice->invoice_number ?? '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&family=Tajawal:wght@500;700;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm 12mm;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: #fff !important; }
            .container { box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color: #000 !important; }
            .table th { background: #000 !important; color: #fff !important; }
        }
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            color: #000000;
            background: #f1f5f9;
            padding: 20px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border: 1px solid #cbd5e1;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #000;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand-title {
            margin: 0;
            color: #000;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            margin: 4px 0 0 0;
            color: #000;
            font-size: 15px;
            font-weight: 800;
        }
        .invoice-meta {
            text-align: left;
        }
        .invoice-title {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            color: #000;
        }
        .invoice-num {
            margin: 3px 0;
            font-weight: 900;
            font-size: 15px;
            color: #000;
            font-family: monospace;
        }
        .invoice-date {
            margin: 2px 0;
            font-size: 13px;
            font-weight: 700;
            color: #000;
        }
        .customer-card {
            background: #ffffff;
            padding: 10px 14px;
            border-radius: 6px;
            border: 2px solid #000;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }
        .table th {
            background: #000000;
            color: #ffffff;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: 900;
            text-align: right;
            border: 1.5px solid #000000;
        }
        .table td {
            padding: 8px 10px;
            border: 1.5px solid #000000;
            font-size: 13px;
            font-weight: 700;
            color: #000000;
        }
        .totals-card {
            width: 340px;
            margin-right: auto;
            background: #ffffff;
            border: 2px solid #000000;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
            font-weight: 800;
            color: #000000;
        }
        .totals-row.final-net {
            font-size: 17px;
            font-weight: 900;
            border-top: 2px solid #000000;
            padding-top: 6px;
            margin-top: 4px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 15px;
            border-top: 2px dashed #000000;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

    <div class="no-print" style="max-width: 210mm; margin: 0 auto 15px auto; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <button onclick="window.print()" style="padding: 10px 22px; background: #059669; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 900; font-family: 'Cairo'; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
            <span>🖨️ طباعة / حفظ PDF</span>
        </button>
        <button onclick="downloadAsImage()" id="btn-download-img" style="padding: 10px 22px; background: #d97706; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 900; font-family: 'Cairo'; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
            <span>📸 تحميل كصورة (PNG)</span>
        </button>
        <button onclick="window.history.back()" style="padding: 10px 20px; background: #475569; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-family: 'Cairo'; font-weight: 700; font-size: 14px;">
            رجوع
        </button>
    </div>

    <div class="container" id="invoice-print-container">
        <!-- Header -->
        <div class="header">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div>
                    <h1 class="brand-title">سرور كوفي</h1>
                    <p class="brand-subtitle">لتوريدات خامات ومطاحن البن</p>
                </div>
            </div>

            <div class="invoice-meta">
                <h2 class="invoice-title">فاتورة مبيعات</h2>
                <p class="invoice-num">{{ $invoice['invoice_number'] ?? $invoice->invoice_number ?? '' }}</p>
                <p class="invoice-date">التاريخ: {{ substr($invoice['invoice_date'] ?? $invoice->invoice_date ?? now()->toDateString(), 0, 10) }}</p>
            </div>
        </div>

        <!-- Customer & Store Info Card -->
        <div class="customer-card">
            <div>
                <span>العميل:</span>
                <strong>{{ $invoice['customer']['name'] ?? $invoice->customer->name ?? 'عميل نقدي عام' }}</strong>
                @if(!empty($invoice['customer']['phone'] ?? $invoice->customer->phone))
                    <span style="margin-right: 8px;">| هاتف: {{ $invoice['customer']['phone'] ?? $invoice->customer->phone }}</span>
                @endif
            </div>
            <div>
                <span>الفرع / نقطة البيع:</span>
                <strong>{{ $invoice['store']['name'] ?? $invoice->store->name ?? 'الفرع الرئيسي' }}</strong>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>بيان الصنف</th>
                    <th style="text-align: center; width: 90px;">الكمية</th>
                    <th style="text-align: center; width: 110px;">سعر الوحدة</th>
                    <th style="text-align: left; width: 120px;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice['items'] ?? $invoice->items ?? [] as $idx => $item)
                @php
                    $itemName = is_array($item) ? ($item['item']['name'] ?? $item['name'] ?? '') : ($item->item->name ?? $item->name ?? '');
                    $itemCode = is_array($item) ? ($item['item']['code'] ?? $item['code'] ?? '') : ($item->item->code ?? $item->code ?? '');
                    $itemQty = is_array($item) ? ($item['quantity'] ?? 1) : ($item->quantity ?? 1);
                    $itemUnit = is_array($item) ? ($item['item']['unit'] ?? $item['unit'] ?? 'كجم') : ($item->item->unit ?? $item->unit ?? 'كجم');
                    $itemPrice = is_array($item) ? ($item['unit_price'] ?? 0) : ($item->unit_price ?? 0);
                    $itemTotal = is_array($item) ? ($item['total_price'] ?? ($itemQty * $itemPrice)) : ($item->total_price ?? ($itemQty * $itemPrice));
                @endphp
                <tr>
                    <td style="text-align: center; font-family: monospace;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $itemName }}</strong>
                        @if($itemCode)
                            <div style="font-size: 11px; font-family: monospace; color: #444;">كود: {{ $itemCode }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; font-family: monospace; font-weight: 800;">{{ number_format((float)$itemQty, 2) }} {{ $itemUnit }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ number_format((float)$itemPrice, 2) }}</td>
                    <td style="text-align: left; font-family: monospace; font-weight: 900;">{{ number_format((float)$itemTotal, 2) }} ج.م</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals & Summary -->
        <div class="totals-card">
            <div class="totals-row">
                <span>المجموع الفرعي:</span>
                <span style="font-family: monospace;">{{ number_format((float)($invoice['subtotal'] ?? $invoice->subtotal ?? 0), 2) }} ج.م</span>
            </div>
            @if((float)($invoice['discount_amount'] ?? $invoice->discount_amount ?? 0) > 0)
            <div class="totals-row" style="color: #dc2626;">
                <span>الخصم:</span>
                <span style="font-family: monospace;">-{{ number_format((float)($invoice['discount_amount'] ?? $invoice->discount_amount ?? 0), 2) }} ج.م</span>
            </div>
            @endif
            <div class="totals-row final-net">
                <span>الصافي المطلوب:</span>
                <span style="font-family: monospace; color: #059669;">{{ number_format((float)($invoice['net_total'] ?? $invoice->net_total ?? 0), 2) }} ج.م</span>
            </div>
            <div class="totals-row">
                <span>المسدد:</span>
                <span style="font-family: monospace;">{{ number_format((float)($invoice['paid_amount'] ?? $invoice->paid_amount ?? 0), 2) }} ج.م</span>
            </div>
            <div class="totals-row">
                <span>المتبقي:</span>
                <span style="font-family: monospace;">{{ number_format((float)($invoice['remaining_amount'] ?? $invoice->remaining_amount ?? 0), 2) }} ج.م</span>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div>
                <strong>توقيع المستلم:</strong> ______________________
            </div>
            <div>
                <strong>المسؤول / الكاشير:</strong> {{ $invoice['user']['name'] ?? $invoice->user->name ?? 'المدير' }}
            </div>
        </div>
    </div>

    <script>
        function downloadAsImage() {
            const btn = document.getElementById('btn-download-img');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>جاري التجهيز... ⏳</span>';
            btn.style.opacity = '0.7';

            const card = document.getElementById('invoice-print-container');
            if (!card) {
                alert('تعذر العثور على الفاتورة');
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
                link.download = 'فاتورة-مبيعات-{{ $invoice["invoice_number"] ?? $invoice->invoice_number ?? "A4" }}.png';
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
                alert('حدث خطأ أثناء حفظ الفاتورة.');
            });
        }
    </script>
</body>
</html>
