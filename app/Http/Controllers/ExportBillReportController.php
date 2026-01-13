<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExportBill;
use App\Models\Buyer;
use App\Models\ExportBillExpense;
use Carbon\Carbon;

class ExportBillReportController extends Controller
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

    // Use the SAME expense types as ExportBillController print method
    private $expenseTypes = [
        "Bank C & F Vat & Others (As Per Receipt)",
        "Labour Bill @ Tk. 3.00 Per Ctns",
        "Landing Bill @ Tk. 207.00 Per Ton",
        "Shorting Bill @ Tk 3.00 Per Ctns",
        "Miscellaneous Expenses for documentation",
        "Automation Document Entry Fee (Refficard) Data Entry",
        "Amendment Purpose Expenses",
        "Extra Miscellaneous Exp For (Scale Charge)",
        "Carton damage & Others",
        "Kallan Fund",
        "Scale Charge (As Per Receipt)",
        "Cbm Charge (As Per Receipt)",
        "ADMIN CHARGE",
        "Special permission DSV AIR & SEA LTD",
        "Short Ship Certificate",
        "Weight Permission. Invoice P/list Dc Print",
        "Eid Boxsis",
        "DHL SHAHJAHAN/SAROWAR",
        "Other Expense",
    ];

    public function index(Request $request)
    {
        // Get last bill (for default selection) - check if exists first
        $lastBill = ExportBill::latest('id')->first();

        // Set default values safely
        $defaultBuyerId = $lastBill ? $lastBill->buyer_id : 'all';
        $defaultBeNo = $lastBill ? $lastBill->be_no : 'all';
        $defaultBillNo = $lastBill ? $lastBill->bill_no : 'all';
        $defaultCompany = $lastBill ? $lastBill->company_name : 'all';
        $defaultBillDate = $lastBill ? $lastBill->bill_date?->format('Y-m-d') : null;

        // Default or requested values
        $buyerId = $request->input('buyer', $defaultBuyerId);
        $beNo    = $request->input('be_no', $defaultBeNo);
        $billNo  = $request->input('bill_no', $defaultBillNo);
        $billDate = $request->input('bill_date', $defaultBillDate);
        $company = $request->input('company', $defaultCompany);

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

        if ($company !== 'all') {
            $query->where('company_name', $company);
        }

        if (!empty($billDate)) {
            $query->whereDate('bill_date', Carbon::parse($billDate));
        }

        $exportBills = $query->get();

        // Process each bill to include all expense types
        $processedBills = [];

        foreach ($exportBills as $bill) {
            $processedBill = $this->processBillWithAllExpenses($bill);
            $processedBills[] = $processedBill;
        }

        // Dropdowns - get ALL options
        $buyers = Buyer::pluck('name', 'id');
        $allBeNos = ExportBill::distinct()->pluck('be_no');
        $allBillNos = ExportBill::distinct()->pluck('bill_no');

        // Get filtered options based on current selection
        $beNos = $this->getFilteredOptions('be_no', $buyerId, $beNo, $billNo, $company);
        $billNos = $this->getFilteredOptions('bill_no', $buyerId, $beNo, $billNo, $company);

        // AJAX for table update only
        if ($request->ajax() && !$request->has('ajaxDropdown')) {
            return view('partials.exportBillReportTable', [
                'exportBills' => $processedBills,
                'companyAddresses' => $this->companyAddresses
            ])->render();
        }

        // AJAX for dropdown update
        if ($request->ajax() && $request->has('ajaxDropdown')) {
            return response()->json([
                'beNos' => $beNos,
                'billNos' => $billNos,
            ]);
        }

        return view('reports.export_bill_report', [
            'exportBills' => $processedBills,
            'buyers' => $buyers,
            'allBeNos' => $allBeNos,
            'allBillNos' => $allBillNos,
            'buyerId' => $buyerId,
            'beNo' => $beNo,
            'billNo' => $billNo,
            'billDate' => $billDate,
            'beNos' => $beNos,
            'billNos' => $billNos,
            'lastBill' => $lastBill,
            'company' => $company,
            'companyNames' => $this->companyNames,
            'companyAddresses' => $this->companyAddresses,
            'expenseTypes' => $this->expenseTypes
        ]);
    }

    /**
     * Process bill to include all expense types - EXACTLY like print method
     */
    private function processBillWithAllExpenses($bill)
    {
        // Initialize all types with null (blank) - EXACTLY like print method
        $expenses = array_fill_keys($this->expenseTypes, null);

        // Fill actual amounts
        foreach ($bill->expenses as $expense) {
            $expenses[$expense->expense_type] = $expense->amount;
        }

        $total = array_sum(array_filter($expenses)); // ignores nulls, exactly like print method

        // Determine company address based on company name
        $companyAddress = '';
        $companyNameForPrint = '';

        if ($bill->company_name == 'EMS APPARELS LTD') {
            $companyNameForPrint = 'EMS APPARELS LTD';
            $companyAddress = 'Barenda, Kashimpur, Gazipur, Bangladesh';
        } else {
            // Default to MULTI FABS LTD
            $companyNameForPrint = 'MULTI FABS LTD';
            $companyAddress = 'NAYAPARA, KASHIMPUR, GAZIPUR-1704, BANGLADESH';
        }

        return [
            'bill' => $bill,
            'expenses' => $expenses,
            'total' => $total,
            'companyNameForPrint' => $companyNameForPrint,
            'companyAddress' => $companyAddress,
        ];
    }

    /**
     * Get filtered options for dropdowns
     */
    private function getFilteredOptions($field, $buyerId, $beNo, $billNo, $company = 'all')
    {
        $query = ExportBill::select($field)->distinct();

        if ($buyerId !== 'all') $query->where('buyer_id', $buyerId);
        if ($beNo !== 'all') $query->where('be_no', $beNo);
        if ($billNo !== 'all') $query->where('bill_no', $billNo);
        if ($company !== 'all') $query->where('company_name', $company);

        return $query->orderBy($field)->pluck($field);
    }

    public function getDependentOptions(Request $request)
    {
        $buyerId = $request->input('buyer', 'all');
        $beNo = $request->input('be_no', 'all');
        $billNo = $request->input('bill_no', 'all');
        $company = $request->input('company', 'all');

        $beNos = $this->getFilteredOptions('be_no', $buyerId, $beNo, $billNo, $company);
        $billNos = $this->getFilteredOptions('bill_no', $buyerId, $beNo, $billNo, $company);

        return response()->json([
            'beNos' => $beNos,
            'billNos' => $billNos,
        ]);
    }
}
