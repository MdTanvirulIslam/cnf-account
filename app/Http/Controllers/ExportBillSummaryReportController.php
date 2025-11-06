<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ExportBill;

class ExportBillSummaryReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $bills = ExportBill::with('expenses')
            ->whereYear('bill_date', $year)
            ->whereMonth('bill_date', $monthNum)
            ->orderByRaw(
                "CAST(REGEXP_SUBSTR(bill_no, '[0-9]+') AS UNSIGNED) ASC"
            )
            ->get();

        if ($request->ajax()) {
            return view('partials.exportBillSummaryTable', compact('bills', 'month'))->render();
        }

        return view('reports.export_bill_summary', compact('bills', 'month'));
    }
}
