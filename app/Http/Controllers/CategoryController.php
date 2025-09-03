<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Category::with('parent')->select('categories.*');

            return DataTables::of($query)
                ->addIndexColumn() // DT_RowIndex
                ->addColumn('parent_name', function ($row) {
                    return $row->parent
                        ? e($row->parent->category)
                        : '<span class="badge badge-light-secondary">Root</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <ul class="table-controls text-center">
                            <li>
                                <a href="javascript:void(0);"
                                   class="edit-btn bs-tooltip"
                                   data-id="'.$row->id.'"
                                   data-bs-toggle="tooltip" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);"
                                   class="delete-btn bs-tooltip text-danger"
                                   data-id="'.$row->id.'"
                                   data-bs-toggle="tooltip" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash p-1 br-8 mb-1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </a>
                            </li>
                        </ul>
                    ';
                })
                ->rawColumns(['parent_name', 'action'])
                ->make(true);
        }

        $parents = Category::orderBy('category')->get();

        return view('categories.index', compact('parents'));
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
            'category'  => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $cat = Category::create([
            'category'  => $request->category,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!',
            'data'    => $cat,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cat = Category::findOrFail($id);
        return response()->json([
            'id'        => $cat->id,
            'category'  => $cat->category,
            'parent_id' => $cat->parent_id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category'  => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|not_in:'.$id, // prevent selecting itself
        ]);

        $cat = Category::findOrFail($id);
        $cat->update([
            'category'  => $request->category,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!',
        ]);
    }

    public function destroy($id)
    {
        $cat = Category::findOrFail($id);
        $cat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!',
        ]);
    }
}
