<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->estAdmin()) {
            return redirect()->route('login')
                ->with('error', 'Accès réservé à l\'administrateur.');
        }

        return $next($request);
    }
}
