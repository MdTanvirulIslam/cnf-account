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
            'categories.index'  => 'Expense Categories',
            'expenses.index'  => 'Expenses',
            'employees.index'  => 'Employees',
            'transactions.import'  => 'Import Employee Transactions',
            'transactions.export'  => 'Export Employee Transactions',

        ];

        $title = isset($pageTitles[$routeName]) ? $pageTitles[$routeName] : '';

        view()->share('pageTitle', $title);

        return $next($request);
    }

}
