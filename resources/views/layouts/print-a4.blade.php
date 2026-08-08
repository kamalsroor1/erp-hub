<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة مبيعات ضريبية - {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 12mm 15mm;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
        }
        body {
            font-family: 'Cairo', sans-serif;
            color: #1e293b;
            background: #f8fafc;
            padding: 20px;
            font-size: 13px;
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background: #0f172a;
            color: #fff;
            padding: 8px 12px;
            font-size: 12px;
            text-align: right;
        }
        .table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        .totals-card {
            width: 320px;
            margin-right: auto;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }
    </style>
</head>
<body>

    <div class="no-print" style="max-width: 210mm; margin: 0 auto 15px auto; display: flex; gap: 10px;">
        <button onclick="window.print()" style="padding: 8px 20px; background: #059669; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-family: 'Cairo';">🖨️ طباعة الفاتورة (A4)</button>
        <button onclick="window.history.back()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-family: 'Cairo';">رجوع</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1 style="margin: 0; color: #059669; font-size: 24px; font-weight: 900;">مؤسسة سرور التجارية</h1>
                <p style="margin: 4px 0; color: #64748b;">لتجارة التجزئة والجملة والأنظمة</p>
                <p style="margin: 2px 0; font-size: 11px;">الرقم الضريبي: 300123456789003 | السجل التجاري: 102938</p>
            </div>
            <div style="text-align: left;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;">فاتورة مبيعات</h2>
                <p style="margin: 3px 0; font-weight: bold; color: #059669;">{{ $invoice->invoice_number }}</p>
                <p style="margin: 2px 0; font-size: 11px; color: #64748b;">التاريخ: {{ $invoice->invoice_date->format('Y-m-d') }}</p>
            </div>
        </div>

        <!-- Info Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8fafc; padding: 12px 16px; border-radius: 6px; border: 1px solid #e2e8f0;">
            <div>
                <strong style="color: #64748b; font-size: 11px;">بيانات العميل:</strong>
                <p style="margin: 2px 0; font-weight: bold; font-size: 14px;">{{ $invoice->customer->name }}</p>
                <p style="margin: 2px 0; font-size: 11px; color: #64748b;">الهاتف: {{ $invoice->customer->phone ?? 'غير مسجل' }}</p>
                <p style="margin: 2px 0; font-size: 11px; color: #64748b;">العنوان: {{ $invoice->customer->address ?? 'غير مسجل' }}</p>
            </div>
            <div style="text-align: left;">
                <strong style="color: #64748b; font-size: 11px;">تفاصيل السداد:</strong>
                <p style="margin: 2px 0; font-weight: bold;">
                    @if($invoice->payment_type === 'cash') نقدي (Cash)
                    @elseif($invoice->payment_type === 'credit') آجل (Credit)
                    @else دفع جزئي
                    @endif
                </p>
                <p style="margin: 2px 0; font-size: 11px; color: #64748b;">حالة السداد: 
                    @if($invoice->payment_status === 'paid') <span style="color: #059669; font-weight: bold;">مدفوعة بالكامل</span>
                    @elseif($invoice->payment_status === 'partially_paid') <span style="color: #d97706; font-weight: bold;">مسددة جزئياً</span>
                    @else <span style="color: #dc2626; font-weight: bold;">غير مسددة (آجل)</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th>الصنف والوصف</th>
                    <th style="text-align: center; width: 80px;">الكمية</th>
                    <th style="text-align: center; width: 100px;">سعر الوحدة</th>
                    <th style="text-align: center; width: 80px;">الخصم</th>
                    <th style="text-align: left; width: 110px;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->item->name }}</strong>
                        <div style="font-size: 10px; color: #64748b;">كود: {{ $item->item->code }}</div>
                    </td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 2) }} {{ $item->item->unit }}</td>
                    <td style="text-align: center;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: center; color: #dc2626;">{{ number_format($item->discount_amount, 2) }}</td>
                    <td style="text-align: left; font-weight: bold;">{{ number_format($item->total_price, 2) }} ج.م</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Card -->
        <div class="totals-card">
            <div class="totals-row">
                <span>المجموع الفرعي:</span>
                <span>{{ number_format($invoice->subtotal, 2) }} ج.م</span>
            </div>
            @if(bccomp($invoice->discount_amount, '0.000', 3) > 0)
            <div class="totals-row" style="color: #dc2626;">
                <span>إجمالي الخصومات:</span>
                <span>-{{ number_format($invoice->discount_amount, 2) }} ج.م</span>
            </div>
            @endif
            <div class="totals-row" style="font-size: 15px; font-weight: 900; color: #059669; border-top: 2px solid #cbd5e1; padding-top: 6px; margin-top: 4px;">
                <span>الصافي النهائي:</span>
                <span>{{ number_format($invoice->net_total, 2) }} ج.م</span>
            </div>
            <div class="totals-row">
                <span>المدفوع:</span>
                <span>{{ number_format($invoice->paid_amount, 2) }} ج.م</span>
            </div>
            <div class="totals-row" style="font-weight: bold; color: {{ bccomp($invoice->remaining_amount, '0.000', 3) > 0 ? '#dc2626' : '#059669' }};">
                <span>المتبقي المطلوب:</span>
                <span>{{ number_format($invoice->remaining_amount, 2) }} ج.م</span>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div style="text-align: center;">
                <p style="margin: 0; font-weight: bold;">توقيع المستلم</p>
                <p style="margin: 30px 0 0 0; color: #cbd5e1;">....................................</p>
            </div>
            <div style="text-align: center;">
                <p style="margin: 0; font-weight: bold;">ختم وتوقيع الإدارة</p>
                <p style="margin: 30px 0 0 0; color: #cbd5e1;">....................................</p>
            </div>
        </div>
    </div>

</body>
</html>
