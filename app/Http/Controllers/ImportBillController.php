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
        "Agent Bill (As Per Receipt)",
        "Labour Bill (As Per Rcpt)",
        "Depot Bill (As Per Rcpt)",
        "Bank Guarantee Bank Verify (As Per Rcpt)",
        "Extra Expenses For NOCOM",
        "Custom Air",
        "Cover Van Labour",
        "Nilmark",
        "Breaking Permission",
        "Assistance Commissioner With Pion (Random Selection)",
        "Extra Expenses For Marine Policy",
        "Documents Expenses",
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
                ->latest();

            return datatables()->of($query)
                ->addIndexColumn()
                ->editColumn('lc_date', function ($row) {
                    return $row->lc_date
                        ? \Carbon\Carbon::parse($row->lc_date)->format('Y-m-d')
                        : '';
                })
                ->editColumn('be_date', function ($row) {
                    return $row->be_date
                        ? \Carbon\Carbon::parse($row->be_date)->format('Y-m-d')
                        : '';
                })
                ->editColumn('bill_date', function ($row) {
                    return $row->bill_date
                        ? \Carbon\Carbon::parse($row->bill_date)->format('Y-m-d')
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
                        ? \Carbon\Carbon::parse($row->created_at)->format('F')
                        : '';
                })
                ->addColumn('ait_amount', function ($row) {
                    return number_format(isset($row->ait_sum_amount) ? $row->ait_sum_amount : 0, 2);
                })
                ->addColumn('action', function ($row) {
                    $editUrl   = route('import-bills.edit', $row->id);
                    $deleteUrl = route('import-bills.destroy', $row->id);

                    return '
                    <ul class="table-controls text-center">
                        <li>
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
            'lc_no'           => 'required|string|max:255',
            'lc_date'         => 'nullable|date',
            'bill_no'         => 'required|string|max:255',
            'bill_date'       => 'nullable|date',
            'value'           => 'required|numeric|min:0.01',
            'ait_account_id'  => 'nullable|exists:accounts,id',
            'port_account_id' => 'nullable|exists:accounts,id',
            'expenses'        => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            // 1️⃣ Create ImportBill
            $bill = ImportBill::create($request->only([
                'lc_no','lc_date','bill_no','bill_date',
                'item','value','qty','weight','be_no','be_date',
                'scan_fee','doc_fee'
            ]));

            // 2️⃣ Create ImportBillExpenses
            $expenses = (array)$request->input('expenses', []);
            foreach ($this->expenseTypes as $type) {
                $amount = isset($expenses[$type]) ? floatval($expenses[$type]) : 0;
                ImportBillExpense::create([
                    'import_bill_id' => $bill->id,
                    'expense_type'   => $type,
                    'amount'         => $amount,
                ]);
            }

            // 3️⃣ Create BankBook entries **without adjusting account**
            // AIT (Sonali Bank)
            $aitType = 'AIT (As Per Receipt)';
            $aitAmount = floatval($expenses[$aitType] ?? 0);
            $aitAccountId = $request->input('ait_account_id') ?: Account::where('name', 'like', '%Sonali%')->value('id');

            if ($aitAmount > 0) {
                if (!$aitAccountId) {
                    throw ValidationException::withMessages([
                        'ait_account_id' => 'AIT amount provided but Sonali Bank account not found.'
                    ]);
                }

                BankBook::create([
                    'account_id'     => $aitAccountId,
                    'type'           => 'Pay Order',
                    'amount'         => $aitAmount,
                    'note'           => "Import Bill #{$bill->id} — {$aitType}",

                ]);
            }

            // Port Bill (Janata Bank)
            $portType = 'Port Bill (As Per Receipt)';
            $portAmount = floatval($expenses[$portType] ?? 0);
            $portAccountId = $request->input('port_account_id') ?: Account::where('name', 'like', '%Janata%')->value('id');

            if ($portAmount > 0) {
                if (!$portAccountId) {
                    throw ValidationException::withMessages([
                        'port_account_id' => 'Port Bill amount provided but Janata Bank account not found.'
                    ]);
                }

                BankBook::create([
                    'account_id'     => $portAccountId,
                    'type'           => 'Pay Order',
                    'amount'         => $portAmount,
                    'note'           => "Import Bill #{$bill->id} — {$portType}",

                ]);
            }

            \Log::info('Import Bill created', ['bill_id' => $bill->id, 'user_id' => auth()->id()]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Import Bill created successfully',
        ], 201);
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
            'lc_no'        => 'required|string|max:255',
            'lc_date'      => 'nullable|date',
            'bill_no'      => 'required|string|max:255',
            'bill_date'    => 'nullable|date',
            'value'        => 'required|numeric|min:0.01',
            'ait_account_id'  => 'nullable|exists:accounts,id',
            'port_account_id' => 'nullable|exists:accounts,id',
            'expenses'     => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $id) {
            $bill = ImportBill::with('expenses')->findOrFail($id);

            // Update basic fields
            $bill->update($request->only([
                'lc_no','lc_date','bill_no','bill_date',
                'item','value','qty','weight','be_no','be_date',
                'scan_fee','doc_fee'
            ]));

            // Update/create expenses
            $expenses = (array)$request->input('expenses', []);
            foreach ($this->expenseTypes as $type) {
                $amount = floatval($expenses[$type] ?? 0);
                $expRow = $bill->expenses()->where('expense_type', $type)->first();
                if ($expRow) {
                    $expRow->update(['amount' => $amount]);
                } else {
                    $bill->expenses()->create(['expense_type' => $type, 'amount' => $amount]);
                }
            }

            // ---- Sync AIT BankBook ----
            $aitType = 'AIT (As Per Receipt)';
            $aitAmount = floatval($expenses[$aitType] ?? 0);
            $aitAccountId = $request->input('ait_account_id')
                ?: Account::where('name', 'like', '%Sonali%')->value('id');

            $oldAitBankBook = BankBook::where('type', 'Pay Order')
                ->where('note', 'like', "%Import Bill #{$bill->id}%{$aitType}%")
                ->first();
            if ($oldAitBankBook) {
                $oldAitBankBook->delete(); // reverses old balance
            }
            if ($aitAmount > 0) {
                BankBook::create([
                    'account_id'     => $aitAccountId,
                    'type'           => 'Pay Order',
                    'amount'         => $aitAmount,
                    'note'           => "Import Bill #{$bill->id} — {$aitType}",
                    'adjust_balance' => true, // deducts balance
                ]);
            }

            // ---- Sync Port Bill BankBook ----
            $portType = 'Port Bill (As Per Receipt)';
            $portAmount = floatval($expenses[$portType] ?? 0);
            $portAccountId = $request->input('port_account_id')
                ?: Account::where('name', 'like', '%Janata%')->value('id');

            $oldPortBankBook = BankBook::where('type', 'Pay Order')
                ->where('note', 'like', "%Import Bill #{$bill->id}%{$portType}%")
                ->first();
            if ($oldPortBankBook) {
                $oldPortBankBook->delete(); // reverses old balance
            }
            if ($portAmount > 0) {
                BankBook::create([
                    'account_id'     => $portAccountId,
                    'type'           => 'Pay Order',
                    'amount'         => $portAmount,
                    'note'           => "Import Bill #{$bill->id} — {$portType}",
                    'adjust_balance' => true, // deducts balance
                ]);
            }

            \Log::info('Import Bill Updated', ['bill_id' => $bill->id, 'user_id' => auth()->id()]);
        });

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
            // delete all related Pay Order bankbooks created for this import bill note pattern
            $bankbooks = BankBook::where('type', 'Pay Order')
                ->where('note', 'like', "%Import Bill #{$bill->id}%")
                ->get();

            foreach ($bankbooks as $bk) {
                $bk->delete(); // BankBook deleting handler will restore account balance
            }

            // then delete bill (and expenses)
            $bill->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully',
        ]);
    }


}
