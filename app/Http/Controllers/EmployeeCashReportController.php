<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeCashReportController extends Controller
{
    /**
     * Display transactions for current month by default
     */
    public function index(Request $request)
    {
        $query = DB::table('transactions')
            ->leftJoin('employees', 'transactions.employee_id', '=', 'employees.id')
            ->select(
                'transactions.employee_id',
                'transactions.type',
                'employees.name as employee_name',
                'employees.department',
                DB::raw('SUM(transactions.amount) as total_amount'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->whereNull('transactions.deleted_at')
            ->groupBy('transactions.employee_id', 'transactions.type', 'employees.name', 'employees.department');

        // Apply filters from request
        if ($request->filled('department')) {
            $query->where('employees.department', $request->department);
        }

        if ($request->filled('paymentType')) {
            $query->where('transactions.type', strtolower($request->paymentType));
        }

        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('transactions.date', $date->year)
                ->whereMonth('transactions.date', $date->month);
            $selectedMonth = $date;
        } else {
            $selectedMonth = Carbon::now();
            $query->whereYear('transactions.date', $selectedMonth->year)
                ->whereMonth('transactions.date', $selectedMonth->month);
        }

        $groupedTransactions = $query->orderBy('employees.name', 'asc')
            ->orderBy('transactions.type', 'asc')
            ->get();

        // DEBUG: Check what we're getting
        \Log::info('Grouped transactions count: ' . $groupedTransactions->count());
        foreach ($groupedTransactions as $transaction) {
            \Log::info('Employee: ' . $transaction->employee_name . ', Type: ' . $transaction->type . ', Amount: ' . $transaction->total_amount);
        }

        return view('reports.employee_cash_report', compact('groupedTransactions', 'selectedMonth'));
    }

    public function filter(Request $request)
    {
        $query = DB::table('transactions')
            ->leftJoin('employees', 'transactions.employee_id', '=', 'employees.id')
            ->select(
                'transactions.employee_id',
                'transactions.type',
                'employees.name as employee_name',
                'employees.department',
                DB::raw('SUM(transactions.amount) as total_amount'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->whereNull('transactions.deleted_at')
            ->groupBy('transactions.employee_id', 'transactions.type', 'employees.name', 'employees.department');

        // Apply filters
        if ($request->filled('department')) {
            $query->where('employees.department', $request->department);
        }

        if ($request->filled('paymentType')) {
            $query->where('transactions.type', strtolower($request->paymentType));
        }

        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('transactions.date', $date->year)
                ->whereMonth('transactions.date', $date->month);
            $selectedMonth = $date;
        } else {
            $selectedMonth = Carbon::now();
            $query->whereYear('transactions.date', $selectedMonth->year)
                ->whereMonth('transactions.date', $selectedMonth->month);
        }

        $groupedTransactions = $query->orderBy('employees.name', 'asc')
            ->orderBy('transactions.type', 'asc')
            ->get();

        $html = view('partials.employeeCashReportTable', compact('groupedTransactions', 'selectedMonth'))->render();

        return response()->json(['html' => $html]);
    }
}
