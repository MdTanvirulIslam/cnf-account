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
        $billDate = $request->input('billDate', null);

        // Base query
        $query = ImportBill::with('expenses');

        if ($lcNo !== 'all') $query->where('lc_no', $lcNo);
        if ($beNo !== 'all') $query->where('be_no', $beNo);
        if ($billNo !== 'all') $query->where('bill_no', $billNo);
        if ($billDate) $query->whereDate('bill_date', Carbon::parse($billDate));

        $importBills = $query->orderBy('bill_date', 'desc')->get();

        // Fetch dropdowns and ensure last bill values exist
        $lcNos = ImportBill::distinct()->pluck('lc_no')->toArray();
        $beNos = ImportBill::distinct()->pluck('be_no')->toArray();
        $billNos = ImportBill::distinct()->pluck('bill_no')->toArray();

        if ($lastBill) {
            if (!in_array($lastBill->lc_no, $lcNos)) $lcNos[] = $lastBill->lc_no;
            if (!in_array($lastBill->be_no, $beNos)) $beNos[] = $lastBill->be_no;
            if (!in_array($lastBill->bill_no, $billNos)) $billNos[] = $lastBill->bill_no;
        }

        if ($request->ajax()) {
            $html = view('partials.importBillReportTable', compact('importBills'))->render();
            return response()->json(['html' => $html]);
        }

        return view('reports.import_bill_report', compact(
            'importBills', 'lcNo', 'beNo', 'billNo', 'billDate', 'lcNos', 'beNos', 'billNos'
        ));
    }

    public function getDependentOptions(Request $request)
    {
        $lcNo = $request->input('lcNo', null);
        $beNo = $request->input('be_no', null);
        $billNo = $request->input('bill_no', null);

        $query = ImportBill::query();

        // Only filter if specific value selected, skip 'all'
        if ($lcNo && $lcNo !== 'all') {
            $query->where('lc_no', $lcNo);
        }
        if ($beNo && $beNo !== 'all') {
            $query->where('be_no', $beNo);
        }
        if ($billNo && $billNo !== 'all') {
            $query->where('bill_no', $billNo);
        }

        // Get distinct values for all three dropdowns
        $lcNos = ImportBill::select('lc_no')->distinct()
            ->when($beNo && $beNo !== 'all', fn($q) => $q->where('be_no', $beNo))
            ->when($billNo && $billNo !== 'all', fn($q) => $q->where('bill_no', $billNo))
            ->pluck('lc_no');

        $beNos = ImportBill::select('be_no')->distinct()
            ->when($lcNo && $lcNo !== 'all', fn($q) => $q->where('lc_no', $lcNo))
            ->when($billNo && $billNo !== 'all', fn($q) => $q->where('bill_no', $billNo))
            ->pluck('be_no');

        $billNos = ImportBill::select('bill_no')->distinct()
            ->when($lcNo && $lcNo !== 'all', fn($q) => $q->where('lc_no', $lcNo))
            ->when($beNo && $beNo !== 'all', fn($q) => $q->where('be_no', $beNo))
            ->pluck('bill_no');

        return response()->json([
            'lcNos' => $lcNos,
            'beNos' => $beNos,
            'billNos' => $billNos,
        ]);
    }


}
