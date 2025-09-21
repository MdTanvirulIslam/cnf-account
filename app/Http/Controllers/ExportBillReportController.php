<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExportBill;
use App\Models\Buyer;
use Carbon\Carbon;

class ExportBillReportController extends Controller
{
    public function index(Request $request)
    {
        // Get last bill (for default selection)
        $lastBill = ExportBill::latest('id')->first();

        // Default or requested values
        $buyerId = $request->input('buyer', $lastBill->buyer_id ?? 'all');
        $beNo    = $request->input('be_no', $lastBill->be_no ?? 'all');
        $billNo  = $request->input('bill_no', $lastBill->bill_no ?? 'all');
        $billDate = $request->input('bill_date', null);

        // Base query
        $query = ExportBill::query()->with(['buyer', 'expenses']);

        if ($buyerId !== 'all') {
            $query->where('buyer_id', $buyerId);
        }

        if ($beNo !== 'all') {
            $query->where('be_no', $beNo);
        }

        if ($billNo !== 'all') {
            $query->where('bill_no', $billNo);
        }

        if (!empty($billDate)) {
            $query->whereDate('bill_date', Carbon::parse($billDate));
        }

        $exportBills = $query->get();

        // Calculate grand total
        $grandTotal = 0;
        foreach ($exportBills as $bill) {
            $grandTotal += $bill->expenses->sum('amount');
        }

        // Dropdowns - get ALL options
        $buyers = Buyer::pluck('name', 'id');
        $allBeNos = ExportBill::distinct()->pluck('be_no');
        $allBillNos = ExportBill::distinct()->pluck('bill_no');

        // Get filtered options based on current selection
        $beNos = $this->getFilteredOptions('be_no', $buyerId, $beNo, $billNo);
        $billNos = $this->getFilteredOptions('bill_no', $buyerId, $beNo, $billNo);

        // AJAX for table update only
        if ($request->ajax() && !$request->has('ajaxDropdown')) {
            return view('partials.exportBillReportTable', compact('exportBills', 'grandTotal'))->render();
        }

        // AJAX for dropdown update
        if ($request->ajax() && $request->has('ajaxDropdown')) {
            return response()->json([
                'beNos' => $beNos,
                'billNos' => $billNos,
            ]);
        }

        return view('reports.export_bill_report', compact(
            'exportBills',
            'grandTotal',
            'buyers',
            'allBeNos',
            'allBillNos',
            'buyerId',
            'beNo',
            'billNo',
            'billDate',
            'beNos',
            'billNos',
            'lastBill'
        ));
    }

    /**
     * Get filtered options for dropdowns
     */
    private function getFilteredOptions($field, $buyerId, $beNo, $billNo)
    {
        $query = ExportBill::select($field)->distinct();

        if ($buyerId !== 'all') $query->where('buyer_id', $buyerId);
        if ($beNo !== 'all') $query->where('be_no', $beNo);
        if ($billNo !== 'all') $query->where('bill_no', $billNo);

        return $query->orderBy($field)->pluck($field);
    }

    public function getDependentOptions(Request $request)
    {
        $buyerId = $request->input('buyer', 'all');
        $beNo = $request->input('be_no', 'all');
        $billNo = $request->input('bill_no', 'all');

        $beNos = $this->getFilteredOptions('be_no', $buyerId, $beNo, $billNo);
        $billNos = $this->getFilteredOptions('bill_no', $buyerId, $beNo, $billNo);

        return response()->json([
            'beNos' => $beNos,
            'billNos' => $billNos,
        ]);
    }
}
