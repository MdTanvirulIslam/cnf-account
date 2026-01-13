@php
    use Carbon\Carbon;
    use App\Http\Controllers\ImportBillSummaryReportController;

    $printDate = Carbon::now()->format('d/m/Y');
    [$year, $monthNum] = explode('-', $month);
    $monthName = Carbon::createFromDate($year, $monthNum, 1)->format('F Y');

    $totalValue = $totalPort = $totalTotalBill = $totalDfVat = $totalDocFee = $totalScan = $totalItc = 0;

    // Get company name for display
    $controller = new ImportBillSummaryReportController();
    $companyName = $company == 'all' ? 'All Companies' : $controller->companyNames[$company] ?? $company;
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
        table-layout: fixed;
    }
    .invoice-table th,
    .invoice-table td {
        border: 1px solid #000;
        padding: 6px 8px;
        word-wrap: break-word;  /* Allow text to wrap */
        overflow-wrap: break-word;  /* Allow text to wrap */
    }
    .invoice-table th {
        background-color: #f4f4f4;
        text-align: center;
    }

    /* Set specific column widths - EXACTLY YOUR ORIGINAL */
    .invoice-table th:nth-child(1),
    .invoice-table td:nth-child(1) { width: 8%; }   /* L/C NO. */
    .invoice-table th:nth-child(2),
    .invoice-table td:nth-child(2) { width: 6%; }   /* B/E */
    .invoice-table th:nth-child(3),
    .invoice-table td:nth-child(3) { width: 5%; }   /* BE DT */
    .invoice-table th:nth-child(4),
    .invoice-table td:nth-child(4) { width: 7%; }   /* BILL NO. */
    .invoice-table th:nth-child(5),
    .invoice-table td:nth-child(5) { width: 5%; }   /* BILL DT */
    .invoice-table th:nth-child(6),
    .invoice-table td:nth-child(6) { width: 10%; }  /* GOODS NAME */
    .invoice-table th:nth-child(7),
    .invoice-table td:nth-child(7) { width: 5%; }   /* QNTY */
    .invoice-table th:nth-child(8),
    .invoice-table td:nth-child(8) { width: 7%; }   /* NET WEIGHT */
    .invoice-table th:nth-child(9),
    .invoice-table td:nth-child(9) { width: 8%; }   /* MONTH */
    .invoice-table th:nth-child(10),
    .invoice-table td:nth-child(10) { width: 7%; }  /* VALUE */
    .invoice-table th:nth-child(11),
    .invoice-table td:nth-child(11) { width: 7%; }  /* PORT BILL */
    .invoice-table th:nth-child(12),
    .invoice-table td:nth-child(12) { width: 7%; }  /* TOTAL BILL AMOUNT */
    .invoice-table th:nth-child(13),
    .invoice-table td:nth-child(13) { width: 6%; }  /* DF VAT */
    .invoice-table th:nth-child(14),
    .invoice-table td:nth-child(14) { width: 5%; }  /* DOC FEE */
    .invoice-table th:nth-child(15),
    .invoice-table td:nth-child(15) { width: 5%; }  /* SCAN FEE */
    .invoice-table th:nth-child(16),
    .invoice-table td:nth-child(16) { width: 5%; }  /* ITC */

    .right {
        text-align: right;
    }
    .center {
        text-align: center;
    }
    .left {
        text-align: left;
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

<!-- ONLY UPDATED COMPANY NAME HERE -->
<div class="company-header">
    <h1>{{ $companyName }}</h1>
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
        <th>ITC</th>
    </tr>
    </thead>
    <tbody>
    @forelse($bills as $bill)
        @php
            $portBill = $bill->portBill();
            $dfVat = $bill->dfVat();
            $totalBillAmount = $bill->totalExpenses();
            $itcValue = $bill->itc ?? 0;

            $totalValue += $bill->value;
            $totalPort += $portBill;
            $totalTotalBill += $totalBillAmount;
            $totalDfVat += $dfVat;
            $totalDocFee += $bill->doc_fee;
            $totalScan += $bill->scan_fee;
            $totalItc += $itcValue;
        @endphp
        <tr>
            <td class="center">{{ $bill->lc_no }}</td>
            <td class="center">{{ $bill->be_no }}</td>
            <td class="center">{{ optional($bill->be_date)->format('d-M') }}</td>
            <td class="center">{{ $bill->bill_no }}</td>
            <td class="center">{{ optional($bill->bill_date)->format('d-M') }}</td>
            <td class="center">{{ $bill->item }}</td>
            <td class="center">{{ $bill->qty }}</td>
            <td class="center">{{ number_format($bill->weight, 2) }}</td>
            <td class="center">{{ $monthName }}</td>
            <td class="center">{{ number_format($bill->value, 2) }}</td>
            <td class="center">{{ number_format($portBill, 2) }}</td>
            <td class="center">{{ number_format($totalBillAmount, 2) }}</td>
            <td class="center">{{ number_format($dfVat, 2) }}</td>
            <td class="center">{{ number_format($bill->doc_fee, 2) }}</td>
            <td class="center">{{ number_format($bill->scan_fee, 2) }}</td>
            <td class="center">{{ number_format($itcValue, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="16" class="center">No records found for {{ $monthName }}</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr>
        <td colspan="9" class="right"><strong>GRAND TOTAL</strong></td>
        <td class="center"><strong>{{ number_format($totalValue, 2) }}</strong></td>
        <td class="center"><strong>{{ number_format($totalPort, 2) }}</strong></td>
        <td class="center"><strong>{{ number_format($totalTotalBill, 2) }}</strong></td>
        <td class="center"><strong>{{ number_format($totalDfVat, 2) }}</strong></td>
        <td class="center"><strong>{{ number_format($totalDocFee, 2) }}</strong></td>
        <td class="center"><strong>{{ number_format($totalScan, 2) }}</strong></td>
        <td class="center"><strong>{{ number_format($totalItc, 2) }}</strong></td>
    </tr>
    </tfoot>
</table>
