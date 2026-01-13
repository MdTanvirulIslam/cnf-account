@php
    use Carbon\Carbon;
    use App\Http\Controllers\ExportBillSummaryReportController;

    $printDate = Carbon::now()->format('d/m/Y');
    [$year, $monthNum] = explode('-', $month);
    $monthName = Carbon::createFromDate($year, $monthNum, 1)->format('F Y');

    $totalUSD = $totalSubmitted = $totalDfVat = $totalItc = $totalCtn = $totalInvoicePcs = 0;

    foreach($bills as $bill) {
        $submittedExp = $bill->submittedExpense();
        $dfVat = $bill->dfVat();
        $itcValue = $bill->itc ?? 0;
        $totalUSD += $bill->usd;
        $totalSubmitted += $submittedExp;
        $totalDfVat += $dfVat;
        $totalItc += $itcValue;
        $totalCtn += $bill->total_qty;
        $totalInvoicePcs += $bill->qty_pcs;
    }
    $count = 1;

    // Get company name for display
    $controller = new ExportBillSummaryReportController();
    $companyName = $company == 'all' ? 'All Companies' : $controller->companyNames[$company] ?? $company;
@endphp

<!-- UPDATED COMPANY HEADER -->
<div class="company-header">
    <h1>{{ $companyName }}</h1>
    @if($company == 'all' || $company == 'MULTI FABS LTD')
        <p>(SELF C&F AGENTS)</p>
    @endif
    <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
</div>

<hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />

<div style="display: flex; justify-content: space-between; font-size: 14px;">
    <div><strong>Export Statement :</strong> {{ $monthName }}</div>
    <div><strong>Print Date:</strong> {{ Carbon::now()->format('d-m-Y') }}</div>
</div>

<br>

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
        <th>ITC</th>
        <th>APPROVED BILL</th>
    </tr>
    </thead>

    <tbody>
    @forelse($bills as $bill)
        @php
            $submittedExp = $bill->submittedExpense();
            $dfVat = $bill->dfVat();
            $itcValue = $bill->itc ?? 0;
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
            <td>{{ number_format($itcValue, 2) }}</td>
            <td></td>
        </tr>
    @empty
        <tr>
            <td colspan="14">No records found for {{ $monthName }}</td>
        </tr>
    @endforelse

    @if(count($bills) > 0)
        <!-- ✅ Grand Total as regular row instead of tfoot -->
        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td colspan="3" style="text-align: right;">GRAND TOTAL</td>
            <td>{{ $totalCtn }} CTN</td>
            <td>{{ $totalInvoicePcs }} PCS</td>
            <td>{{ number_format($totalUSD, 2) }} $</td>
            <td colspan="4"></td>
            <td>{{ number_format($totalSubmitted, 2) }}</td>
            <td>{{ number_format($totalDfVat, 2) }}</td>
            <td>{{ number_format($totalItc, 2) }}</td>
            <td></td>
        </tr>
    @endif
    </tbody>
</table>
