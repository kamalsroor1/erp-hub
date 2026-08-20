<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب عميل - {{ $customer['name'] ?? '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&family=Tajawal:wght@500;700;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm 12mm;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: #fff !important; }
            .container { box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color: #000 !important; }
            .table th { background: #000 !important; color: #fff !important; }
        }
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            color: #000000;
            background: #f1f5f9;
            padding: 20px;
            font-size: 13px;
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
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }
        .table th {
            background: #000000;
            color: #ffffff;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 900;
            text-align: right;
            border: 1.5px solid #000000;
        }
        .table td {
            padding: 7px 10px;
            border: 1.5px solid #000000;
            font-size: 12px;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="no-print" style="max-width: 210mm; margin: 0 auto 15px auto; display: flex; gap: 10px; align-items: center;">
        <button onclick="window.print()" style="padding: 10px 22px; background: #059669; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 900; font-family: 'Cairo'; font-size: 14px;">
            🖨️ طباعة كشف الحساب
        </button>
        <button onclick="window.history.back()" style="padding: 10px 20px; background: #475569; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-family: 'Cairo'; font-weight: 700; font-size: 14px;">
            رجوع
        </button>
    </div>

    <div class="container">
        <div class="header">
            <div>
                <h1 style="margin: 0; font-size: 24px; font-weight: 900;">سرور كوفي</h1>
                <p style="margin: 2px 0 0 0; font-size: 14px; font-weight: 800;">لتوريدات خامات ومطاحن البن</p>
            </div>
            <div style="text-align: left;">
                <h2 style="margin: 0; font-size: 20px; font-weight: 900;">كشف حساب تفصيلي</h2>
                <p style="margin: 2px 0 0 0; font-size: 12px;">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <!-- Customer Summary -->
        <div style="display: flex; justify-content: space-between; background: #fff; border: 2px solid #000; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px;">
            <div>
                <span>العميل:</span>
                <strong style="font-size: 15px;">{{ $customer['name'] ?? '' }}</strong>
                @if(!empty($customer['phone']))
                    <span style="margin-right: 8px;">| هاتف: {{ $customer['phone'] }}</span>
                @endif
            </div>
            <div>
                <span>الرصيد المدين المطلوب:</span>
                <strong style="font-size: 16px; font-family: monospace; color: #dc2626;">
                    {{ number_format((float)($summary['net_balance'] ?? $customer['current_balance'] ?? 0), 2) }} ج.م
                </strong>
            </div>
        </div>

        <!-- Ledger Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 85px;">التاريخ</th>
                    <th style="width: 110px;">نوع الحركة</th>
                    <th style="width: 140px;">رقم السند / الفاتورة</th>
                    <th style="text-align: center; width: 100px;">مدين (+) فاتورة</th>
                    <th style="text-align: center; width: 100px;">دائن (-) سداد</th>
                    <th>البيان والملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledger ?? [] as $row)
                <tr>
                    <td style="font-family: monospace;">{{ substr($row['date'] ?? '', 0, 10) }}</td>
                    <td>{{ $row['type_label'] ?? $row['type'] }}</td>
                    <td style="font-family: monospace; font-weight: 800;">{{ $row['document_number'] ?? '-' }}</td>
                    <td style="text-align: center; font-family: monospace; color: #dc2626;">
                        {{ (float)($row['debit'] ?? 0) > 0 ? number_format((float)$row['debit'], 2) : '-' }}
                    </td>
                    <td style="text-align: center; font-family: monospace; color: #059669;">
                        {{ (float)($row['credit'] ?? 0) > 0 ? number_format((float)$row['credit'], 2) : '-' }}
                    </td>
                    <td>{{ $row['notes'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Card -->
        <div style="width: 320px; margin-right: auto; border: 2px solid #000; border-radius: 6px; padding: 10px 14px; margin-top: 15px;">
            <div style="display: flex; justify-content: space-between; padding: 3px 0;">
                <span>إجمالي الفواتير:</span>
                <span style="font-family: monospace; font-weight: 800;">{{ number_format((float)($summary['total_invoices_amount'] ?? 0), 2) }} ج.م</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 3px 0;">
                <span>إجمالي المسدد:</span>
                <span style="font-family: monospace; font-weight: 800; color: #059669;">{{ number_format((float)($summary['total_paid_amount'] ?? 0), 2) }} ج.م</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 6px 0; border-top: 2px solid #000; font-size: 15px; font-weight: 900;">
                <span>الرصيد الصافي المطلوب:</span>
                <span style="font-family: monospace; color: #dc2626;">{{ number_format((float)($summary['net_balance'] ?? 0), 2) }} ج.م</span>
            </div>
        </div>
    </div>
</body>
</html>
