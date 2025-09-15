<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Always allowed
        $allowedDomains = [
            'localhost',
            '127.0.0.1',
        ];

        // Check exact match
        if (in_array($host, $allowedDomains)) {
            return $next($request);
        }

        // Check subdomains
        if (substr($host, -strlen('.localhost')) === '.localhost') {
            return $next($request);
        }

        // Otherwise block
        abort(404, '404 - Please contact your admin');
    }
}
