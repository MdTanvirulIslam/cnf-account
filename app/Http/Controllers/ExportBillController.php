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
        ]);

        $formToken = $request->input('form_token');
        if (Cache::has("export_bill_token_{$formToken}")) {
            return response()->json(['success' => true, 'message' => 'Duplicate submission ignored']);
        }
        Cache::put("export_bill_token_{$formToken}", true, 3600);

        try {
            DB::beginTransaction();

            // Lock the account to prevent race conditions
            $account = Account::where('id', $request->from_account_id)
                ->lockForUpdate()
                ->first();

            if (!$account) {
                throw new \Exception("Account not found");
            }

            Log::info("Account locked for transaction", [
                'account_id' => $account->id,
                'balance'    => $account->balance
            ]);

            // 1. Create Export Bill
            $bill = ExportBill::create($request->only([
                'buyer_id', 'invoice_no', 'invoice_date', 'bill_no', 'bill_date',
                'usd', 'total_qty', 'ctn_no', 'be_no', 'be_date', 'qty_pcs', 'from_account_id'
            ]));

            // 2. Save all expenses
            foreach (isset($request->expenses) ? $request->expenses : [] as $type => $amount) {
                if ($amount > 0) {
                    ExportBillExpense::create([
                        'export_bill_id' => $bill->id,
                        'expense_type'   => $type,
                        'amount'         => (float) $amount,
                    ]);
                }
            }

            // 3. Process bank expense - ONLY create BankBook entry (balance adjustment is handled by BankBook model)
            $bankExpense = (float) $request->input("expenses.Bank C & F Vat & Others (As Per Receipt)", 0);

            if ($bankExpense > 0) {
                $bankBook = BankBook::create([
                    'account_id' => $request->from_account_id,
                    'type'       => 'Pay Order',
                    'amount'     => $bankExpense,
                    'note'       => 'Bank C & F Vat & Others (As Per Receipt) for Export Bill #' . $bill->id,
                ]);

                Log::info("Bank expense processed via BankBook", [
                    'account_id'    => $request->from_account_id,
                    'bank_expense'  => $bankExpense,
                    'bankbook_id'   => $bankBook->id,
                    'export_bill_id'=> $bill->id
                ]);
            }

            DB::commit();

            // Final verification
            $finalAccount = Account::find($request->from_account_id);
            Log::info("Transaction completed successfully", [
                'account_id'     => $request->from_account_id,
                'final_balance'  => $finalAccount->balance,
                'export_bill_id' => $bill->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Export bill creation failed: " . $e->getMessage(), [
                'account_id' => $request->from_account_id,
                'error'      => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing request: ' . $e->getMessage()
            ], 500);
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
        $bankExpenseType = 'Bank C & F Vat & Others (As Per Receipt)';

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
            'expenses'        => 'nullable|array',
            'expenses.*'      => 'nullable|numeric|min:0',
        ]);

        // prevent duplicate submission
        $formToken = $request->input('form_token');
        if ($formToken && Cache::has("export_bill_token_{$formToken}")) {
            return response()->json(['success' => true, 'message' => 'Duplicate submission ignored']);
        }
        if ($formToken) {
            Cache::put("export_bill_token_{$formToken}", true, 3600);
        }

        DB::transaction(function () use ($request, $id, $bankExpenseType) {
            $bill = ExportBill::with('expenses')->findOrFail($id);

            $oldAccountId   = $bill->from_account_id;
            $newAccountId   = (int) $request->input('from_account_id');
            $newBankExpense = (float) ($request->input('expenses')[$bankExpenseType] ?? 0);

            // 1️⃣ Update Export Bill
            $bill->update($request->only([
                'buyer_id','invoice_no','invoice_date','bill_no','bill_date',
                'usd','total_qty','ctn_no','be_no','be_date','qty_pcs','from_account_id'
            ]));

            // 2️⃣ Update/create expenses
            foreach ($request->input('expenses', []) as $type => $amount) {
                $bill->expenses()->updateOrCreate(
                    ['expense_type' => $type],
                    ['amount' => (float) $amount]
                );
            }

            // 3️⃣ Sync with BankBook (always Pay Order)
            $bankBook = BankBook::where('note', 'like', "%Export Bill #{$bill->id}%")
                ->where('type', 'Pay Order')
                ->first();

            if ($oldAccountId == $newAccountId) {
                // same account → just update BankBook if exists
                if ($bankBook) {
                    $bankBook->update([
                        'account_id' => $newAccountId,
                        'amount'     => $newBankExpense,
                        'note'       => "Export Bill #{$bill->id} — {$bankExpenseType}",
                    ]);
                } else {
                    if ($newBankExpense > 0) {
                        BankBook::create([
                            'account_id' => $newAccountId,
                            'type'       => 'Pay Order',
                            'amount'     => $newBankExpense,
                            'note'       => "Export Bill #{$bill->id} — {$bankExpenseType}",
                        ]);
                    }
                }
            } else {
                // account changed → delete old entry and create new one
                if ($bankBook) {
                    $bankBook->delete();
                }
                if ($newBankExpense > 0) {
                    BankBook::create([
                        'account_id' => $newAccountId,
                        'type'       => 'Pay Order',
                        'amount'     => $newBankExpense,
                        'note'       => "Export Bill #{$bill->id} — {$bankExpenseType}",
                    ]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Export Bill updated successfully']);
    }





    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $bill = ExportBill::with('expenses')->find($id);
            if(!$bill){
                return response()->json(['success'=>false,'message'=>'Bill not found'],404);
            }

            // reverse balance if Bank Vat exists
            $bankVatAmount = $bill->expenses
                    ->where('expense_type', 'Bank C & F Vat & Others (As Per Recipt)')
                    ->first()->amount ?? 0;

            if($bankVatAmount > 0 && $bill->from_account_id){
                $acc = Account::lockForUpdate()->find($bill->from_account_id);
                if($acc){
                    $acc->balance += $bankVatAmount;
                    $acc->save();
                    Cache::forget("account_balance_{$acc->id}");
                    Cache::put("account_balance_{$acc->id}", $acc->balance, 3600);
                }
            }

            $bill->delete();
            DB::commit();

            return response()->json(['success'=>true,'message'=>'Export Bill deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Export Bill Delete Failed', ['error'=>$e->getMessage()]);
            return response()->json(['success'=>false,'message'=>'Something went wrong'],500);
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
