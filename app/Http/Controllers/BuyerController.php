<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Buyer::latest();
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary editBtn" data-id="'.$row->id.'">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->id.'">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('buyers.index');
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
        $id = $request->id;

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:buyers,email,' . $id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'company' => 'nullable|string',
        ]);

        $buyer = Buyer::updateOrCreate(['id' => $id], $validated);

        return response()->json(['success' => true, 'message' => $id ? 'Buyer updated!' : 'Buyer created!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Buyer $buyer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $buyer = Buyer::findOrFail($id);
        return response()->json($buyer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Buyer $buyer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Buyer::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Buyer deleted!']);
    }
}
