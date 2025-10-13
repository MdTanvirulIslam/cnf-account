<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ExportBill;
use App\Models\BankBook;
use Illuminate\Http\Request;
use App\Models\ExportBillExpense;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Buyer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class ExportBillController extends Controller
{
    private $expenseTypes = [
        "Bank C & F Vat & Others (As Per Receipt)",
        "Labour Bill @ Tk. 3.00 Per Ctns",
        "Landing Bill @ Tk. 207.00 Per Ton",
        "Shorting Bill @ Tk 3.00 Per Ctns",
        "Miscellaneous Expenses for documentation",
        "Automation Document Entry Fee (Refficard) Data Entry",
        "Amendment Purpose Expenses Eid Boxsis",
        "Extra Miscellaneous Exp For (Scale Charge)",
        "Carton damage & Others",
        "Kallan Fund",
        "Scale Charge (As Per Receipt)",
        "Cbm Charge (As Per Receipt)",
        "ADMIN CHARGE",
        "Special permission DSV AIR & SEA LTD",
        "Short Ship Certificate",
        "Weight Permission. Invoice P/list Dc Print",

    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('export_bills.index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = ExportBill::with('buyer') // relation: export_bills.buyer_id → buyers.id
            ->withSum('expenses', 'amount') // total expenses
            ->withSum(['expenses as bank_vat_sum_amount' => function ($q) {
                $q->where('expense_type', 'Bank C & F Vat & Others (As Per Receipt)');
            }], 'amount')
                ->latest();

            return datatables()->of($query)
                ->addIndexColumn()
                ->editColumn('invoice_date', function ($row) {
                    return $row->invoice_date
                        ? \Carbon\Carbon::parse($row->invoice_date)->format('Y-m-d')
                        : '';
                })
                ->editColumn('bill_date', function ($row) {
                    return $row->bill_date
                        ? \Carbon\Carbon::parse($row->bill_date)->format('Y-m-d')
                        : '';
                })
                ->editColumn('be_date', function ($row) {
                    return $row->be_date
                        ? \Carbon\Carbon::parse($row->be_date)->format('Y-m-d')
                        : '';
                })

                ->addColumn('buyer_name', function ($row) {
                    return $row->buyer ? $row->buyer->name : '';
                })
                ->addColumn('amount', function ($row) {
                    return number_format(isset($row->expenses_sum_amount) ? $row->expenses_sum_amount : 0, 2);
                })
                ->addColumn('bank_vat_amount', function ($row) {
                    return number_format(isset($row->bank_vat_sum_amount) ? $row->bank_vat_sum_amount : 0, 2);
                })
                ->addColumn('action', function ($row) {
                    $editUrl   = route('export-bills.edit', $row->id);
                    $deleteUrl = route('export-bills.destroy', $row->id);
                    $viewUrl   = route('export-bills.print', $row->id);

                    return '
                <ul class="table-controls text-center">
                    <li>
                        <a href="'.$viewUrl.'" target="_blank"
                           class="view-btn bs-tooltip text-primary"
                           title="View / Print"
                           data-bs-toggle="tooltip">
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
                           class="edit-btn bs-tooltip"
                           title="Edit"
                           data-id="'.$row->id.'" data-bs-toggle="tooltip">
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
                           data-bs-toggle="tooltip"
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

        return view('export_bills.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts  = Account::select('id','name','balance')->get();
        $buyers    = Buyer::select('id','name')->get();

        // generate a unique token for the form to prevent processing duplicates
        $formToken = (string) Str::uuid();

        return view('export_bills.create', [
            'expenseTypes' => $this->expenseTypes,
            'buyers'       => $buyers,
            'accounts'     => $accounts,
            'formToken'    => $formToken,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $request->validate([
            'buyer_id'        => 'required|exists:buyers,id',
            'invoice_no'      => 'required|string|max:255',
            'invoice_date'    => 'nullable|date',
            'bill_no'         => 'required|string|max:255',
            'bill_date'       => 'nullable|date',
            'usd'             => 'required|numeric',
            'total_qty'       => 'required|integer',
            'ctn_no'          => 'nullable|string|max:255',
            'be_no'           => 'nullable|string|max:255',
            'be_date'         => 'nullable|date',
            'qty_pcs'         => 'required|integer',
            'from_account_id' => 'required|exists:accounts,id',
            'account_id'      => 'required|exists:accounts,id',
            'expenses'        => 'nullable|array',
            'expenses.*'      => 'nullable|numeric|min:0',
        ]);

        $formToken = $request->input('form_token');
        if ($formToken && Cache::has("export_bill_token_{$formToken}")) {
            return response()->json(['success' => true, 'message' => 'Duplicate submission ignored']);
        }
        if ($formToken) {
            Cache::put("export_bill_token_{$formToken}", true, 3600);
        }

        try {
            DB::beginTransaction();

            $vatAccount = Account::where('id', $request->from_account_id)->lockForUpdate()->first();
            $mainAccount = Account::where('id', $request->account_id)->lockForUpdate()->first();

            if (!$vatAccount || !$mainAccount) {
                throw new \Exception("Account not found");
            }

            // 1️⃣ Create Export Bill
            $bill = ExportBill::create($request->only([
                'buyer_id','invoice_no','invoice_date','bill_no','bill_date',
                'usd','total_qty','ctn_no','be_no','be_date','qty_pcs','from_account_id','account_id'
            ]));

            $vatType = 'Bank C & F Vat & Others (As Per Receipt)';
            $vatAmount = (float) ($request->input('expenses')[$vatType] ?? 0);

            // 2️⃣ Save all expenses individually and calculate other amount correctly
            $otherAmount = 0;
            foreach ($request->input('expenses', []) as $type => $amount) {
                $amount = (float) $amount;
                if ($amount > 0) {
                    ExportBillExpense::create([
                        'export_bill_id' => $bill->id,
                        'expense_type'   => $type,
                        'amount'         => $amount,
                    ]);

                    // Calculate other amount (all expenses except VAT type)
                    if ($type !== $vatType) {
                        $otherAmount += $amount;
                    }
                }
            }

            // 3️⃣ Create BankBook entries
            if ($vatAmount > 0) {
                BankBook::create([
                    'export_bill_id' => $bill->id,
                    'account_id' => $vatAccount->id,
                    'type'       => 'Pay Order',
                    'amount'     => $vatAmount,
                    'note'       => "{$vatType} for Export Bill #{$bill->id}",
                ]);
            }

            if ($otherAmount > 0) {
                BankBook::create([
                    'export_bill_id' => $bill->id,
                    'account_id' => $mainAccount->id,
                    'type'       => 'Export Bill',
                    'amount'     => $otherAmount,
                    'note'       => "{$otherAmount} Amount deduct for export bill #{$bill->id}",
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Export bill creation failed: " . $e->getMessage(), [
                'request' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Export Bill created successfully']);
    }



    /**
     * Display the specified resource.
     */
    public function show(ExportBill $exportBill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $bill    = ExportBill::with('expenses')->findOrFail($id);
        $buyers  = Buyer::select('id','name')->get();
        $accounts = Account::select('id','name','balance')->get();

        $expenses = $bill->expenses->pluck('amount', 'expense_type')->toArray();

        return view('export_bills.edit', [
            'bill'         => $bill,
            'buyers'       => $buyers,
            'accounts'     => $accounts,
            'expenseTypes' => $this->expenseTypes,
            'expenses'     => $expenses,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'buyer_id'        => 'required|exists:buyers,id',
            'invoice_no'      => 'required|string|max:255',
            'invoice_date'    => 'nullable|date',
            'bill_no'         => 'required|string|max:255',
            'bill_date'       => 'nullable|date',
            'usd'             => 'required|numeric',
            'total_qty'       => 'required|integer',
            'ctn_no'          => 'nullable|string|max:255',
            'be_no'           => 'nullable|string|max:255',
            'be_date'         => 'nullable|date',
            'qty_pcs'         => 'required|integer',
            'from_account_id' => 'required|exists:accounts,id',
            'account_id'      => 'required|exists:accounts,id',
            'expenses'        => 'nullable|array',
            'expenses.*'      => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Find the existing bill with expenses
            $bill = ExportBill::with('expenses')->findOrFail($id);

            // Update the bill
            $bill->update($request->only([
                'buyer_id','invoice_no','invoice_date','bill_no','bill_date',
                'usd','total_qty','ctn_no','be_no','be_date','qty_pcs','from_account_id','account_id'
            ]));

            $vatType = 'Bank C & F Vat & Others (As Per Receipt)';
            $vatAmount = (float) ($request->input('expenses')[$vatType] ?? 0);
            $totalExpenses = array_sum(array_map('floatval', $request->input('expenses', [])));
            $otherAmount = max($totalExpenses - $vatAmount, 0);

            // Delete all existing expenses and create new ones (consistent with store method)
            $bill->expenses()->delete();

            foreach ($request->input('expenses', []) as $type => $amount) {
                if ($amount > 0) {
                    ExportBillExpense::create([
                        'export_bill_id' => $bill->id,
                        'expense_type'   => $type,
                        'amount'         => (float) $amount,
                    ]);
                }
            }

            // Find existing BankBook entries
            $vatBankBook = BankBook::where('note', 'like', "%Export Bill #{$bill->id}%")
                ->where('type', 'Pay Order')
                ->where('note', 'like', "%{$vatType}%")
                ->first();

            $otherBankBook = BankBook::where('note', 'like', "%Export Bill #{$bill->id}%")
                ->where('type', 'Export Bill')
                ->where('note', 'like', "%Amount deduct for export bill #{$bill->id}%")
                ->first();

            // Handle VAT BankBook entry
            if ($vatAmount > 0) {
                if ($vatBankBook) {
                    // Update existing entry - BankBook model events will handle balance adjustment
                    $vatBankBook->update([
                        'account_id' => $bill->from_account_id,
                        'amount'     => $vatAmount,
                        'note'       => "{$vatType} for Export Bill #{$bill->id}",
                    ]);
                } else {
                    // Create new entry - BankBook model events will handle balance adjustment
                    BankBook::create([
                        'account_id' => $bill->from_account_id,
                        'type'       => 'Pay Order',
                        'amount'     => $vatAmount,
                        'note'       => "{$vatType} for Export Bill #{$bill->id}",
                    ]);
                }
            } elseif ($vatBankBook) {
                // Delete if amount is 0 but entry exists - BankBook model events will handle balance adjustment
                $vatBankBook->delete();
            }

            // Handle Other Amount BankBook entry
            if ($otherAmount > 0) {
                if ($otherBankBook) {
                    // Update existing entry - BankBook model events will handle balance adjustment
                    $otherBankBook->update([
                        'account_id' => $bill->account_id,
                        'type'       => 'Export Bill',
                        'amount'     => $otherAmount,
                        'note'       => "{$otherAmount} Amount deduct for export bill #{$bill->id}",
                    ]);
                } else {
                    // Create new entry - BankBook model events will handle balance adjustment
                    BankBook::create([
                        'account_id' => $bill->account_id,
                        'type'       => 'Export Bill',
                        'amount'     => $otherAmount,
                        'note'       => "{$otherAmount} Amount deduct for export bill #{$bill->id}",
                    ]);
                }
            } elseif ($otherBankBook) {
                // Delete if amount is 0 but entry exists - BankBook model events will handle balance adjustment
                $otherBankBook->delete();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Export bill update failed: " . $e->getMessage(), [
                'request' => $request->all(),
                'bill_id' => $id
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Export Bill updated successfully']);
    }






    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $bill = ExportBill::with(['expenses'])->find($id);

            if (!$bill) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill not found',
                ], 404);
            }

            // Step 1: Fetch related BankBook entries
            $bankBooks = BankBook::where('export_bill_id', $bill->id)->get();

            foreach ($bankBooks as $bk) {
                if ($bk->account) {
                    //  Restore deducted amount back to the account balance
                    $account = Account::lockForUpdate()->find($bk->account->id);
                    if ($account) {
                        //$account->balance += $bk->amount;
                        $account->save();

                        Cache::forget("account_balance_{$account->id}");
                        Cache::put("account_balance_{$account->id}", $account->balance, 3600);
                    }
                }

                // Delete each bank book entry
                $bk->delete();
            }

            // Step 2: Delete related ExportBillExpense entries
            ExportBillExpense::where('export_bill_id', $bill->id)->delete();

            // Step 3: Adjust both from_account and account balances if needed
            if ($bill->from_account_id) {
                $fromAcc = Account::lockForUpdate()->find($bill->from_account_id);
                if ($fromAcc) {
                    Cache::forget("account_balance_{$fromAcc->id}");
                    Cache::put("account_balance_{$fromAcc->id}", $fromAcc->balance, 3600);
                }
            }

            if ($bill->account_id) {
                $mainAcc = Account::lockForUpdate()->find($bill->account_id);
                if ($mainAcc) {
                    Cache::forget("account_balance_{$mainAcc->id}");
                    Cache::put("account_balance_{$mainAcc->id}", $mainAcc->balance, 3600);
                }
            }

            // Step 4: Delete the export bill itself
            $bill->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Export Bill, related bank books, and expenses deleted successfully & balances adjusted',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Export Bill Delete Failed', [
                'error' => $e->getMessage(),
                'bill_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong during deletion: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function print($id)
    {
        $bill = ExportBill::with(['buyer', 'expenses'])->findOrFail($id);

        // Map expenses into array for easier access
        $expenses = $bill->expenses->pluck('amount', 'expense_type')->toArray();

        // Calculate total
        $total = array_sum($expenses);

        return view('export_bills.print', [
            'bill'         => $bill,
            'expenses'     => $expenses,
            'expenseTypes' => $this->expenseTypes,
            'total'        => $total,
        ]);
    }

}
