<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\ReturnDocument;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class ExportService
{
    /**
     * Helper to stream CSV with UTF-8 BOM for perfect Arabic display in Excel
     */
    protected function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            
            // Output UTF-8 BOM for Microsoft Excel Arabic support
            fputs($handle, "\xEF\xBB\xBF");
            
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export customer ledger to CSV
     */
    public function exportCustomerStatement(Customer $customer): StreamedResponse
    {
        $headers = ['التاريخ', 'نوع الحركة', 'رقم السند / الفاتورة', 'المستحق على العميل (مدين)', 'المسدد من العميل (دائن)', 'الرصيد بعد الحركة', 'البيان والملاحظات'];

        $entries = collect();

        // 1. Invoices
        $invoices = Invoice::where('customer_id', $customer->id)->where('status', 'confirmed')->get();
        foreach ($invoices as $inv) {
            $entries->push([
                'date'       => $inv->invoice_date->format('Y-m-d'),
                'type'       => 'فاتورة مبيعات',
                'ref'        => $inv->invoice_number,
                'debit'      => $inv->net_total,
                'credit'     => '0.000',
                'notes'      => $inv->notes ?? 'فاتورة مبيعات معتمدة',
                'timestamp'  => $inv->created_at->timestamp,
            ]);
        }

        // 2. Payments
        $payments = Payment::where('customer_id', $customer->id)->get();
        foreach ($payments as $pay) {
            $entries->push([
                'date'       => $pay->payment_date->format('Y-m-d'),
                'type'       => 'سند تحصيل نقدي',
                'ref'        => $pay->payment_number,
                'debit'      => '0.000',
                'credit'     => $pay->amount,
                'notes'      => $pay->notes ?? 'دفعة نقدية مسددة',
                'timestamp'  => $pay->created_at->timestamp,
            ]);
        }

        // 3. Returns
        $returns = ReturnDocument::where('customer_id', $customer->id)->where('return_type', 'sales_return')->get();
        foreach ($returns as $ret) {
            $entries->push([
                'date'       => $ret->return_date->format('Y-m-d'),
                'type'       => 'مرتجع مبيعات',
                'ref'        => $ret->return_number,
                'debit'      => '0.000',
                'credit'     => $ret->total_amount,
                'notes'      => $ret->reason ?? 'بضاعة مرتجعة',
                'timestamp'  => $ret->created_at->timestamp,
            ]);
        }

        $sorted = $entries->sortBy('timestamp')->values();
        $runningBalance = '0.000';
        $rows = [];

        foreach ($sorted as $row) {
            $runningBalance = bcadd($runningBalance, $row['debit'], 3);
            $runningBalance = bcsub($runningBalance, $row['credit'], 3);

            $rows[] = [
                $row['date'],
                $row['type'],
                $row['ref'],
                number_format((float)$row['debit'], 2),
                number_format((float)$row['credit'], 2),
                number_format((float)$runningBalance, 2),
                $row['notes'],
            ];
        }

        $filename = "كشف_حساب_عميل_{$customer->name}_" . date('Y-m-d') . ".csv";
        return $this->streamCsv($filename, $headers, $rows);
    }

    /**
     * Export supplier ledger to CSV
     */
    public function exportSupplierStatement(Supplier $supplier): StreamedResponse
    {
        $headers = ['التاريخ', 'نوع الحركة', 'رقم الفاتورة / السند', 'مستحق للمورد (توريد)', 'سداد للمورد (صرف)', 'الرصيد بعد الحركة', 'الملاحظات'];

        $entries = collect();

        $purchases = Purchase::where('supplier_id', $supplier->id)->where('status', 'confirmed')->get();
        foreach ($purchases as $pur) {
            $entries->push([
                'date'       => $pur->purchase_date->format('Y-m-d'),
                'type'       => 'فاتورة توريد وشراء',
                'ref'        => $pur->purchase_number,
                'debit'      => $pur->net_total,
                'credit'     => '0.000',
                'notes'      => $pur->notes ?? 'توريد بضاعة للمخزن',
                'timestamp'  => $pur->created_at->timestamp,
            ]);
        }

        $payments = Payment::where('supplier_id', $supplier->id)->get();
        foreach ($payments as $pay) {
            $entries->push([
                'date'       => $pay->payment_date->format('Y-m-d'),
                'type'       => 'سند صرف نقدي',
                'ref'        => $pay->payment_number,
                'debit'      => '0.000',
                'credit'     => $pay->amount,
                'notes'      => $pay->notes ?? 'سداد مستحقات المورد',
                'timestamp'  => $pay->created_at->timestamp,
            ]);
        }

        $returns = ReturnDocument::where('supplier_id', $supplier->id)->where('return_type', 'purchase_return')->get();
        foreach ($returns as $ret) {
            $entries->push([
                'date'       => $ret->return_date->format('Y-m-d'),
                'type'       => 'مرتجع مشتريات',
                'ref'        => $ret->return_number,
                'debit'      => '0.000',
                'credit'     => $ret->total_amount,
                'notes'      => $ret->reason ?? 'مرتجع للمورد',
                'timestamp'  => $ret->created_at->timestamp,
            ]);
        }

        $sorted = $entries->sortBy('timestamp')->values();
        $runningBalance = '0.000';
        $rows = [];

        foreach ($sorted as $row) {
            $runningBalance = bcadd($runningBalance, $row['debit'], 3);
            $runningBalance = bcsub($runningBalance, $row['credit'], 3);

            $rows[] = [
                $row['date'],
                $row['type'],
                $row['ref'],
                number_format((float)$row['debit'], 2),
                number_format((float)$row['credit'], 2),
                number_format((float)$runningBalance, 2),
                $row['notes'],
            ];
        }

        $filename = "كشف_حساب_مورد_{$supplier->name}_" . date('Y-m-d') . ".csv";
        return $this->streamCsv($filename, $headers, $rows);
    }

    /**
     * Export warehouse inventory valuation to CSV
     */
    public function exportInventory(): StreamedResponse
    {
        $headers = ['كود الصنف', 'اسم الصنف', 'القسم', 'الوحدة', 'الرصيد الحالي بالمخزن', 'سعر التكلفة للوحدة', 'سعر البيع للوحدة', 'إجمالي قيمة المخزون بالتكلفة', 'إجمالي قيمة المخزون بالبيع', 'الربح المتوقع'];

        $items = Item::active()->orderBy('category')->orderBy('name')->get();
        $rows = [];

        foreach ($items as $itm) {
            $costVal = bcmul($itm->current_stock, $itm->cost_price, 3);
            $sellVal = bcmul($itm->current_stock, $itm->selling_price, 3);
            $profitExp = bcsub($sellVal, $costVal, 3);

            $rows[] = [
                $itm->code,
                $itm->name,
                $itm->category ?? 'عام',
                $itm->unit,
                number_format((float)$itm->current_stock, 3),
                number_format((float)$itm->cost_price, 2),
                number_format((float)$itm->selling_price, 2),
                number_format((float)$costVal, 2),
                number_format((float)$sellVal, 2),
                number_format((float)$profitExp, 2),
            ];
        }

        $filename = "جرد_وتقييم_المخزون_" . date('Y-m-d') . ".csv";
        return $this->streamCsv($filename, $headers, $rows);
    }
}
