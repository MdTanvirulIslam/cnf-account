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
            'import-bills.index'  => 'Import Bills',
            'import-bills.create'  => 'Add Import Bill',
            'import-bills.edit'  => 'Update Import Bill',
            'export-bills.index'  => 'Export Bills',
            'export-bills.create'  => 'Add Export Bill',
            'export-bills.edit'  => 'Update Export Bill',
            'buyers.index'  => 'Buyers',
            'accounts.index'  => 'Accounts',
            'bankbook.report'  => 'Bank Book Report',
            'expense.report'  => 'Expense Report',
            'employee.cash.report'  => 'Employee Cash Report',
            'employee-daily-cash-report.index'  => 'Employee Daily Cash Report',
            'summary.report'  => 'Summary Report',
            'import.bill.report'  => 'Import Bill Report',
            'import.bill.summary.report'  => 'Import Bill Summary Report',
            'export.bill.report'  => 'Export Bill Report',
            'export.bill.summary.report'  => 'Export Bill Summary Report',
            'profile.edit'  => 'Profile',


        ];

        $title = isset($pageTitles[$routeName]) ? $pageTitles[$routeName] : '';

        view()->share('pageTitle', $title);

        return $next($request);
    }

}
