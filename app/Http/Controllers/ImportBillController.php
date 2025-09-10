<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ImportBill;
use App\Models\ImportBillExpense;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

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
            $query = ImportBill::withSum('expenses', 'amount') // adds expenses_sum_amount
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
                        ? \Carbon\Carbon::parse($row->bill_date)->format('F')
                        : '';
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
        return view('import_bills.create', ['expenseTypes' => $this->expenseTypes]);
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
            'company_name' => 'required|string|max:255',
            'lc_no'        => 'required|string|max:255',
            'lc_date'      => 'nullable|date',
            'bill_no'      => 'required|string|max:255',
            'bill_date'    => 'nullable|date',
            'value'        => 'required|numeric|min:0.01',
        ]);

        $bill = ImportBill::create($request->only([
            'company_name','lc_no','lc_date','bill_no','bill_date',
            'item','value','qty','weight','be_no','be_date',
            'scan_fee','doc_fee'
        ]));

        $expenses = (array)$request->input('expenses', []);
        foreach ($this->expenseTypes as $type) {
            $amount = isset($expenses[$type]) ? floatval($expenses[$type]) : 0;
            ImportBillExpense::create([
                'import_bill_id' => $bill->id,
                'expense_type'   => $type,
                'amount'         => $amount,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import Bill created successfully',
            'bill_id' => $bill->id
        ], 201);
    }

    // edit form
    public function edit($id)
    {
        $bill = ImportBill::with('expenses')->findOrFail($id);
        return view('import_bills.edit', [
            'bill' => $bill,
            'expenseTypes' => $this->expenseTypes
        ]);
    }

    // update existing bill
    public function update(Request $request, $id)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'lc_no'        => 'required|string|max:255',
            'lc_date'      => 'nullable|date',
            'bill_no'      => 'required|string|max:255',
            'bill_date'    => 'nullable|date',
            'value'        => 'required|numeric|min:0.01',
        ]);

        $bill = ImportBill::findOrFail($id);
        $bill->update($request->only([
            'company_name','lc_no','lc_date','bill_no','bill_date',
            'item','value','qty','weight','be_no','be_date',
            'scan_fee','doc_fee'
        ]));

        $expenses = (array)$request->input('expenses', []);
        // update existing expense rows (or create if missing)
        foreach ($this->expenseTypes as $type) {
            $amount = isset($expenses[$type]) ? floatval($expenses[$type]) : 0;
            $expRow = $bill->expenses()->where('expense_type', $type)->first();
            if ($expRow) {
                $expRow->update(['amount' => $amount]);
            } else {
                $bill->expenses()->create(['expense_type' => $type, 'amount' => $amount]);
            }
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

        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully',
        ]);
    }

}
