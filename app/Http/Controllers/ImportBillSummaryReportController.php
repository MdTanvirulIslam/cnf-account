<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ImportBill;

class ImportBillSummaryReportController extends Controller
{
    // Add company names array
    private $companyNames = [
        'all' => 'All Companies',
        'MULTI FABS LTD' => 'MULTI FABS LTD',
        'EMS APPARELS LTD' => 'EMS APPARELS LTD'
    ];

    // Company addresses mapping
    private $companyAddresses = [
        'MULTI FABS LTD' => 'NAYAPARA, KASHIMPUR, GAZIPUR-1704, BANGLADESH',
        'EMS APPARELS LTD' => 'Barenda, Kashimpur, Gazipur, Bangladesh'
    ];

    public function index(Request $request)
    {
        // Get selected month (default current)
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        // Get selected company
        $company = $request->input('company', 'all');

        [$year, $monthNum] = explode('-', $month);

        // Fetch bills based on filters
        $query = ImportBill::with('expenses')
            ->whereYear('bill_date', $year)
            ->whereMonth('bill_date', $monthNum);

        // Apply company filter if not 'all'
        if ($company !== 'all') {
            $query->where('company_name', $company); // Changed from 'company' to 'company_name'
        }

        $bills = $query->orderByRaw(
            "CAST(REGEXP_SUBSTR(bill_no, '[0-9]+') AS UNSIGNED) ASC"
        )
            ->get();

        if ($request->ajax()) {
            return view('partials.importBillSummaryTable', compact('bills', 'month', 'company'))->render();
        }

        return view('reports.import_bill_summary', [
            'bills' => $bills,
            'month' => $month,
            'company' => $company,
            'companyNames' => $this->companyNames
        ]);
    }
}
