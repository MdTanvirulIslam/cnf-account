<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ImportBill;
use App\Models\ImportBillExpense;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\BankBook;
use Illuminate\Validation\ValidationException;


class ImportBillController extends Controller
{
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
        "Bank Guarantee Bank Verify (As Per Rcpt)",
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

    // list view
    public function index()
    {
        return view('import_bills.index', ['expenseTypes' => $this->expenseTypes]);
    }

    // DataTables AJAX endpoint
    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = ImportBill::withSum('expenses', 'amount') // total expenses
            ->withSum(['expenses as ait_sum_amount' => function ($q) {
                $q->where('expense_type', 'AIT (As Per Receipt)'); // only AIT expenses
            }], 'amount')
                ->withSum(['expenses as port_bill_sum_amount' => function ($q) {
                    $q->where('expense_type', 'Port Bill (As Per Receipt)'); // only Port Bill expenses
                }], 'amount')
                ->latest();

            // Apply Company Filter
            if ($request->has('company_name') && !empty($request->company_name)) {
                $query->where('company_name', $request->company_name);
            }

            // Apply Month Filter
            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('bill_date', $request->month);
            }

            // Apply Year Filter
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('bill_date', $request->year);
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('company_name', function($row) {
                    return $row->company_name ?? 'N/A';
                })
                ->editColumn('lc_date', function ($row) {
                    return $row->lc_date
                        ? \Carbon\Carbon::parse($row->lc_date)->format('d-m-Y')
                        : '';
                })
                ->editColumn('be_date', function ($row) {
                    return $row->be_date
                        ? \Carbon\Carbon::parse($row->be_date)->format('d-m-Y')
                        : '';
                })
                ->editColumn('bill_date', function ($row) {
                    return $row->bill_date
                        ? \Carbon\Carbon::parse($row->bill_date)->format('d-m-Y')
                        : '';
                })
                ->editColumn('value', function ($row) {
                    return number_format($row->value, 2);
                })
                ->addColumn('amount', function ($row) {
                    return number_format(isset($row->expenses_sum_amount) ? $row->expenses_sum_amount : 0, 2);
                })
                ->addColumn('month_name', function ($row) {
                    return $row->bill_date
                        ? \Carbon\Carbon::parse($row->bill_date)->format('F')
                        : '';
                })
                ->addColumn('ait_amount', function ($row) {
                    return number_format(isset($row->ait_sum_amount) ? $row->ait_sum_amount : 0, 2);
                })
                ->addColumn('port_bill_amount', function ($row) {
                    return number_format(isset($row->port_bill_sum_amount) ? $row->port_bill_sum_amount : 0, 2);
                })
                ->addColumn('action', function ($row) {
                    $editUrl   = route('import-bills.edit', $row->id);
                    $deleteUrl = route('import-bills.destroy', $row->id);
                    $viewUrl   = route('import-bills.print', $row->id);

                    return '
                <ul class="table-controls text-center">
                    <li>
                        <a href="'.$viewUrl.'" target="_blank"
                           class="bs-tooltip text-success"
                           title="View / Print"
                           data-id="'.$row->id.'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 viewBox="0 0 30 30" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-printer p-1 br-8 mb-1">
                                <path d="M6 9V2h18v7"></path>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-6a2 2 0 0 1
                                         2-2h22a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="18" height="10"></rect>
                            </svg>

                        </a>
                        <a href="'.$editUrl.'"
                           class="bs-tooltip"
                           title="Edit"
                           data-id="'.$row->id.'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 viewBox="0 0 30 30" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-edit-2 p-1 br-8 mb-1">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5
                                20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);"
                           class="delete-btn bs-tooltip text-danger"
                           title="Delete"
                           data-route="'.$deleteUrl.'"
                           data-id="'.$row->id.'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 viewBox="0 0 30 30" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-trash p-1 br-8 mb-1">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0
                                1-2-2V6m3 0V4a2 2 0 0 1
                                2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
            ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('import_bills.index');
    }

    // create form
    public function create()
    {
        // fetch accounts for the selects
        $accounts = Account::orderBy('name')->get();

        // try to auto-find best defaults by bank name (Sonali / Janata)
        $defaultAitAccountId = Account::where('name', 'like', '%Sonali%')->value('id');
        $defaultPortAccountId = Account::where('name', 'like', '%Janata%')->value('id');

        return view('import_bills.create', [
            'expenseTypes' => $this->expenseTypes,
            'accounts' => $accounts,
            'defaultAitAccountId' => $defaultAitAccountId,
            'defaultPortAccountId' => $defaultPortAccountId,
        ]);
    }

    public function show($id)
    {
        $bill = ImportBill::with('expenses')->find($id);

        if (!$bill) {
            return response()->json(['message' => 'Bill not found'], 404);
        }

        return response()->json($bill);
    }

    // store
    public function store(Request $request)
    {
        $request->validate([
            'company_name'    => 'required',
            'lc_no'           => 'required|string|max:255',
            'lc_date'         => 'nullable|date',
            'bill_no'         => 'required|string|max:255|unique:import_bills,bill_no',
            'be_no'           => 'required|string|max:255|unique:import_bills,be_no',
            'bill_date'       => 'nullable|date',
            'value'           => 'required|numeric|min:0.01',
            'account_id'      => 'required|exists:accounts,id',
            'ait_account_id'  => 'nullable|exists:accounts,id',
            'port_account_id' => 'nullable|exists:accounts,id',
            'expenses'        => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Get accounts with lock
            $mainAccount = Account::where('id', $request->account_id)->lockForUpdate()->first();
            $aitAccount = $request->ait_account_id ? Account::where('id', $request->ait_account_id)->lockForUpdate()->first() : null;
            $portAccount = $request->port_account_id ? Account::where('id', $request->port_account_id)->lockForUpdate()->first() : null;

            if (!$mainAccount) {
                throw new \Exception("Main account not found");
            }

            // Get company name from request
            $companyName = $request->company_name;

            // Add prefixes to the fields with company name
            $bill_no = $this->addBillNoPrefix(trim($request->bill_no), $companyName);
            $be_no = $this->addBeNoPrefix(trim($request->be_no ?? ''));

            // 1️⃣ Create ImportBill with prefixed values
            $bill = ImportBill::create([
                'company_name'    => $companyName,
                'lc_no'           => trim($request->lc_no),
                'lc_date'         => $request->lc_date,
                'bill_no'         => $bill_no,
                'bill_date'       => $request->bill_date,
                'item'            => $request->item,
                'value'           => $request->value,
                'qty'             => $request->qty,
                'weight'          => $request->weight,
                'be_no'           => $be_no,
                'be_date'         => $request->be_date,
                'scan_fee'        => $request->scan_fee,
                'doc_fee'         => $request->doc_fee,
                'account_id'      => $request->account_id,
                'ait_account_id'  => $request->ait_account_id,
                'port_account_id' => $request->port_account_id,
                'itc'             => $request->itc,
            ]);

            $expenses = (array)$request->input('expenses', []);

            // Define special expense types
            $aitType = 'AIT (As Per Receipt)';
            $portType = 'Port Bill (As Per Receipt)';

            $aitAmount = floatval($expenses[$aitType] ?? 0);
            $portAmount = floatval($expenses[$portType] ?? 0);

            // Calculate other amount (all expenses except AIT and Port Bill, plus doc_fee and scan_fee)
            $otherAmount = 0;

            // Add all other expenses
            foreach ($expenses as $type => $amount) {
                if ($type !== $aitType && $type !== $portType) {
                    $otherAmount += floatval($amount);
                }
            }

            // Create ImportBillExpenses
            foreach ($this->expenseTypes as $type) {
                $amount = isset($expenses[$type]) ? floatval($expenses[$type]) : 0;
                if ($amount > 0) {
                    ImportBillExpense::create([
                        'import_bill_id' => $bill->id,
                        'expense_type'   => $type,
                        'amount'         => $amount,
                    ]);
                }
            }

            // Create BankBook entries
            // AIT (Sonali Bank)
            if ($aitAmount > 0) {
                if (!$aitAccount) {
                    throw new \Exception("AIT amount provided but AIT account not found.");
                }

                BankBook::create([
                    'account_id'     => $aitAccount->id,
                    'import_bill_id' => $bill->id,
                    'type'           => 'Pay Order',
                    'amount'         => $aitAmount,
                    'note'           => "Import Bill #{$bill->id} — {$aitType}",
                ]);
            }

            // Port Bill (Janata Bank)
            if ($portAmount > 0) {
                if (!$portAccount) {
                    throw new \Exception("Port Bill amount provided but Port account not found.");
                }

                BankBook::create([
                    'account_id'     => $portAccount->id,
                    'import_bill_id' => $bill->id,
                    'type'           => 'Pay Order',
                    'amount'         => $portAmount,
                    'note'           => "Import Bill #{$bill->id} — {$portType}",
                ]);
            }

            // Other Amount (Dhaka Bank)
            if ($otherAmount > 0) {
                BankBook::create([
                    'account_id'     => $mainAccount->id,
                    'import_bill_id' => $bill->id,
                    'type'           => 'Import Bill',
                    'amount'         => $otherAmount,
                    'note'           => "{$otherAmount} Amount deduct for import bill #{$bill->id}",
                ]);
            }

            DB::commit();
            \Log::info('Import Bill created', ['bill_id' => $bill->id, 'user_id' => auth()->id()]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Import bill creation failed: " . $e->getMessage(), [
                'request' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import Bill created successfully',
        ], 201);
    }

    // Helper methods for adding prefixes dynamically based on company
    private function addBillNoPrefix($billNo, $companyName)
    {
        $prefixes = [
            'MULTI FABS LTD' => 'MFL/IMP/',
            'EMS APPARELS LTD' => 'EMS/IMP/'
        ];

        $prefix = $prefixes[$companyName] ?? 'MFL/IMP/'; // Default to MFL if not found

        // Remove existing prefix to avoid duplication
        foreach ($prefixes as $companyPrefix) {
            if (strpos($billNo, $companyPrefix) === 0) {
                $billNo = str_replace($companyPrefix, '', $billNo);
                break;
            }
        }

        return $prefix . trim($billNo);
    }

    private function addBeNoPrefix($beNo)
    {
        if (empty($beNo)) {
            return null;
        }
        $prefix = 'C-';
        // Remove prefix if already exists to avoid duplication
        $beNo = str_replace($prefix, '', $beNo);
        return $prefix . trim($beNo);
    }

    // edit form
    public function edit($id)
    {
        $bill = ImportBill::with('expenses')->findOrFail($id);
        $expenseTypes = $this->expenseTypes;
        $accounts = Account::all(); // load bank accounts

        return view('import_bills.edit', compact('bill','expenseTypes','accounts'));
    }


    // update existing bill
    public function update(Request $request, $id)
    {
        $request->validate([
            'company_name'    => 'required',
            'lc_no'           => 'required|string|max:255',
            'lc_date'         => 'nullable|date',
            'bill_no'         => 'required|string|max:255',
            'bill_date'       => 'nullable|date',
            'value'           => 'required|numeric|min:0.01',
            'account_id'      => 'required|exists:accounts,id',
            'ait_account_id'  => 'nullable|exists:accounts,id',
            'port_account_id' => 'nullable|exists:accounts,id',
            'expenses'        => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $bill = ImportBill::with('expenses')->findOrFail($id);

            // Get company name from request
            $companyName = $request->company_name;

            // Add prefixes to the fields with company name
            $bill_no = $this->addBillNoPrefix(trim($request->bill_no), $companyName);
            $be_no = $this->addBeNoPrefix(trim($request->be_no ?? ''));

            // Update basic fields with prefixed values
            $bill->update([
                'company_name'    => $companyName,
                'lc_no'           => trim($request->lc_no),
                'lc_date'         => $request->lc_date,
                'bill_no'         => $bill_no,
                'bill_date'       => $request->bill_date,
                'item'            => $request->item,
                'value'           => $request->value,
                'qty'             => $request->qty,
                'weight'          => $request->weight,
                'be_no'           => $be_no,
                'be_date'         => $request->be_date,
                'scan_fee'        => $request->scan_fee,
                'doc_fee'         => $request->doc_fee,
                'account_id'      => $request->account_id,
                'ait_account_id'  => $request->ait_account_id,
                'port_account_id' => $request->port_account_id,
                'itc'             => $request->itc,
            ]);

            $expenses = (array)$request->input('expenses', []);

            // Define special expense types
            $aitType = 'AIT (As Per Receipt)';
            $portType = 'Port Bill (As Per Receipt)';

            $aitAmount = floatval($expenses[$aitType] ?? 0);
            $portAmount = floatval($expenses[$portType] ?? 0);

            // Calculate other amount (all expenses except AIT and Port Bill, plus doc_fee and scan_fee)
            $otherAmount = 0;

            // Add all other expenses
            foreach ($expenses as $type => $amount) {
                if ($type !== $aitType && $type !== $portType) {
                    $otherAmount += floatval($amount);
                }
            }

            // Delete and recreate expenses (consistent approach)
            $bill->expenses()->delete();
            foreach ($this->expenseTypes as $type) {
                $amount = isset($expenses[$type]) ? floatval($expenses[$type]) : 0;
                if ($amount > 0) {
                    ImportBillExpense::create([
                        'import_bill_id' => $bill->id,
                        'expense_type'   => $type,
                        'amount'         => $amount,
                    ]);
                }
            }

            // Find existing BankBook entries
            $aitBankBook = BankBook::where('import_bill_id', $bill->id)
                ->where('type', 'Pay Order')
                ->where('note', 'like', "%{$aitType}%")
                ->first();

            $portBankBook = BankBook::where('import_bill_id', $bill->id)
                ->where('type', 'Pay Order')
                ->where('note', 'like', "%{$portType}%")
                ->first();

            $otherBankBook = BankBook::where('import_bill_id', $bill->id)
                ->where('type', 'Import Bill')
                ->where('note', 'like', "%Amount deduct for import bill #{$bill->id}%")
                ->first();

            // Handle AIT BankBook entry
            if ($aitAmount > 0) {
                if ($aitBankBook) {
                    $aitBankBook->update([
                        'account_id' => $bill->ait_account_id,
                        'amount'     => $aitAmount,
                        'note'       => "Import Bill #{$bill->id} — {$aitType}",
                    ]);
                } else {
                    BankBook::create([
                        'account_id'     => $bill->ait_account_id,
                        'import_bill_id' => $bill->id,
                        'type'           => 'Pay Order',
                        'amount'         => $aitAmount,
                        'note'           => "Import Bill #{$bill->id} — {$aitType}",
                    ]);
                }
            } elseif ($aitBankBook) {
                $aitBankBook->delete();
            }

            // Handle Port Bill BankBook entry
            if ($portAmount > 0) {
                if ($portBankBook) {
                    $portBankBook->update([
                        'account_id' => $bill->port_account_id,
                        'amount'     => $portAmount,
                        'note'       => "Import Bill #{$bill->id} — {$portType}",
                    ]);
                } else {
                    BankBook::create([
                        'account_id'     => $bill->port_account_id,
                        'import_bill_id' => $bill->id,
                        'type'           => 'Pay Order',
                        'amount'         => $portAmount,
                        'note'           => "Import Bill #{$bill->id} — {$portType}",
                    ]);
                }
            } elseif ($portBankBook) {
                $portBankBook->delete();
            }

            // Handle Other Amount BankBook entry
            if ($otherAmount > 0) {
                if ($otherBankBook) {
                    $otherBankBook->update([
                        'account_id' => $bill->account_id,
                        'amount'     => $otherAmount,
                        'note'       => "{$otherAmount} Amount deduct for import bill #{$bill->id}",
                    ]);
                } else {
                    BankBook::create([
                        'account_id'     => $bill->account_id,
                        'import_bill_id' => $bill->id,
                        'type'           => 'Import Bill',
                        'amount'         => $otherAmount,
                        'note'           => "{$otherAmount} Amount deduct for import bill #{$bill->id}",
                    ]);
                }
            } elseif ($otherBankBook) {
                $otherBankBook->delete();
            }

            DB::commit();
            \Log::info('Import Bill Updated', ['bill_id' => $bill->id, 'user_id' => auth()->id()]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Import bill update failed: " . $e->getMessage(), [
                'request' => $request->all(),
                'bill_id' => $id
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import Bill updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $bill = ImportBill::find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found',
            ], 404);
        }

        DB::transaction(function () use ($bill) {
            // Step 1: Restore balances & delete bank_books
            $bankbooks = BankBook::where('import_bill_id', $bill->id)->get();

            foreach ($bankbooks as $bk) {
                if ($bk->account) {
                    $bk->account->save();
                }
                $bk->delete();
            }

            // Step 2: Delete related expenses
            ImportBillExpense::where('import_bill_id', $bill->id)->delete();

            // Step 3: Delete the bill
            $bill->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Bill, related bank books, and expenses deleted successfully & balances adjusted',
        ]);
    }

    public function print($id)
    {
        $bill = ImportBill::with('expenses')->findOrFail($id);

        $total = $bill->expenses->sum('amount');

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

        return view('import_bills.print', [
            'bill' => $bill,
            'total' => $total,
            'companyNameForPrint' => $companyNameForPrint,
            'companyAddress' => $companyAddress,
        ]);
    }
}
