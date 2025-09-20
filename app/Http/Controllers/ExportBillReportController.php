<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExportBill;
use App\Models\Buyer;

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
        $query = ExportBill::query();

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
            $query->whereDate('bill_date', $billDate);
        }

        $exportBills = $query->get();

        // Dropdowns
        $buyers = Buyer::pluck('name', 'id');

        if ($buyerId !== 'all') {
            $beNos = ExportBill::where('buyer_id', $buyerId)->distinct()->pluck('be_no');
            $billNos = ExportBill::where('buyer_id', $buyerId)->distinct()->pluck('bill_no');
        } else {
            $beNos = ExportBill::distinct()->pluck('be_no');
            $billNos = ExportBill::distinct()->pluck('bill_no');
        }

        // AJAX for table update only
        if ($request->ajax() && !$request->has('ajaxDropdown')) {
            return view('partials.exportBillReportTable', compact('exportBills'))->render();
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
            'buyers',
            'buyerId',
            'beNo',
            'billNo',
            'billDate',
            'beNos',
            'billNos'
        ));
    }
}
