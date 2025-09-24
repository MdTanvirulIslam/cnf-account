<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankBook;
use App\Models\Account;
use App\Models\ExportBill;
use App\Models\ExportBillExpense;
use App\Models\ImportBill;
use App\Models\ImportBillExpense;
use App\Models\Expenses;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SummaryReportController extends Controller
{
    public function index(Request $request)
    {
        // Get the selected month or default to current month
        $selectedMonth = $request->filled('month')
            ? Carbon::parse($request->month)
            : Carbon::now();

        // 1. Get previous month's closing balance
        $previousMonthClosing = $this->calculatePreviousMonthClosing($selectedMonth);

        // 2. Get cash received in Dhaka Bank
        $dhakaBankReceived = $this->getBankReceived('Dhaka Bank', $selectedMonth);

        // 3. Get cash received in Cash account
        $cashReceived = $this->getBankReceived('Cash', $selectedMonth);

        // Calculate office balance
        $officeBalance = $previousMonthClosing + $dhakaBankReceived + $cashReceived;

        // 4. Get export documents data
        $exportData = $this->getExportData($selectedMonth);

        // 5. Get import documents data
        $importData = $this->getImportData($selectedMonth);

        // 6. Get office maintenance expenses
        $officeExpenses = $this->getOfficeExpenses($selectedMonth);

        // Calculate total expenses
        $totalExpenses = $exportData['total'] + $importData['total'] + $officeExpenses;

        // Calculate closing balance (can be positive or negative)
        $closingBalance = $officeBalance - $totalExpenses;

        // Prepare data array
        $data = compact(
            'selectedMonth',
            'previousMonthClosing',
            'dhakaBankReceived',
            'cashReceived',
            'officeBalance',
            'exportData',
            'importData',
            'officeExpenses',
            'totalExpenses',
            'closingBalance'
        );

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($data);
        }

        // For regular request, return the view
        return view('reports.summary_report', $data);
    }

    /**
     * Calculate previous month's closing balance
     */
    private function calculatePreviousMonthClosing($currentMonth)
    {
        $previousMonth = $currentMonth->copy()->subMonth();

        // Get income for previous month
        $previousIncome = $this->getBankReceived('Dhaka Bank', $previousMonth) +
            $this->getBankReceived('Cash', $previousMonth);

        // Get expenses for previous month
        $previousExport = $this->getExportData($previousMonth)['total'];
        $previousImport = $this->getImportData($previousMonth)['total'];
        $previousOffice = $this->getOfficeExpenses($previousMonth);

        $previousExpenses = $previousExport + $previousImport + $previousOffice;

        // Return income minus expenses (can be positive or negative)
        return $previousIncome - $previousExpenses;
    }

    /**
     * Get bank received amount for a specific account and month
     */
    private function getBankReceived($accountName, $month)
    {
        $account = Account::where('name', 'like', '%' . $accountName . '%')->first();
        if (!$account) return 0;

        return BankBook::where('account_id', $account->id)
            ->where('type', 'Receive')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->sum('amount');
    }

    /**
     * Get export data for a specific month
     */
    private function getExportData($month)
    {
        $totalQty = ExportBill::whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count('id');

        $exportBillIds = ExportBill::whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->pluck('id');

        if ($exportBillIds->count() === 0) {
            return ['qty' => 0, 'total' => 0];
        }

        $totalExpenses = ExportBillExpense::whereIn('export_bill_id', $exportBillIds)->sum('amount');
        $subtractExpenses = ExportBillExpense::whereIn('export_bill_id', $exportBillIds)
            ->where('expense_type', 'like', '%Bank C & F Vat & Others%')
            ->sum('amount');

        return [
            'qty' => $totalQty,
            'total' => $totalExpenses
        ];
    }

    /**
     * Get import data for a specific month
     */
    private function getImportData($month)
    {
        $totalQty = ImportBill::whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count('id');

        $importBillIds = ImportBill::whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->pluck('id');

        if ($importBillIds->count() === 0) {
            return ['qty' => 0, 'total' => 0];
        }

        $totalExpenses = ImportBillExpense::whereIn('import_bill_id', $importBillIds)->sum('amount');
        $subtractExpenses = ImportBillExpense::whereIn('import_bill_id', $importBillIds)
            ->where(function($query) {
                $query->where('expense_type', 'like', '%Port Bill%')
                    ->orWhere('expense_type', 'like', '%AIT%');
            })
            ->sum('amount');

        $fees = ImportBill::whereIn('id', $importBillIds)
            ->select(DB::raw('SUM(scan_fee) as total_scan_fee'), DB::raw('SUM(doc_fee) as total_doc_fee'))
            ->first();

        $totalFees = ($fees->total_scan_fee ?? 0) + ($fees->total_doc_fee ?? 0);

        return [
            'qty' => $totalQty,
            'total' => $totalExpenses
        ];
    }

    /**
     * Get office expenses for a specific month
     */
    private function getOfficeExpenses($month)
    {
        return Expenses::whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->sum('amount');
    }
}
