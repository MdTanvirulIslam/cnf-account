<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImportBill;
use Carbon\Carbon;

class ImportBillReportController extends Controller
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

    // Use the SAME expense types as ImportBillController print method
    private $expenseTypes = [
        "Kallan Fund Bill (As Per Receipt)",
        "Data Entry (As Per Rcpt)",
        "Reffie Card Bill (As Per Receipt)",
        "AIT (As Per Receipt)",
        "Port Bill (As Per Receipt)",
        "PORT BILL (RSGT/PCT)",
        "Agent Bill (As Per Receipt)",
        "Labour Bill (As Per Rcpt)",
        "Depot Bill (As Per Rcpt)",
        "Bank Guarantee Bank Verify (As Per Receipt)",
        "Extra Expenses For Custom",
        "Custom Air",
        "Cover Van Labour",
        "Nilmark",
        "Breaking Permission",
        "Assistance Commissioner With Pion (Random Selection)",
        "Extra Expenses For Marine Policy",
        "Documents Expenses",
        "CONTAINER KEEP DOWN",
        "NOTE SHEET TYPE",
        "100% EXAMINE PURPOSE",
        "TEST EXPENSES",
        "HISTAR",
        "BREAKING",
        "WRONG MARK",
        "PART PARMISSION",
        "BANK GUARANTEE BANK VERIFY (AS PER RECEIPT)",
        "CHITTY ISSUE",
        "ASSISTANT COMMISSIONER WITH PION",
        "TEST EXPENSES FOR CUTE",
        "PAY ORDER CHARGE",
        "EXTRA EXPENSES FOR CUSTOMS (ARO,RO,AC)",
        "EXTRA EXPENSES FOR CUSTOMS (PORT)",
        "SPECIAL PERMISSION",
        "IGM AMENDMENT PURPOSE",
        "VAT PAYMENT EXPENSES",
        "Other Expense",
    ];

    public function index(Request $request)
    {
        // Last bill for default values
        $lastBill = ImportBill::latest('id')->first();

        // Default or requested values
        $lcNo = $request->input('lcNo', $lastBill->lc_no ?? 'all');
        $beNo = $request->input('be_no', $lastBill->be_no ?? 'all');
        $billNo = $request->input('bill_no', $lastBill->bill_no ?? 'all');
        $billDate = $request->input('billDate', $lastBill?->bill_date?->format('Y-m-d'));
        $company = $request->input('company', $lastBill->company_name ?? 'all');

        // Base query
        $query = ImportBill::with('expenses');

        if ($lcNo !== 'all') $query->where('lc_no', $lcNo);
        if ($beNo !== 'all') $query->where('be_no', $beNo);
        if ($billNo !== 'all') $query->where('bill_no', $billNo);
        if ($company !== 'all') $query->where('company_name', $company);
        if ($billDate) $query->whereDate('bill_date', Carbon::parse($billDate));

        $importBills = $query->orderBy('bill_date', 'desc')->get();

        // Process each bill to include all expense types
        $processedBills = [];
        foreach ($importBills as $bill) {
            $processedBills[] = $this->processBillWithAllExpenses($bill);
        }

        // Fetch ALL options for dropdowns (not filtered)
        $allLcNos = ImportBill::distinct()->orderBy('lc_no')->pluck('lc_no')->toArray();
        $allBeNos = ImportBill::distinct()->orderBy('be_no')->pluck('be_no')->toArray();
        $allBillNos = ImportBill::distinct()->orderBy('bill_no')->pluck('bill_no')->toArray();

        if ($request->ajax()) {
            return view('partials.importBillReportTable', [
                'importBills' => $processedBills,
                'companyAddresses' => $this->companyAddresses
            ])->render();
        }

        return view('reports.import_bill_report', [
            'importBills' => $processedBills,
            'lcNo' => $lcNo,
            'beNo' => $beNo,
            'billNo' => $billNo,
            'billDate' => $billDate,
            'company' => $company,
            'allLcNos' => $allLcNos,
            'allBeNos' => $allBeNos,
            'allBillNos' => $allBillNos,
            'lastBill' => $lastBill,
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

    public function getDependentOptions(Request $request)
    {
        $lcNo = $request->input('lcNo', 'all');
        $beNo = $request->input('be_no', 'all');
        $billNo = $request->input('bill_no', 'all');
        $company = $request->input('company', 'all');

        // Get filtered options based on selections
        $lcNos = ImportBill::select('lc_no')->distinct()
            ->when($beNo !== 'all', fn($q) => $q->where('be_no', $beNo))
            ->when($billNo !== 'all', fn($q) => $q->where('bill_no', $billNo))
            ->when($company !== 'all', fn($q) => $q->where('company_name', $company))
            ->orderBy('lc_no')->pluck('lc_no')->toArray();

        $beNos = ImportBill::select('be_no')->distinct()
            ->when($lcNo !== 'all', fn($q) => $q->where('lc_no', $lcNo))
            ->when($billNo !== 'all', fn($q) => $q->where('bill_no', $billNo))
            ->when($company !== 'all', fn($q) => $q->where('company_name', $company))
            ->orderBy('be_no')->pluck('be_no')->toArray();

        $billNos = ImportBill::select('bill_no')->distinct()
            ->when($lcNo !== 'all', fn($q) => $q->where('lc_no', $lcNo))
            ->when($beNo !== 'all', fn($q) => $q->where('be_no', $beNo))
            ->when($company !== 'all', fn($q) => $q->where('company_name', $company))
            ->orderBy('bill_no')->pluck('bill_no')->toArray();

        return response()->json([
            'lcNos' => $lcNos,
            'beNos' => $beNos,
            'billNos' => $billNos,
        ]);
    }
}
