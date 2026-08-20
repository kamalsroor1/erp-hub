<?php

namespace App\Filters\Tenants;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter
{
    public function __construct(
        protected Request $request
    ) {}

    public function handle(Builder $query, Closure $next)
    {
        $status = $this->request->query('status') ?? $this->request->input('status');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $next($query);
    }
}
