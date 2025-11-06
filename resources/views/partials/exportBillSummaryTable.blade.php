@php
    use Carbon\Carbon;

    $printDate = Carbon::now()->format('d/m/Y');
    [$year, $monthNum] = explode('-', $month);
    $monthName = Carbon::createFromDate($year, $monthNum, 1)->format('F Y');

    $totalUSD = $totalSubmitted = $totalDfVat = 0;
@endphp
<style>
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
        color: #333;
    }

    .invoice-info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-size: 14px;
    }
    .invoice-info div {
        width: 48%;
    }

    h3 {
        margin-top: 20px;
        font-size: 15px;
        text-transform: uppercase;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0 20px;
        font-size: 13px;
    }
    .info-table td {
        border: 1px solid #222;
        padding: 6px;
        vertical-align: top;
    }
    .info-key {
        font-weight: bold;
        width: 10%;
    }
    .info-value {
        width: 40%;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .invoice-table th,
    .invoice-table td {
        border: 1px solid #000;
        padding: 6px 8px;
    }
    .invoice-table th {
        background-color: #f4f4f4;
        text-align: center;
    }
    .right {
        text-align: right;
    }
    .center {
        text-align: center;
    }
    .total-row td {
        font-weight: bold;
        background: #f9f9f9;
    }

    .footer-note {
        margin-top: 20px;
        font-size: 13px;
    }
</style>
<div class="company-header">
    <h1>MULTI FABS LTD</h1>
    <p>(SELF C&F AGENTS)</p>
    <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
</div>
<hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />
<div class="invoice-info">
    <div><strong>Export Steatement :</strong>{{ $monthName }}</div>
    <div class="right"><strong>Print Date:</strong> {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
</div>
</br>
<table class="invoice-table">
    <thead>
    <tr>
        <th>INVOICE NO</th>
        <th>TOTAL DATE</th>
        <th>CTN.</th>
        <th>INVOICE PCS</th>
        <th>VALUE($)</th>
        <th>B/E NO.</th>
        <th>DATE</th>
        <th>BILL NO</th>
        <th>ACTUAL DATE</th>
        <th>SUBMITED EXP</th>
        <th>DF VAT</th>
        <th>APPROVED BILL (TK.)</th>
    </tr>
    </thead>
    <tbody>
    @forelse($bills as $bill)
        @php
            $submittedExp = $bill->submittedExpense(); // Sum of all expenses
            $dfVat = $bill->dfVat(); // DF VAT from expense_type

            $totalUSD += $bill->usd;
            $totalSubmitted += $submittedExp;
            $totalDfVat += $dfVat;
        @endphp
        <tr>
            <td>{{ $bill->invoice_no }}</td>
            <td>{{ optional($bill->invoice_date)->format('d-M-Y') }}</td>
            <td>{{ $bill->ctn_no }}</td>
            <td>{{ $bill->qty_pcs }}</td>
            <td>{{ number_format($bill->usd,2) }}</td>
            <td>{{ $bill->be_no }}</td>
            <td>{{ optional($bill->be_date)->format('d-M-Y') }}</td>
            <td>{{ $bill->bill_no }}</td>
            <td>{{ optional($bill->bill_date)->format('d-M-Y') }}</td>
            <td>{{ number_format($submittedExp,2) }}</td>
            <td>{{ number_format($dfVat,2) }}</td>
            <td></td> <!-- Approved Bill empty -->
        </tr>
    @empty
        <tr>
            <td colspan="12" class="text-center">No records found for {{ $monthName }}</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <td colspan="9" class="right"><strong>GRAND TOTAL</strong></td>

        <td class="left"><strong>{{ number_format($totalSubmitted,2) }}</strong></td>
        <td class="center"><strong>{{ number_format($totalDfVat,2) }}</strong></td>
        <td></td>
    </tr>
    </tfoot>
</table>
