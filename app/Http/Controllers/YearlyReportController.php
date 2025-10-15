<?php

namespace App\Http\Controllers;

use App\Models\ImportBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class YearlyReportController extends Controller
{
    public function index()
    {
        $total_received = $this->allReceivedYearlyReport();
        $total_expense = $this->allExpenseYearlyReport();
        $this_month_receive = $this->thisMonthReceiveAmount();
        $individual_cost = $this->allOverIndividualCost();
        $this_month_sonali_receive = $this->thisMonthSonaliReceivedAmount();
        $this_moth_janata_receive = $this->thisMonthJanataReceivedAmount();

        return view('dashboard', compact([
            'total_received',
            'total_expense',
            'this_month_receive',
            'individual_cost',
            'this_month_sonali_receive',
            'this_moth_janata_receive'
        ]));
    }

    public function allReceivedYearlyReport()
    {
        $result = DB::table('accounts')
            ->join('bank_books', 'accounts.id', '=', 'bank_books.account_id')
            ->select(DB::raw('COALESCE(SUM(bank_books.amount), 0) as total_receive'))
            ->whereIn('accounts.name', ['Cash', 'Dhaka Bank'])
            ->where('bank_books.type', 'Receive')
            ->whereNull('bank_books.deleted_at') // Fixed: should be null for active records
            ->whereYear('bank_books.created_at', Carbon::now()->year)
            ->first();

        return $result->total_receive ?? 0;
    }

    public function allExpenseYearlyReport()
    {
        $result = DB::table('bank_books')
            ->select(DB::raw('SUM(amount) as total_expense'))
            ->whereNull('bank_books.deleted_at') // Fixed: should be null for active records
            ->whereIn('type', ['Export Bill', 'Import Bill', 'Expense', 'Pay Order'])
            ->whereYear('bank_books.created_at', Carbon::now()->year)
            ->first();

        return $result->total_expense ?? 0;
    }

    public function thisMonthReceiveAmount()
    {
        $result = DB::table('accounts')
            ->join('bank_books', 'accounts.id', '=', 'bank_books.account_id')
            ->select(DB::raw('COALESCE(SUM(bank_books.amount), 0) as total_receive'))
            ->whereIn('accounts.name', ['Cash', 'Dhaka Bank'])
            ->where('bank_books.type', 'Receive')
            ->whereNull('bank_books.deleted_at') // Fixed: should be null for active records
            ->whereYear('bank_books.created_at', Carbon::now()->year)
            ->whereMonth('bank_books.created_at', Carbon::now()->month)
            ->first();

        return $result->total_receive ?? 0;
    }

    public function allOverIndividualCost()
    {
        // Office Expenses (only non-deleted records)
        $office_expense = DB::table('expenses')
            ->select(DB::raw('COALESCE(SUM(amount), 0) as total_expense'))
            ->whereNull('deleted_at')
            ->first();

        // Export Bill Expenses (only non-deleted records)
        $total_export_cost = DB::table('export_bill_expenses')
            ->select(DB::raw('COALESCE(SUM(amount), 0) as total_export_cost'))

            ->first();

        // Import Bill Expenses (only non-deleted records)
        $import_cost = DB::table('import_bill_expenses')
            ->select(DB::raw('COALESCE(SUM(amount), 0) as import_cost'))

            ->first();

        // Import Bill Fees (only non-deleted records)
        $fees = DB::table('import_bills')
            ->select(DB::raw('COALESCE(SUM(scan_fee), 0) as total_scan_fee'), DB::raw('COALESCE(SUM(doc_fee), 0) as total_doc_fee'))

            ->first();

        $total_import_cost = ($import_cost->import_cost ?? 0) + ($fees->total_scan_fee ?? 0) + ($fees->total_doc_fee ?? 0);

        // Calculate individual values
        $office_expense_value = $office_expense->total_expense ?? 0;
        $export_cost_value = $total_export_cost->total_export_cost ?? 0;
        $import_cost_value = $total_import_cost;

        // Calculate total for percentages
        $total_all_costs = $office_expense_value + $export_cost_value + $import_cost_value;

        // Calculate percentages (avoid division by zero)
        $office_percentage = $total_all_costs > 0 ? ($office_expense_value / $total_all_costs) * 100 : 0;
        $export_percentage = $total_all_costs > 0 ? ($export_cost_value / $total_all_costs) * 100 : 0;
        $import_percentage = $total_all_costs > 0 ? ($import_cost_value / $total_all_costs) * 100 : 0;

        return [
            'office_expense' => $office_expense_value,
            'total_export_cost' => $export_cost_value,
            'total_import_cost' => $import_cost_value,
            'office_percentage' => round($office_percentage, 2),
            'export_percentage' => round($export_percentage, 2),
            'import_percentage' => round($import_percentage, 2),
            'total_all_costs' => $total_all_costs
        ];
    }

    public function thisMonthSonaliReceivedAmount()
    {
        $result = DB::table('accounts')
            ->join('bank_books', 'accounts.id', '=', 'bank_books.account_id')
            ->select(DB::raw('COALESCE(SUM(bank_books.amount), 0) as total_receive'))
            ->where('accounts.name', 'Sonali Bank')
            ->where('bank_books.type', 'Receive')
            ->whereNull('bank_books.deleted_at') // Fixed: should be null for active records
            ->whereYear('bank_books.created_at', Carbon::now()->year)
            ->whereMonth('bank_books.created_at', Carbon::now()->month)
            ->first();

        return $result->total_receive ?? 0;
    }

    public function thisMonthJanataReceivedAmount()
    {
        $result = DB::table('accounts')
            ->join('bank_books', 'accounts.id', '=', 'bank_books.account_id')
            ->select(DB::raw('COALESCE(SUM(bank_books.amount), 0) as total_receive'))
            ->where('accounts.name', 'Janata Bank')
            ->where('bank_books.type', 'Receive')
            ->whereNull('bank_books.deleted_at') // Fixed: should be null for active records
            ->whereYear('bank_books.created_at', Carbon::now()->year)
            ->whereMonth('bank_books.created_at', Carbon::now()->month)
            ->first();

        return $result->total_receive ?? 0;
    }

    /**
     * Additional useful methods for dashboard
     */
    public function getMonthlyData($year = null)
    {
        $year = $year ?? Carbon::now()->year;

        $monthlyData = DB::table('bank_books')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(CASE WHEN type = "Receive" THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN type IN ("Export Bill", "Import Bill", "Expense", "Pay Order") THEN amount ELSE 0 END) as expense')
            )
            ->whereNull('deleted_at')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        return $monthlyData;
    }

    /**
     * Get current year profit/loss
     */
    public function getYearlyProfitLoss()
    {
        $income = $this->allReceivedYearlyReport();
        $expense = $this->allExpenseYearlyReport();

        return $income - $expense;
    }

    /**
     * Get bank-wise current month receipts
     */
    public function getBankWiseThisMonthReceipts()
    {
        $result = DB::table('accounts')
            ->join('bank_books', 'accounts.id', '=', 'bank_books.account_id')
            ->select(
                'accounts.name as bank_name',
                DB::raw('COALESCE(SUM(bank_books.amount), 0) as total_receive')
            )
            ->where('bank_books.type', 'Receive')
            ->whereNull('bank_books.deleted_at')
            ->whereYear('bank_books.created_at', Carbon::now()->year)
            ->whereMonth('bank_books.created_at', Carbon::now()->month)
            ->groupBy('accounts.name')
            ->get();

        return $result;
    }
}
