<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expenses;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = expenses::with(['category', 'subCategory'])->latest();

            return DataTables::of($expenses)
                ->addIndexColumn()
                ->addColumn('category', fn($row) => isset($row->category?->category) ? $row->category?->category : '-')
                ->addColumn('sub_category', fn($row) => isset($row->subCategory?->category) ? $row->subCategory?->category : '-')
                ->addColumn('action', function ($row) {
                    return '
        <ul class="table-controls text-center" style="list-style: none; padding-left: 0; margin: 0; display: flex; justify-content: center; gap: 6px;">
            <li>
                <a href="javascript:void(0);"
                   class="edit-btn bs-tooltip"
                   data-id="'.$row->id.'"
                   data-bs-toggle="tooltip" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);"
                   class="delete-btn bs-tooltip text-danger"
                   data-id="'.$row->id.'"
                   data-bs-toggle="tooltip" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash p-1 br-8 mb-1">
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

        $categories = Category::whereNull('parent_id')->get();
        return view('expenses.index', compact('categories'));
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
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        $expense = Expenses::create($data);

        return response()->json(['success' => true, 'message' => 'Expense created successfully!', 'data' => $expense]);
    }

    /**
     * Display the specified resource.
     */
    public function show(expenses $expenses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $expense = Expenses::findOrFail($id);
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $expense = expenses::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        $expense->update($data);

        return response()->json(['success' => true, 'message' => 'Expense updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $expense = Expenses::findOrFail($id);
        $expense->delete();

        return response()->json(['success' => true, 'message' => 'Expense deleted successfully!']);
    }

    public function getSubCategories($categoryId)
    {
        $subCategories = Category::where('parent_id', $categoryId)->get();
        return response()->json($subCategories);
    }
}
