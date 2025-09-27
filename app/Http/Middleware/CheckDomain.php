<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\SendDomainAlert;

class CheckDomain
{
    /**
     * Base domains that are authorized (all subdomains included).
     */
    protected $allowedBaseDomains = [
        '127.0.0.1',
    ];

    /**
     * Suppress duplicate alerts for this many seconds (1 hour).
     */
    protected $suppressSeconds = 3600;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $currentDomain = strtolower($request->getHost());

        // Check if domain is ALLOWED
        if ($this->isAllowedDomain($currentDomain)) {
            // Domain is authorized - allow the request
            return $next($request);
        }

        // Domain is NOT allowed - send alert and block
        $this->handleUnauthorizedDomain($request, $currentDomain);

        abort(404, 'Unauthorized domain. Please contact your admin.');
    }

    /**
     * Check if the given host is allowed (including subdomains).
     */
    protected function isAllowedDomain(string $host): bool
    {
        foreach ($this->allowedBaseDomains as $base) {
            if ($host === $base || str_ends_with($host, '.' . $base)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle unauthorized domain access (with alert throttling).
     */
    protected function handleUnauthorizedDomain(Request $request, string $domain): void
    {
        $cacheKey = 'domain_alert_sent_' . md5($domain);

        if (!Cache::has($cacheKey)) {
            $extra = "IP: " . $request->ip() . "\n";
            $extra .= "URI: " . $request->getRequestUri() . "\n";
            $extra .= "User-Agent: " . $request->header('User-Agent');

            SendDomainAlert::sendAlert($domain, $extra);

            Cache::put($cacheKey, true, $this->suppressSeconds);
        }
    }
}
