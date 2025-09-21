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
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($request->ajax()) {
            return view('partials.exportBillSummaryTable', compact('bills', 'month'))->render();
        }

        return view('reports.export_bill_summary', compact('bills', 'month'));
    }
}
