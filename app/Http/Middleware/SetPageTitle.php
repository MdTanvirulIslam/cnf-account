<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetPageTitle
{
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()?->getName();

        $pageTitles = [
            'bankbooks.index'  => 'Bank Book',

        ];

        $title = isset($pageTitles[$routeName]) ? $pageTitles[$routeName] : '';

        view()->share('pageTitle', $title);

        return $next($request);
    }

}
