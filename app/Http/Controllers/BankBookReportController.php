<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankBook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankBookReportController extends Controller
{
    private $months = [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    // Show the report page (default: current month, Dhaka Bank, all types)
    public function index(Request $request)
    {
        // Default filters
        $bank  = $request->get('bank', 'Dhaka Bank'); // could be account_id or name
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $type  = $request->get('type', 'all');

        // Load all banks for dropdown
        $banks = Account::pluck('name', 'id'); // id => name

        // Base query
        $query = BankBook::query();

        // ---------- Bank filter ----------
        if (!empty($bank) && strtolower((string)$bank) !== 'all') {
            if (is_numeric($bank)) {
                $query->where('account_id', intval($bank));
            } else {
                $query->whereHas('account', function ($q) use ($bank) {
                    $q->where('name', $bank);
                });
            }
        }

        // ---------- Month filter ----------
        try {
            $carbonMonth = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            $carbonMonth = Carbon::now();
            $month = $carbonMonth->format('Y-m');
        }
        $query->whereYear('created_at', $carbonMonth->year)
            ->whereMonth('created_at', $carbonMonth->month)
            ->orderBy('created_at', 'asc');

        // ---------- Type filter ----------
        if (!empty($type) && strtolower($type) !== 'all') {
            $query->where('type', $type);
        }

        // ---------- Fetch data ----------
        $data = $query->orderBy('created_at', 'desc')->get();

        // Add Final Amount for each record (optional convenience fields)
        $data->map(function ($row) {
            $received  = ($row->type === 'Receive') ? $row->amount : 0;
            $withdraw  = ($row->type !== 'Receive') ? $row->amount : 0;
            $row->received_amount   = $received;
            $row->withdrawal_amount = $withdraw;
            $row->final_amount      = $received - $withdraw;
            return $row;
        });

        // ---------- AJAX response ----------
        if ($request->ajax()) {
            // Pass month and bank so partial can render header correctly
            $html = view('partials.bankbookReportTable', compact('data', 'month', 'bank'))->render();
            return response()->json(['html' => $html]);
        }

        // ---------- Full page render ----------
        return view('reports.bankbookReport', compact('data', 'banks', 'bank', 'month', 'type'));
    }
}
