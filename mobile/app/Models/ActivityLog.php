<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'module',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'النظام التلقائي (System)',
            'phone' => '-',
        ]);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withDefault([
            'name' => 'الفرع الرئيسي',
            'type' => 'main_warehouse',
        ]);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getModuleBadgeAttribute(): array
    {
        return match ($this->module) {
            'sales'     => ['label' => 'المبيعات و POS', 'color' => 'emerald', 'icon' => '🛒'],
            'inventory' => ['label' => 'الأصناف والمخزون', 'color' => 'amber', 'icon' => '📦'],
            'purchases' => ['label' => 'المشتريات والتوريد', 'color' => 'blue', 'icon' => '🚚'],
            'shifts'    => ['label' => 'الخزينة والورديات', 'color' => 'purple', 'icon' => '💵'],
            'expenses'  => ['label' => 'المصروفات', 'color' => 'rose', 'icon' => '💸'],
            'contacts'  => ['label' => 'العملاء والموردين', 'color' => 'cyan', 'icon' => '👥'],
            'auth'      => ['label' => 'الأمان والحسابات', 'color' => 'indigo', 'icon' => '🔐'],
            default     => ['label' => 'إدارة النظام', 'color' => 'slate', 'icon' => '⚙️'],
        };
    }

    public function getActionBadgeAttribute(): array
    {
        return match ($this->action) {
            'created'      => ['label' => 'إنشاء جديد', 'color' => 'emerald', 'bg' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30'],
            'updated'      => ['label' => 'تعديل وتحديث', 'color' => 'amber', 'bg' => 'bg-amber-500/10 text-amber-600 border-amber-500/30'],
            'cancelled'    => ['label' => 'إلغاء معتمد', 'color' => 'rose', 'bg' => 'bg-rose-500/10 text-rose-600 border-rose-500/30'],
            'deleted'      => ['label' => 'حذف / أرشفة', 'color' => 'red', 'bg' => 'bg-red-500/10 text-red-600 border-red-500/30'],
            'restored'     => ['label' => 'استرجاع من السلة', 'color' => 'teal', 'bg' => 'bg-teal-500/10 text-teal-600 border-teal-500/30'],
            'login'        => ['label' => 'تسجيل دخول', 'color' => 'blue', 'bg' => 'bg-blue-500/10 text-blue-600 border-blue-500/30'],
            'shift_opened' => ['label' => 'فتح وردية', 'color' => 'cyan', 'bg' => 'bg-cyan-500/10 text-cyan-600 border-cyan-500/30'],
            'shift_closed' => ['label' => 'إغلاق وردية', 'color' => 'violet', 'bg' => 'bg-violet-500/10 text-violet-600 border-violet-500/30'],
            'payment'      => ['label' => 'سند مالي', 'color' => 'indigo', 'bg' => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/30'],
            default        => ['label' => $this->action, 'color' => 'slate', 'bg' => 'bg-slate-500/10 text-slate-600 border-slate-500/30'],
        };
    }
}
