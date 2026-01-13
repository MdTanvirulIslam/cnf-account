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
        try {
            // Get the selected month or default to current month
            $selectedMonth = $request->filled('month')
                ? Carbon::parse($request->month)
                : Carbon::now();

            // Get selected company or default to all
            $selectedCompany = $request->filled('company') ? $request->company : 'all';

            // Define available companies
            $companies = [
                'all' => 'All Companies',
                'MULTI FABS LTD' => 'MULTI FABS LTD',
                'EMS APPARELS LTD' => 'EMS APPARELS LTD'
            ];

            // 1. Get previous month's closing balance (NO company filter for BankBook)
            $previousMonthClosing = $this->calculatePreviousMonthClosing($selectedMonth);

            // 2. Get cash received in Dhaka Bank (NO company filter for BankBook)
            $dhakaBankReceived = $this->getBankReceived('Dhaka Bank', $selectedMonth);

            // 3. Get cash received in Cash account (NO company filter for BankBook)
            $cashReceived = $this->getBankReceived('Cash', $selectedMonth);

            // Calculate office balance
            $officeBalance = $previousMonthClosing + $dhakaBankReceived + $cashReceived;

            // 4. Get export documents data (WITH company filter)
            $exportData = $this->getExportData($selectedMonth, $selectedCompany);

            // 5. Get import documents data (WITH company filter)
            $importData = $this->getImportData($selectedMonth, $selectedCompany);

            // 6. Get office maintenance expenses (NO company filter for Expenses)
            $officeExpenses = $this->getOfficeExpenses($selectedMonth);

            // Calculate total expenses
            $totalExpenses = $exportData['total'] + $importData['total'] + $officeExpenses;

            // Calculate closing balance (can be positive or negative)
            $closingBalance = $officeBalance - $totalExpenses;

            // Prepare data array
            $data = [
                'selectedMonth' => $selectedMonth,
                'selectedCompany' => $selectedCompany,
                'companies' => $companies,
                'previousMonthClosing' => $previousMonthClosing,
                'dhakaBankReceived' => $dhakaBankReceived,
                'cashReceived' => $cashReceived,
                'officeBalance' => $officeBalance,
                'exportData' => $exportData,
                'importData' => $importData,
                'officeExpenses' => $officeExpenses,
                'totalExpenses' => $totalExpenses,
                'closingBalance' => $closingBalance
            ];

            // If it's an AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }

            // For regular request, return the view
            return view('reports.summary_report', $data);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Summary Report Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // If it's an AJAX request, return error JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading report data: ' . $e->getMessage()
                ], 500);
            }

            // For regular request, show error
            return view('reports.summary_report', [
                'error' => 'Error loading report: ' . $e->getMessage(),
                'selectedMonth' => Carbon::now(),
                'selectedCompany' => 'all',
                'companies' => [
                    'all' => 'All Companies',
                    'MULTI FABS LTD' => 'MULTI FABS LTD',
                    'EMS APPARELS LTD' => 'EMS APPARELS LTD'
                ],
                'previousMonthClosing' => 0,
                'dhakaBankReceived' => 0,
                'cashReceived' => 0,
                'officeBalance' => 0,
                'exportData' => ['qty' => 0, 'total' => 0],
                'importData' => ['qty' => 0, 'total' => 0],
                'officeExpenses' => 0,
                'totalExpenses' => 0,
                'closingBalance' => 0
            ]);
        }
    }

    /**
     * Calculate previous month's closing balance
     * NO company filter for BankBook
     */
    private function calculatePreviousMonthClosing($currentMonth)
    {
        $previousMonth = $currentMonth->copy()->subMonth();

        // Get income for previous month (NO company filter)
        $previousIncome = $this->getBankReceived('Dhaka Bank', $previousMonth) +
            $this->getBankReceived('Cash', $previousMonth);

        // Get expenses for previous month (Export/Import have company filter, Expenses does not)
        // Note: For previous month closing, we should not filter by company
        // because closing balance should be calculated for all companies
        $previousExport = ExportBill::whereYear('bill_date', $previousMonth->year)
            ->whereMonth('bill_date', $previousMonth->month)
            ->with('expenses')
            ->get()
            ->sum(function($bill) {
                return $bill->expenses->sum('amount');
            });

        $previousImport = ImportBill::whereYear('bill_date', $previousMonth->year)
            ->whereMonth('bill_date', $previousMonth->month)
            ->with('expenses')
            ->get()
            ->sum(function($bill) {
                return $bill->expenses->sum('amount');
            });

        $previousOffice = Expenses::whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->sum('amount');

        $previousExpenses = $previousExport + $previousImport + $previousOffice;

        // Return income minus expenses (can be positive or negative)
        return $previousIncome - $previousExpenses;
    }

    /**
     * Get bank received amount for a specific account and month
     * NO company filter for BankBook
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
     * WITH company filter for ExportBill
     */
    private function getExportData($month, $company = 'all')
    {
        $query = ExportBill::whereYear('bill_date', $month->year)
            ->whereMonth('bill_date', $month->month);

        // Filter by company if not 'all'
        if ($company !== 'all') {
            $query->where('company_name', $company);
        }

        $totalQty = $query->count('id');

        $exportBillIds = $query->pluck('id');

        if ($exportBillIds->count() === 0) {
            return ['qty' => 0, 'total' => 0];
        }

        $totalExpenses = ExportBillExpense::whereIn('export_bill_id', $exportBillIds)->sum('amount');

        return [
            'qty' => $totalQty,
            'total' => $totalExpenses
        ];
    }

    /**
     * Get import data for a specific month
     * WITH company filter for ImportBill
     */
    private function getImportData($month, $company = 'all')
    {
        $query = ImportBill::whereYear('bill_date', $month->year)
            ->whereMonth('bill_date', $month->month);

        // Filter by company if not 'all'
        if ($company !== 'all') {
            $query->where('company_name', $company);
        }

        $totalQty = $query->count('id');

        $importBillIds = $query->pluck('id');

        if ($importBillIds->count() === 0) {
            return ['qty' => 0, 'total' => 0];
        }

        $totalExpenses = ImportBillExpense::whereIn('import_bill_id', $importBillIds)->sum('amount');

        return [
            'qty' => $totalQty,
            'total' => $totalExpenses
        ];
    }

    /**
     * Get office expenses for a specific month
     * NO company filter for Expenses
     */
    private function getOfficeExpenses($month)
    {
        return Expenses::whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->sum('amount');
    }
}
