@php
    use Carbon\Carbon;

    $printDate = Carbon::now()->format('d/m/Y');
    [$year, $monthNum] = explode('-', $month);
    $monthName = Carbon::createFromDate($year, $monthNum, 1)->format('F Y');

    $totalValue = $totalPort = $totalTotalBill = $totalDfVat = $totalDocFee = $totalScan = 0;
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
    <div><strong>IMPORT BILL STATEMENT :</strong> {{ $monthName }}</div>
    <div class="right"><strong> Date:</strong> {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d-m-Y') }} </div>
</div>

<table class="invoice-table">
    <thead>
    <tr>
        <th>L/C NO.</th>
        <th>B/E</th>
        <th>BE DT</th>
        <th>BILL NO.</th>
        <th>BILL DT</th>
        <th>GOODS NAME</th>
        <th>QNTY</th>
        <th>NET WEIGHT</th>
        <th>MONTH</th>
        <th>VALUE</th>
        <th>PORT BILL</th>
        <th>TOTAL BILL AMOUNT</th>
        <th>DF VAT</th>
        <th>DOC FEE</th>
        <th>SCAN FEE</th>
    </tr>
    </thead>
    <tbody>
    @forelse($bills as $bill)
        @php
            $portBill = $bill->portBill();
            $dfVat = $bill->dfVat();
            $totalBillAmount = $bill->totalExpenses();

            $totalValue += $bill->value;
            $totalPort += $portBill;
            $totalTotalBill += $totalBillAmount;
            $totalDfVat += $dfVat;
            $totalDocFee += $bill->doc_fee;
            $totalScan += $bill->scan_fee;
        @endphp
        <tr>
            <td>{{ $bill->lc_no }}</td>
            <td>{{ $bill->be_no }}</td>
            <td>{{ optional($bill->be_date)->format('d-M') }}</td>
            <td>{{ $bill->bill_no }}</td>
            <td>{{ optional($bill->bill_date)->format('d-M') }}</td>
            <td>{{ $bill->item }}</td>
            <td>{{ $bill->qty }}</td>
            <td>{{ number_format($bill->weight, 2) }}</td>
            <td>{{ $monthName }}</td>
            <td>{{ number_format($bill->value, 2) }}</td>
            <td>{{ number_format($portBill, 2) }}</td>
            <td>{{ number_format($totalBillAmount, 2) }}</td>
            <td>{{ number_format($dfVat, 2) }}</td>
            <td>{{ number_format($bill->doc_fee, 2) }}</td>
            <td>{{ number_format($bill->scan_fee, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="15" class="center">No records found for {{ $monthName }}</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <td colspan="9" class="right"><strong>GRAND TOTAL</strong></td>
        <td class="left"><strong>{{ number_format($totalValue, 2) }}</strong></td>
        <td class="left"><strong>{{ number_format($totalPort, 2) }}</strong></td>
        <td class="left"><strong>{{ number_format($totalTotalBill, 2) }}</strong></td>
        <td class="left"><strong>{{ number_format($totalDfVat, 2) }}</strong></td>
        <td class="left"><strong>{{ number_format($totalDocFee, 2) }}</strong></td>
        <td class="left"><strong>{{ number_format($totalScan, 2) }}</strong></td>
    </tr>
    </tfoot>
</table>
