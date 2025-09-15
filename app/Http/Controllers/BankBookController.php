<?php

namespace App\Http\Controllers;

use App\Models\BankBook;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Account;

class BankBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $accounts = Account::all();

        if ($request->ajax()) {
            // Eager load account relation for performance
            $data = BankBook::with('account')->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('bank_name', function ($row) {
                    return $row->account
                        ? $row->account->name . ' (Balance: ' . number_format($row->account->balance, 2) . ')'
                        : '<span class="text-danger">N/A</span>';
                })
                ->editColumn('type', function ($row) {
                    if ($row->type === 'Receive') {
                        return '<span class="badge badge-light-secondary">Receive</span>';
                    } elseif ($row->type === 'Withdraw') {
                        return '<span class="badge badge-light-success">Withdraw</span>';
                    } else {
                        return '<span class="badge badge-light-warning">Pay Order</span>';
                    }
                })
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->addColumn('action', function ($row) {
                    $editId   = $row->id;
                    $deleteId = $row->id;

                    return '
                    <ul class="table-controls text-center">
                        <li>
                            <a href="javascript:void(0);"
                               class="edit-btn bs-tooltip"
                               data-id="'.$editId.'"
                               data-bs-toggle="tooltip"
                               title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 30 30" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-edit-2 p-1 br-8 mb-1">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);"
                               class="delete-btn bs-tooltip text-danger"
                               data-id="'.$deleteId.'"
                               data-bs-toggle="tooltip"
                               title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 30 30" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-trash p-1 br-8 mb-1">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4
                                             a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </a>
                        </li>
                    </ul>
                ';
                })
                ->rawColumns(['bank_name','type','action'])
                ->make(true);
        }

        return view('bankbooks.index', compact('accounts'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type'       => 'required|in:Receive,Withdraw,Pay Order',
            'amount'     => 'required|numeric|min:1',
            'note'       => 'nullable|string'
        ]);

        try {
            $bankBook = \App\Models\BankBook::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'BankBook created successfully!',
                'data' => $bankBook
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $bankBook = BankBook::findOrFail($id);

        return response()->json([
            'id'        => $bankBook->id,
            'account_id' => $bankBook->account_id,
            'type'      => $bankBook->type,
            'amount'    => $bankBook->amount,
            'note'      => $bankBook->note,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type'       => 'required|in:Receive,Withdraw,Pay Order',
            'amount'     => 'required|numeric|min:1',
            'note'       => 'nullable|string'
        ]);

        try {
            $bankBook = \App\Models\BankBook::findOrFail($id);
            $bankBook->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'BankBook updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $bankBook = BankBook::findOrFail($id);
            $bankBook->delete();

            return response()->json([
                'success' => true,
                'message' => 'BankBook deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
}
