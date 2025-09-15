<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Account::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('balance', function ($row) {
                    return number_format($row->balance, 2);
                })
                ->addColumn('action', function ($row) {
                    $editId   = $row->id;
                    $deleteId = $row->id;

                    // IMPORTANT: Don't return a <td> here; return only the inner HTML
                    return '
                        <ul class="table-controls text-center">
                            <li>
                                <a href="javascript:void(0);"
                                   class="edit-btn bs-tooltip"
                                   data-id="'.$editId.'"
                                   data-bs-toggle="tooltip"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1">
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash p-1 br-8 mb-1">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounts.index');
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
            'name' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $account = Account::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully!',
            'data'    => $account
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $account = Account::findOrFail($id);
        return response()->json($account);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $account = Account::findOrFail($id);
        $account->update([
            'name' => $request->name,
            'opening_balance' => $request->opening_balance,
            'balance' => $request->opening_balance // reset balance if needed
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully!'
        ]);
    }
}
