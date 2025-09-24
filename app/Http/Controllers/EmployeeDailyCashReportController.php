<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeDailyCashReportController extends Controller
{
    /**
     * Display daily transactions for current date range by default
     */
    public function index(Request $request)
    {
        // Set default date range (current month)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Get all employees for filter dropdown
        $employees = Employee::whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->get();

        // Get employee daily summary
        $query = DB::table('transactions')
            ->leftJoin('employees', 'transactions.employee_id', '=', 'employees.id')
            ->select(
                'transactions.employee_id',
                'transactions.date',
                'employees.name as employee_name',
                'employees.department',
                DB::raw('SUM(CASE WHEN transactions.type = "receive" THEN transactions.amount ELSE 0 END) as receive_amount'),
                DB::raw('SUM(CASE WHEN transactions.type = "return" THEN transactions.amount ELSE 0 END) as return_amount'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->whereBetween('transactions.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNull('transactions.deleted_at')
            ->groupBy('transactions.employee_id', 'transactions.date', 'employees.name', 'employees.department');

        // Apply filters
        if ($request->filled('department')) {
            $query->where('employees.department', $request->department);
        }

        if ($request->filled('employee_id')) {
            $query->where('transactions.employee_id', $request->employee_id);
        }

        if ($request->filled('paymentType')) {
            if ($request->paymentType == 'receive') {
                $query->havingRaw('SUM(CASE WHEN transactions.type = "receive" THEN transactions.amount ELSE 0 END) > 0');
            } elseif ($request->paymentType == 'return') {
                $query->havingRaw('SUM(CASE WHEN transactions.type = "return" THEN transactions.amount ELSE 0 END) > 0');
            }
        }

        $dailyTransactions = $query->orderBy('transactions.date', 'asc')
            ->orderBy('employees.name', 'asc')
            ->get();

        // DEBUG: Check what we're getting
        \Log::info('Daily transactions count: ' . $dailyTransactions->count());
        \Log::info('Date range: ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));

        if ($dailyTransactions->count() > 0) {
            foreach ($dailyTransactions->take(5) as $transaction) {
                \Log::info('Date: ' . $transaction->date . ', Employee: ' . $transaction->employee_name . ', Receive: ' . $transaction->receive_amount . ', Return: ' . $transaction->return_amount);
            }
        }

        return view('reports.employee_daily_cash_report', compact('dailyTransactions', 'startDate', 'endDate', 'employees'));
    }

    public function filter(Request $request)
    {
        // Set date range from request or default to current month
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Get all employees for filter dropdown
        $employees = Employee::whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->get();

        // Get employee daily summary
        $query = DB::table('transactions')
            ->leftJoin('employees', 'transactions.employee_id', '=', 'employees.id')
            ->select(
                'transactions.employee_id',
                'transactions.date',
                'employees.name as employee_name',
                'employees.department',
                DB::raw('SUM(CASE WHEN transactions.type = "receive" THEN transactions.amount ELSE 0 END) as receive_amount'),
                DB::raw('SUM(CASE WHEN transactions.type = "return" THEN transactions.amount ELSE 0 END) as return_amount'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->whereBetween('transactions.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNull('transactions.deleted_at')
            ->groupBy('transactions.employee_id', 'transactions.date', 'employees.name', 'employees.department');

        // Apply filters
        if ($request->filled('department')) {
            $query->where('employees.department', $request->department);
        }

        if ($request->filled('employee_id')) {
            $query->where('transactions.employee_id', $request->employee_id);
        }

        if ($request->filled('paymentType')) {
            if ($request->paymentType == 'receive') {
                $query->havingRaw('SUM(CASE WHEN transactions.type = "receive" THEN transactions.amount ELSE 0 END) > 0');
            } elseif ($request->paymentType == 'return') {
                $query->havingRaw('SUM(CASE WHEN transactions.type = "return" THEN transactions.amount ELSE 0 END) > 0');
            }
        }

        $dailyTransactions = $query->orderBy('transactions.date', 'asc')
            ->orderBy('employees.name', 'asc')
            ->get();

        $html = view('partials.employeeDailyCashReportTable', compact('dailyTransactions', 'startDate', 'endDate'))->render();

        return response()->json(['html' => $html]);
    }
}
