<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(
            $request->user()?->role === 'viewer',
            403,
            'Akun ini hanya memiliki akses lihat (read-only).'
        );

        return $next($request);
    }
}
