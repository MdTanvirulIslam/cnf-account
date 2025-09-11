<?php

namespace App\Http\Controllers;

use App\Models\ExportBill;
use Illuminate\Http\Request;
use App\Models\ExportBillExpense;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Buyer;

class ExportBillController extends Controller
{
    private $expenseTypes = [
        "Bank C & F Vat & Others (As Per Recipt)",
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
        "Weight Permission. Invoice P/list Dc Print"
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
                $q->where('expense_type', 'Bank C & F Vat & Others (As Per Recipt)');
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

        return view('export_bills.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buyers = Buyer::select('id','name')->get();
        return view('export_bills.create', [
            'expenseTypes' => $this->expenseTypes,
            'buyers' => $buyers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       //dd($request->all());
        $request->validate([
            'company_name' => 'required|string|max:255',
            'buyer_id' => 'required|exists:buyers,id',
            'invoice_no' => 'required|string|max:255',
            'invoice_date' => 'nullable|date',
            'bill_no' => 'required|string|max:255',
            'bill_date' => 'nullable|date',
            'usd' => 'required|numeric',
            'total_qty' => 'required|integer',
            'ctn_no' => 'nullable|string|max:255',
            'be_no' => 'nullable|string|max:255',
            'be_date' => 'nullable|date',
            'qty_pcs' => 'required|integer',
        ]);

        $bill = ExportBill::create($request->only([
            'company_name','buyer_id','invoice_no','invoice_date','bill_no',
            'bill_date','usd','total_qty','ctn_no','be_no','be_date','qty_pcs'
        ]));

        foreach($this->expenseTypes as $type){
            $amount = $request->input("expenses.$type",0);
            ExportBillExpense::create([
                'export_bill_id'=>$bill->id,
                'expense_type'=>$type,
                'amount'=>$amount
            ]);
        }

        return response()->json(['success'=>true,'message'=>'Export Bill created successfully']);
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
        $bill = ExportBill::with('expenses')->findOrFail($id);
        $buyers = Buyer::select('id','name')->get();

        // Convert expenses collection to keyed array for easier access in Blade
        $expenses = $bill->expenses->pluck('amount', 'expense_type')->toArray();

        return view('export_bills.edit', [
            'bill' => $bill,
            'buyers' => $buyers,
            'expenseTypes' => $this->expenseTypes,
            'expenses' => $expenses, // pass keyed array
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
       $request->validate([
            'company_name' => 'required|string|max:255',
            'buyer_id' => 'required|exists:buyers,id',
            'invoice_no' => 'required|string|max:255',
            'invoice_date' => 'nullable|date',
            'bill_no' => 'required|string|max:255',
            'bill_date' => 'nullable|date',
            'usd' => 'required|numeric',
            'total_qty' => 'required|integer',
            'ctn_no' => 'nullable|string|max:255',
            'be_no' => 'nullable|string|max:255',
            'be_date' => 'nullable|date',
            'qty_pcs' => 'required|integer',
        ]);
        $bill = ExportBill::findOrFail($id);
        // Update header fields
        $bill->update($request->only([
            'company_name','buyer_id','bill_no','bill_date','invoice_no','invoice_date','usd','total_qty','ctn_no','be_no','be_date','qty_pcs'
        ]));

        // Update expenses
        foreach ($request->expenses as $type => $amount) {
            $expense = $bill->expenses()->firstOrNew(['expense_type' => $type]);
            $expense->amount = $amount ?: 0;
            $expense->save();
        }

        return response()->json(['message' => 'Export bill updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $bill = ExportBill::find($id);
        if(!$bill){
            return response()->json(['success'=>false,'message'=>'Bill not found'],404);
        }
        $bill->delete();
        return response()->json(['success'=>true,'message'=>'Export Bill deleted successfully']);
    }
}
