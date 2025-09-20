<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;
use App\Models\Category;
use Carbon\Carbon;

class ExpenseReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $category = $request->input('category', 'all');
        $subCategory = $request->input('sub_category', 'all');

        // Base query: current month expenses
        $query = Expenses::with(['category', 'subCategory'])
            ->whereMonth('date', Carbon::parse($month)->month)
            ->whereYear('date', Carbon::parse($month)->year);

        // Filter by category
        if ($category !== 'all') {
            $query->where('category_id', intval($category));
        }

        // Filter by sub-category
        if ($subCategory !== 'all') {
            $query->where('sub_category_id', intval($subCategory));
        }

        $data = $query->orderBy('date', 'desc')->get();

        // Dropdowns
        $categories = Category::whereNull('parent_id')->pluck('category', 'id');
        $subCategories = Category::whereNotNull('parent_id')->pluck('category', 'id');

        // AJAX request → return only table partial
        if ($request->ajax()) {
            $html = view('partials.expenseReportTable', compact(
                'data',
                'month',
                'category',
                'subCategory',
                'categories',
                'subCategories'
            ))->render();

            return response()->json(['html' => $html]);
        }

        // Normal page load
        return view('reports.expense_report', compact(
            'data',
            'categories',
            'subCategories',
            'category',
            'subCategory',
            'month'
        ));
    }
}
