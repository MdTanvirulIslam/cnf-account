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
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($request->ajax()) {
            return view('partials.importBillSummaryTable', compact('bills', 'month'))->render();
        }

        return view('reports.import_bill_summary', compact('bills', 'month'));
    }
}
