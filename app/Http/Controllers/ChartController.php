<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImportBill;
use App\Models\ImportBillExpense;
use App\Models\ExportBillExpense;
use App\Models\Expenses;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function getExpenseData()
    {
        $currentYear = date('Y');

        // 1. Import Expenses (scan_fee + doc_fee + import_bill_expenses.amount)
        $importExpenses = $this->getImportExpenses($currentYear);

        // 2. Export Expenses (export_bill_expenses.amount)
        $exportExpenses = $this->getExportExpenses($currentYear);

        // 3. Office Expenses (expenses.amount)
        $officeExpenses = $this->getOfficeExpenses($currentYear);

        $monthlyData = [
            'import' => $importExpenses,
            'export' => $exportExpenses,
            'office' => $officeExpenses
        ];

        return response()->json($monthlyData);
    }

    private function getImportExpenses($year)
    {
        $monthlyData = array_fill(1, 12, 0);

        // Get scan_fee and doc_fee from import_bills
        $billFees = ImportBill::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(scan_fee + doc_fee) as total_fees')
        )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Get additional expenses from import_bill_expenses
        $additionalExpenses = ImportBillExpense::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Combine both results
        foreach ($billFees as $fee) {
            $monthlyData[$fee->month] += $fee->total_fees;
        }

        foreach ($additionalExpenses as $expense) {
            $monthlyData[$expense->month] += $expense->total_amount;
        }

        return array_values($monthlyData);
    }

    private function getExportExpenses($year)
    {
        $monthlyData = array_fill(1, 12, 0);

        $expenses = ExportBillExpense::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        foreach ($expenses as $expense) {
            $monthlyData[$expense->month] = $expense->total_amount;
        }

        return array_values($monthlyData);
    }

    private function getOfficeExpenses($year)
    {
        $monthlyData = array_fill(1, 12, 0);

        $expenses = Expenses::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereYear('date', $year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->get();

        foreach ($expenses as $expense) {
            $monthlyData[$expense->month] = $expense->total_amount;
        }

        return array_values($monthlyData);
    }

    public function showChart()
    {
        return view('your-blade-file-name'); // Your current blade file
    }
}
