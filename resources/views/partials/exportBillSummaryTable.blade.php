@php
    use Carbon\Carbon;

    $printDate = Carbon::now()->format('d/m/Y');
    [$year, $monthNum] = explode('-', $month);
    $monthName = Carbon::createFromDate($year, $monthNum, 1)->format('F Y');

    $totalUSD = $totalSubmitted = $totalDfVat = 0;

    foreach($bills as $bill) {
        $submittedExp = $bill->submittedExpense();
        $dfVat = $bill->dfVat();
        $totalUSD += $bill->usd;
        $totalSubmitted += $submittedExp;
        $totalDfVat += $dfVat;
    }
     $count = 1;
@endphp

<style>
    /* ✅ GLOBAL TABLE STYLES */
    table {
        width: 100%;
        border-collapse: collapse !important;
        table-layout: auto !important;
    }

    th, td {
        border: 1px solid #000 !important;
        text-align: center !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
        padding: 6px 8px;
        font-size: 13px;
    }

    /* ✅ FIX LAST COLUMN (NO BREAK, NO SHRINK) */
    th:last-child,
    td:last-child {
        min-width: 140px !important; /* ✅ FIXED WIDTH */
        max-width: 140px !important;
        white-space: nowrap !important;
    }

    /* ✅ HEADER */
    .company-header {
        text-align: center;
    }
    .company-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: bold;
    }
    .company-header p {
        margin: 2px 0;
        font-size: 13px;
    }

    /* ✅ PRINT MODE */
    @media print {

        body {
            margin: 10px;
            padding: 0;
            font-size: 12px;
        }

        table, th, td {
            border: 1px solid #000 !important;
            border-collapse: collapse !important;
        }

        th, td {
            padding: 4px 6px !important;
        }

        /* ✅ Force landscape */
        @page {
            size: landscape;
            margin: 10mm;
        }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        tr { page-break-inside: avoid; }

        .no-print { display: none !important; }

        /* ✅ PRINT FIX FOR LAST COLUMN TOO */
        th:last-child,
        td:last-child {
            min-width: 150px !important;
            max-width: 150px !important;
        }
    }
</style>



<div class="company-header">
    <h1>MULTI FABS LTD</h1>
    <p>(SELF C&F AGENTS)</p>
    <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
</div>

<hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />

<div style="display: flex; justify-content: space-between; font-size: 14px;">
    <div><strong>Export Statement :</strong> {{ $monthName }}</div>
    <div><strong>Print Date:</strong> {{ Carbon::now()->format('d-m-Y') }}</div>
</div>

<br>

<!-- Your existing code up to the main table -->
<table id="exportSummaryTable">
    <thead>
    <tr>
        <th>SL</th>
        <th>INVOICE NO</th>
        <th>DATE</th>
        <th>CTN.</th>
        <th>INVOICE PCS</th>
        <th>VALUE($)</th>
        <th>B/E NO.</th>
        <th>DATE</th>
        <th>BILL NO</th>
        <th>ACTUAL DATE</th>
        <th>SUBMITED EXP</th>
        <th>DF VAT</th>
        <th>APPROVED BILL</th>
    </tr>
    </thead>

    <tbody>
    @forelse($bills as $bill)
        @php
            $submittedExp = $bill->submittedExpense();
            $dfVat = $bill->dfVat();
        @endphp
        <tr>
            <td>{{ $count++ }}</td>
            <td>{{ $bill->invoice_no }}</td>
            <td>{{ optional($bill->invoice_date)->format('d-M-Y') }}</td>
            <td>{{ $bill->total_qty }} CTN</td>
            <td>{{ $bill->qty_pcs }} PCS</td>
            <td>{{ number_format($bill->usd, 2) }} $</td>
            <td>{{ $bill->be_no }}</td>
            <td>{{ optional($bill->be_date)->format('d-M-Y') }}</td>
            <td>{{ $bill->bill_no }}</td>
            <td>{{ optional($bill->bill_date)->format('d-M-Y') }}</td>
            <td>{{ number_format($submittedExp, 2) }}</td>
            <td>{{ number_format($dfVat, 2) }}</td>
            <td></td>
        </tr>
    @empty
        <tr>
            <td colspan="13">No records found for {{ $monthName }}</td>
        </tr>
    @endforelse

    @if(count($bills) > 0)
        <!-- ✅ Grand Total as regular row instead of tfoot -->
        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td colspan="10" style="text-align: right;">GRAND TOTAL</td>
            <td>{{ number_format($totalSubmitted, 2) }}</td>
            <td>{{ number_format($totalDfVat, 2) }}</td>
            <td></td>
        </tr>
    @endif
    </tbody>
</table>



