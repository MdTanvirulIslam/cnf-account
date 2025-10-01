<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TransactionController extends Controller
{
    private function getDepartment(Request $request)
    {
        return str_contains($request->route()->getName(), 'import') ? 'Import' : 'Export';
    }

    public function index(Request $request)
    {
        $department = $this->getDepartment($request);

        if ($request->ajax()) {
            $query = Transaction::with('employee')
                ->whereHas('employee', fn($q) => $q->where('department', $department))
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee_name', fn($row) => $row->employee->name ?? 'N/A')
                ->editColumn('date', fn($row) =>
                $row->date instanceof \Carbon\Carbon
                    ? $row->date->format('Y-m-d')
                    : date('Y-m-d', strtotime($row->date))
                )
                ->editColumn('amount', fn($row) => number_format($row->amount, 2))
                ->editColumn('type', fn($row) =>
                $row->type === 'receive'
                    ? '<span class="badge badge-light-success">Receive</span>'
                    : '<span class="badge badge-light-secondary">Return</span>'
                )
                ->addColumn('action', function ($row) {
                    $editId   = $row->id;
                    $deleteId = $row->id;

                    // build routes dynamically (import/export will be auto detected)
                    $department = strtolower(isset($row->employee->department) ? $row->employee->department : 'import');
                    $editRoute   = route("transactions.$department.edit", $editId);
                    $deleteRoute = route("transactions.$department.destroy", $deleteId);

                    return '
        <ul class="table-controls text-center">
            <li>
                <a href="javascript:void(0);"
                   class="edit-btn bs-tooltip"
                   data-id="'.$editId.'"
                   data-route="'.$editRoute.'"
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
                   data-route="'.$deleteRoute.'"
                   data-bs-toggle="tooltip"
                   title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                         viewBox="0 0 30 30" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-trash p-1 br-8 mb-1">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6
                                 m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                        </path>
                    </svg>
                </a>
            </li>
        </ul>
    ';
                })

                ->rawColumns(['type','action'])
                ->make(true);
        }

        $employees = Employee::where('department', $department)->get();
        return view('transactions.index', compact('employees','department'));
    }

    public function store(Request $request)
    {
        $department = $this->getDepartment($request);

        $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees','id')->where(fn($q) => $q->where('department',$department))
            ],
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', Rule::in(['receive','return'])],
            'note' => 'nullable|string|max:1000'
        ]);

        // Validate return amount if type is 'return'
        if ($request->type === 'return') {
            $this->validateReturnAmount($request);
        }

        $transaction = Transaction::create($request->only(['employee_id','date','amount','type','note']));

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully!',
            'data' => $transaction
        ]);
    }

    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);

        return response()->json([
            'id' => $transaction->id,
            'employee_id' => $transaction->employee_id,
            'date' => $transaction->date?->format('Y-m-d'),
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'note' => $transaction->note,
        ]);
    }

    public function update(Request $request, $id)
    {
        $department = $this->getDepartment($request);

        $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees','id')->where(fn($q) => $q->where('department',$department))
            ],
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', Rule::in(['receive','return'])],
            'note' => 'nullable|string|max:1000'
        ]);

        // Validate return amount if type is 'return'
        if ($request->type === 'return') {
            $this->validateReturnAmount($request, $id);
        }

        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->only(['employee_id','date','amount','type','note']));

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully!'
        ]);
    }

    /**
     * Validate return amount against overall received amounts (across all dates)
     */
    private function validateReturnAmount(Request $request, $transactionId = null)
    {
        $employeeId = $request->employee_id;
        $returnAmount = $request->amount;

        // Get total received amount for this employee (all dates)
        $totalReceived = Transaction::where('employee_id', $employeeId)
            ->where('type', 'receive');

        // Get total returned amount for this employee (all dates)
        $totalReturned = Transaction::where('employee_id', $employeeId)
            ->where('type', 'return');

        // Exclude current transaction when updating
        if ($transactionId) {
            $totalReceived->where('id', '!=', $transactionId);
            $totalReturned->where('id', '!=', $transactionId);
        }

        $totalReceivedAmount = $totalReceived->sum('amount');
        $totalReturnedAmount = $totalReturned->sum('amount');

        // Calculate net available amount (overall)
        $netAvailable = $totalReceivedAmount - $totalReturnedAmount;

        // Check if return amount exceeds overall available amount
        if ($returnAmount > $netAvailable) {
            $message = "Return amount (৳" . number_format($returnAmount, 2) .
                ") exceeds available balance (৳" . number_format($netAvailable, 2) .
                ") for this employee.";

            // If no received amount at all
            if ($totalReceivedAmount == 0) {
                $message = "No received amount found for this employee. Cannot process return.";
            }

            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => [$message]
            ]);
        }
    }
}
