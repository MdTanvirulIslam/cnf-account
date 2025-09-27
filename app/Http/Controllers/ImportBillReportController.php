<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImportBill;
use Carbon\Carbon;

class ImportBillReportController extends Controller
{
    public function index(Request $request)
    {
        // Last bill for default values
        $lastBill = ImportBill::latest('id')->first();

        $lcNo = $request->input('lcNo', $lastBill->lc_no ?? 'all');
        $beNo = $request->input('be_no', $lastBill->be_no ?? 'all');
        $billNo = $request->input('bill_no', $lastBill->bill_no ?? 'all');
        $billDate = $request->input('billDate', $lastBill?->bill_date?->format('Y-m-d'));
        // Base query
        $query = ImportBill::with('expenses');

        if ($lcNo !== 'all') $query->where('lc_no', $lcNo);
        if ($beNo !== 'all') $query->where('be_no', $beNo);
        if ($billNo !== 'all') $query->where('bill_no', $billNo);
        if ($billDate) $query->whereDate('bill_date', Carbon::parse($billDate));

        $importBills = $query->orderBy('bill_date', 'desc')->get();

        // Fetch ALL options for dropdowns (not filtered)
        $allLcNos = ImportBill::distinct()->orderBy('lc_no')->pluck('lc_no')->toArray();
        $allBeNos = ImportBill::distinct()->orderBy('be_no')->pluck('be_no')->toArray();
        $allBillNos = ImportBill::distinct()->orderBy('bill_no')->pluck('bill_no')->toArray();

        if ($request->ajax()) {
            $html = view('partials.importBillReportTable', compact('importBills'))->render();
            return response()->json(['html' => $html]);
        }

        return view('reports.import_bill_report', compact(
            'importBills', 'lcNo', 'beNo', 'billNo', 'billDate', 'allLcNos', 'allBeNos', 'allBillNos', 'lastBill'
        ));
    }

    public function getDependentOptions(Request $request)
    {
        $lcNo = $request->input('lcNo', 'all');
        $beNo = $request->input('be_no', 'all');
        $billNo = $request->input('bill_no', 'all');

        // Get filtered options based on selections
        $lcNos = ImportBill::select('lc_no')->distinct()
            ->when($beNo !== 'all', fn($q) => $q->where('be_no', $beNo))
            ->when($billNo !== 'all', fn($q) => $q->where('bill_no', $billNo))
            ->orderBy('lc_no')->pluck('lc_no')->toArray();

        $beNos = ImportBill::select('be_no')->distinct()
            ->when($lcNo !== 'all', fn($q) => $q->where('lc_no', $lcNo))
            ->when($billNo !== 'all', fn($q) => $q->where('bill_no', $billNo))
            ->orderBy('be_no')->pluck('be_no')->toArray();

        $billNos = ImportBill::select('bill_no')->distinct()
            ->when($lcNo !== 'all', fn($q) => $q->where('lc_no', $lcNo))
            ->when($beNo !== 'all', fn($q) => $q->where('be_no', $beNo))
            ->orderBy('bill_no')->pluck('bill_no')->toArray();

        return response()->json([
            'lcNos' => $lcNos,
            'beNos' => $beNos,
            'billNos' => $billNos,
        ]);
    }
}
