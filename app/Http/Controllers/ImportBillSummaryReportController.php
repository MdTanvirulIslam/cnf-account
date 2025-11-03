<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ImportBill;

class ImportBillSummaryReportController extends Controller
{
    public function index(Request $request)
    {
        // Get selected month (default current)
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        // Fetch all bills created in that month
        $bills = ImportBill::with('expenses')
            ->whereYear('bill_date', $year)
            ->whereMonth('bill_date', $monthNum)
            ->orderBy('bill_date', 'desc')
            ->get();

        if ($request->ajax()) {
            return view('partials.importBillSummaryTable', compact('bills', 'month'))->render();
        }

        return view('reports.import_bill_summary', compact('bills', 'month'));
    }
}
