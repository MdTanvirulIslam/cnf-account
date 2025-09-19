<?php

namespace App\Http\Controllers;

use App\Models\BankBook;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Account;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BankBookController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::all();

        if ($request->ajax()) {
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
                    } elseif ($row->type === 'Bank Transfer') {
                        return '<span class="badge badge-light-info">Bank Transfer</span>';
                    } else {
                        return '<span class="badge badge-light-warning">Pay Order</span>';
                    }
                })
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->addColumn('action', function ($row) {
                    // Check if note contains restricted keywords
                    $restrictedNotes = [
                        'Bank C & F Vat & Others (As Per Receipt)',
                        'Import Bill',
                        'Export Bill'
                    ];

                    $disableActions = false;
                    foreach ($restrictedNotes as $keyword) {
                        if (str_contains($row->note, $keyword)) {
                            $disableActions = true;
                            break;
                        }
                    }

                    $editBtn = $disableActions
                        ? '<a href="javascript:void(0);" class="edit-btn bs-tooltip disabled" title="Edit Disabled">
                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                               viewBox="0 0 30 30" fill="none" stroke="currentColor"
                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                               class="feather feather-edit-2 p-1 br-8 mb-1 text-secondary">
                              <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                          </svg>
                        </a>'
                        : '<a href="javascript:void(0);" class="edit-btn bs-tooltip" data-id="'.$row->id.'" title="Edit">
                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                               viewBox="0 0 30 30" fill="none" stroke="currentColor"
                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                               class="feather feather-edit-2 p-1 br-8 mb-1">
                              <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                          </svg>
                        </a>';

                    $deleteBtn = $disableActions
                        ? '<a href="javascript:void(0);" class="delete-btn bs-tooltip text-secondary disabled" title="Delete Disabled">
                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                               viewBox="0 0 30 30" fill="none" stroke="currentColor"
                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                               class="feather feather-trash p-1 br-8 mb-1">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4
                                       a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                          </svg>
                        </a>'
                        : '<a href="javascript:void(0);" class="delete-btn bs-tooltip text-danger" data-id="'.$row->id.'" title="Delete">
                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                               viewBox="0 0 30 30" fill="none" stroke="currentColor"
                               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                               class="feather feather-trash p-1 br-8 mb-1">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4
                                       a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                          </svg>
                        </a>';

                    return '<ul class="table-controls text-center"><li>'.$editBtn.'</li><li>'.$deleteBtn.'</li></ul>';
                })
                ->rawColumns(['bank_name','type','action'])
                ->make(true);
        }

        return view('bankbooks.index', compact('accounts'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'account_id'     => 'required|exists:accounts,id',
            'type'           => 'required|in:Receive,Withdraw,Pay Order,Bank Transfer',
            'amount'         => 'required|numeric|min:1',
            'note'           => 'nullable|string',
            'from_account_id'=> 'nullable|exists:accounts,id',
        ]);

        try {
            DB::transaction(function () use ($request, &$responseData) {
                $type = $request->type;
                $amount = $request->amount;
                $note = $request->note;

                if ($type === 'Bank Transfer') {
                    // extra validation: ensure from_account provided and different
                    if (!$request->from_account_id) {
                        throw new \Exception("From Account is required for Bank Transfer.");
                    }
                    if ($request->from_account_id == $request->account_id) {
                        throw new \Exception("From Account and To Account must be different.");
                    }

                    $uuid = (string) Str::uuid();

                    // Create Receive for destination account
                    $receive = BankBook::create([
                        'account_id'   => $request->account_id,
                        'type'         => 'Receive',
                        'amount'       => $amount,
                        'note'         => $note,
                        'transfer_uuid' => $uuid,
                    ]);

                    // Create Transfer (deduct) for source account
                    $transfer = BankBook::create([
                        'account_id'   => $request->from_account_id,
                        'type'         => 'Bank Transfer',
                        'amount'       => $amount,
                        'note'         => $note,
                        'transfer_uuid' => $uuid,
                    ]);

                    $responseData = [$receive, $transfer];
                } else {
                    $bankBook = BankBook::create($request->only(['account_id','type','amount','note']));
                    $responseData = $bankBook;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'BankBook created successfully!',
                'data'    => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function edit($id)
    {
        $bankBook = BankBook::findOrFail($id);

        // If it's part of a transfer, return both sides info (from and to)
        $fromAccountId = null;
        $toAccountId = $bankBook->account_id;

        if ($bankBook->transfer_uuid) {
            $pair = BankBook::where('transfer_uuid', $bankBook->transfer_uuid)->get();

            // find receive row and transfer row
            $receive = $pair->firstWhere('type', 'Receive');
            $transfer = $pair->firstWhere('type', 'Bank Transfer');

            if ($receive) {
                $toAccountId = $receive->account_id;
            }

            if ($transfer) {
                $fromAccountId = $transfer->account_id;
            }
        }

        return response()->json([
            'id'             => $bankBook->id,
            'account_id'     => $toAccountId,
            'from_account_id'=> $fromAccountId,
            'type'           => ($bankBook->transfer_uuid ? 'Bank Transfer' : $bankBook->type),
            'amount'         => $bankBook->amount,
            'note'           => $bankBook->note,
            'transfer_uuid'  => $bankBook->transfer_uuid
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'account_id'     => 'required|exists:accounts,id',
            'type'           => 'required|in:Receive,Withdraw,Pay Order,Bank Transfer',
            'amount'         => 'required|numeric|min:1',
            'note'           => 'nullable|string',
            'from_account_id'=> 'nullable|exists:accounts,id',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $bankBook = BankBook::findOrFail($id);
                $type = $request->type;
                $amount = $request->amount;
                $note = $request->note;

                // Target: Bank Transfer
                if ($type === 'Bank Transfer') {

                    if (!$request->from_account_id) {
                        throw new \Exception("From Account is required for Bank Transfer.");
                    }
                    if ($request->from_account_id == $request->account_id) {
                        throw new \Exception("From Account and To Account must be different.");
                    }

                    if ($bankBook->transfer_uuid) {
                        // existing transfer pair -> update both
                        $pair = BankBook::where('transfer_uuid', $bankBook->transfer_uuid)->get();

                        $receive = $pair->firstWhere('type','Receive');
                        $transfer = $pair->firstWhere('type','Bank Transfer');

                        // if missing any side, attempt to recover: ensure both exist
                        if (!$receive) {
                            // if current $bankBook is Receive, use it
                            if ($bankBook->type == 'Receive') {
                                $receive = $bankBook;
                            } else {
                                // create the missing receive
                                $receive = BankBook::create([
                                    'account_id'   => $request->account_id,
                                    'type'         => 'Receive',
                                    'amount'       => $amount,
                                    'note'         => $note,
                                    'transfer_uuid'=> $bankBook->transfer_uuid,
                                ]);
                            }
                        }

                        if (!$transfer) {
                            if ($bankBook->type == 'Bank Transfer') {
                                $transfer = $bankBook;
                            } else {
                                $transfer = BankBook::create([
                                    'account_id'   => $request->from_account_id,
                                    'type'         => 'Bank Transfer',
                                    'amount'       => $amount,
                                    'note'         => $note,
                                    'transfer_uuid'=> $bankBook->transfer_uuid,
                                ]);
                            }
                        }

                        // Update both records (model updating() will handle balances)
                        $receive->update([
                            'account_id' => $request->account_id,
                            'type'       => 'Receive',
                            'amount'     => $amount,
                            'note'       => $note
                        ]);

                        $transfer->update([
                            'account_id' => $request->from_account_id,
                            'type'       => 'Bank Transfer',
                            'amount'     => $amount,
                            'note'       => $note
                        ]);

                    } else {
                        // previously single record -> convert into transfer pair
                        $uuid = (string) Str::uuid();

                        // Update current as Receive and attach uuid
                        $bankBook->update([
                            'account_id'   => $request->account_id,
                            'type'         => 'Receive',
                            'amount'       => $amount,
                            'note'         => $note,
                            'transfer_uuid'=> $uuid
                        ]);

                        // Create the transfer side
                        BankBook::create([
                            'account_id'    => $request->from_account_id,
                            'type'          => 'Bank Transfer',
                            'amount'        => $amount,
                            'note'          => $note,
                            'transfer_uuid' => $uuid
                        ]);
                    }

                } else {
                    // Non-transfer type

                    if ($bankBook->transfer_uuid) {
                        // If this record is part of a transfer, remove the counterpart then update to single
                        $pair = BankBook::where('transfer_uuid', $bankBook->transfer_uuid)
                            ->where('id', '!=', $bankBook->id)
                            ->first();

                        if ($pair) {
                            // deleting the counterpart will reverse its balance (model deleting hook)
                            $pair->delete();
                        }

                        // Update this row to requested non-transfer type (updating hook will reconcile balances)
                        $bankBook->update([
                            'account_id'   => $request->account_id,
                            'type'         => $request->type,
                            'amount'       => $amount,
                            'note'         => $note,
                            'transfer_uuid'=> null
                        ]);
                    } else {
                        // simple update
                        $bankBook->update($request->only(['account_id','type','amount','note']));
                    }
                }

            });

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
