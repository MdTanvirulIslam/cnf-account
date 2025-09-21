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
                'transactions.id',
                'transactions.employee_id',
                'transactions.date',
                'transactions.amount',
                'transactions.type',
                'transactions.note',
                'transactions.created_at',
                'transactions.updated_at',
                'employees.name as employee_name',
                'employees.department'
            )
            ->whereNull('transactions.deleted_at');

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

        $transactions = $query->orderBy('transactions.date', 'asc')
            ->orderBy('transactions.created_at', 'asc')
            ->get();

        // DEBUG: Check what we're getting
        \Log::info('Transactions count: ' . $transactions->count());
        foreach ($transactions as $transaction) {
            \Log::info('Transaction ID: ' . $transaction->id . ', Amount: ' . $transaction->amount);
        }

        return view('reports.employee_cash_report', compact('transactions', 'selectedMonth'));
    }

    public function filter(Request $request)
    {
        $query = DB::table('transactions')
            ->leftJoin('employees', 'transactions.employee_id', '=', 'employees.id')
            ->select(
                'transactions.id',
                'transactions.employee_id',
                'transactions.date',
                'transactions.amount',
                'transactions.type',
                'transactions.note',
                'transactions.created_at',
                'transactions.updated_at',
                'employees.name as employee_name',
                'employees.department'
            )
            ->whereNull('transactions.deleted_at');

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

        $transactions = $query->orderBy('transactions.date', 'asc')
            ->orderBy('transactions.created_at', 'asc')
            ->get();

        $html = view('partials.employeeCashReportTable', compact('transactions', 'selectedMonth'))->render();

        return response()->json(['html' => $html]);
    }
}
