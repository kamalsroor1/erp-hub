<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\Expense;
use App\Models\ReturnDocument;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TrashController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->input('tab', 'items');
        $search = trim((string)$request->input('search', ''));

        $itemsCount = Item::onlyTrashed()->count();
        $customersCount = Customer::onlyTrashed()->count();
        $suppliersCount = Supplier::onlyTrashed()->count();
        $storesCount = Store::onlyTrashed()->count();
        $expensesCount = Expense::onlyTrashed()->count();
        $returnsCount = ReturnDocument::onlyTrashed()->count();

        $records = [];

        if ($tab === 'items') {
            $q = Item::onlyTrashed();
            if ($search !== '') $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            $records = $q->latest('deleted_at')->paginate(15)->through(fn($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'code' => $i->code,
                'category' => $i->category,
                'deleted_at' => $i->deleted_at->diffForHumans(),
            ]);
        } elseif ($tab === 'customers') {
            $q = Customer::onlyTrashed();
            if ($search !== '') $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
            $records = $q->latest('deleted_at')->paginate(15)->through(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'deleted_at' => $c->deleted_at->diffForHumans(),
            ]);
        } elseif ($tab === 'suppliers') {
            $q = Supplier::onlyTrashed();
            if ($search !== '') $q->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%");
            $records = $q->latest('deleted_at')->paginate(15)->through(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'company_name' => $s->company_name,
                'deleted_at' => $s->deleted_at->diffForHumans(),
            ]);
        } elseif ($tab === 'stores') {
            $q = Store::onlyTrashed();
            if ($search !== '') $q->where('name', 'like', "%{$search}%");
            $records = $q->latest('deleted_at')->paginate(15)->through(fn($st) => [
                'id' => $st->id,
                'name' => $st->name,
                'type' => $st->type,
                'deleted_at' => $st->deleted_at->diffForHumans(),
            ]);
        } elseif ($tab === 'expenses') {
            $q = Expense::onlyTrashed();
            if ($search !== '') $q->where('title', 'like', "%{$search}%");
            $records = $q->latest('deleted_at')->paginate(15)->through(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'amount' => (float)$e->amount,
                'category' => $e->category,
                'deleted_at' => $e->deleted_at->diffForHumans(),
            ]);
        } elseif ($tab === 'returns') {
            $q = ReturnDocument::onlyTrashed();
            if ($search !== '') $q->where('return_number', 'like', "%{$search}%");
            $records = $q->latest('deleted_at')->paginate(15)->through(fn($r) => [
                'id' => $r->id,
                'return_number' => $r->return_number,
                'net_total' => (float)$r->net_total,
                'deleted_at' => $r->deleted_at->diffForHumans(),
            ]);
        }

        return Inertia::render('Trash/Index', [
            'tab' => $tab,
            'records' => $records,
            'counts' => [
                'items' => $itemsCount,
                'customers' => $customersCount,
                'suppliers' => $suppliersCount,
                'stores' => $storesCount,
                'expenses' => $expensesCount,
                'returns' => $returnsCount,
            ],
            'filters' => [
                'search' => $search,
                'tab' => $tab,
            ],
        ]);
    }

    public function restore(string $type, int $id)
    {
        $model = match ($type) {
            'items' => Item::onlyTrashed()->findOrFail($id),
            'customers' => Customer::onlyTrashed()->findOrFail($id),
            'suppliers' => Supplier::onlyTrashed()->findOrFail($id),
            'stores' => Store::onlyTrashed()->findOrFail($id),
            'expenses' => Expense::onlyTrashed()->findOrFail($id),
            'returns' => ReturnDocument::onlyTrashed()->findOrFail($id),
            default => abort(404),
        };

        $model->restore();

        return redirect()->back()->with('success', 'تم استعادة السجل المحذوف بنجاح');
    }

    public function forceDelete(string $type, int $id)
    {
        $model = match ($type) {
            'items' => Item::onlyTrashed()->findOrFail($id),
            'customers' => Customer::onlyTrashed()->findOrFail($id),
            'suppliers' => Supplier::onlyTrashed()->findOrFail($id),
            'stores' => Store::onlyTrashed()->findOrFail($id),
            'expenses' => Expense::onlyTrashed()->findOrFail($id),
            'returns' => ReturnDocument::onlyTrashed()->findOrFail($id),
            default => abort(404),
        };

        $model->forceDelete();

        return redirect()->back()->with('success', 'تم حذف السجل نهائياً من قاعدة البيانات');
    }
}