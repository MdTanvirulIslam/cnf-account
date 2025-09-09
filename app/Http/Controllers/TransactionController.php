<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

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
                ->addColumn('employee_name', fn($row) => isset($row->employee->name) ? $row->employee->name : 'N/A')
                ->editColumn('date', fn($row) => $row->date instanceof \Carbon\Carbon ? $row->date->format('Y-m-d') : $row->date)
                ->editColumn('amount', fn($row) => number_format($row->amount, 2))
                ->editColumn('type', fn($row) =>
                $row->type === 'receive'
                    ? '<span class="badge badge-light-success">Receive</span>'
                    : '<span class="badge badge-light-secondary">Return</span>'
                )
                ->addColumn('action', function ($row) use ($department) {
                    $prefix = strtolower($department);
                    return '
                        <ul class="table-controls text-center">
                            <li><a href="javascript:void(0);" class="edit-btn" data-id="'.$row->id.'" data-route="'.route("transactions.$prefix.edit",$row->id).'">✏️</a></li>
                            <li><a href="javascript:void(0);" class="delete-btn text-danger" data-id="'.$row->id.'" data-route="'.route("transactions.$prefix.destroy",$row->id).'">🗑</a></li>
                        </ul>';
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
}
