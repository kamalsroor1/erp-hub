<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ApiService;

class MobileApiAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!ApiService::isAuthenticated()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
