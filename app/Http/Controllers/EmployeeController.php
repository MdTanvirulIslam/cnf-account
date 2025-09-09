<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Employee::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('department', function ($row) {
                    return $row->department === 'Import'
                        ? '<span class="badge badge-light-primary">Import</span>'
                        : '<span class="badge badge-light-info">Export</span>';
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
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5
                                                 2 22l1.5-5.5L17 3z"></path>
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
                                        <path d="M19 6v14a2 2 0 0 1-2
                                                 2H7a2 2 0 0 1-2-2V6m3
                                                 0V4a2 2 0 0 1 2-2h4a2
                                                 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    ';
                })
                ->rawColumns(['department', 'action'])
                ->make(true);
        }

        return view('employees.index');
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
            'name'          => 'required|string|max:255',
            'mobile_number' => [
                'required',
                'digits:11',
                'unique:employees,mobile_number',
                'regex:/^(013|016|017|018|019|015)[0-9]{8}$/'
            ],
            'address'       => 'nullable|string|max:255',
            'department'    => 'required|in:Import,Export',
            'note'          => 'nullable|string',
        ]);

        $employee = Employee::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully!',
            'data'    => $employee,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);

        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'mobile_number' => [
                'required',
                'digits:11',
                'unique:employees,mobile_number,' . $id, // exclude current id for update
                'regex:/^(013|016|017|018|019)[0-9]{8}$/'
            ],
            'address'       => 'nullable|string|max:255',
            'department'    => 'required|in:Import,Export',
            'note'          => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($id);
        $employee->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully!',
        ]);
    }
}
