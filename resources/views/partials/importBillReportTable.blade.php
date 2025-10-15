@php $grandTotal = 0; @endphp

@foreach($importBills as $bill)
    @php $billTotal = $bill->expenses->sum('amount'); $grandTotal += $billTotal; @endphp

    <div class="company-header">
        <h1>{{ $bill->company_name }}</h1>
        <p>(SELF C&F AGENTS)</p>
        <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
    </div>

    <hr style="border:none;border-top:1px solid #222;margin:10px 0 18px 0">

    <div class="invoice-info">
        <div><strong>BILL NO:</strong> {{ $bill->bill_no }}</div>
        <div class="right"><strong>DATE:</strong> {{ $bill->bill_date?->format('d/m/Y') }}</div>
    </div>

    <h3>SUB: CLEARING BILL FOR {{ $bill->item }}</h3>

    <table class="info-table">
        <tr>
            <td class="info-key">L/C No</td>
            <td class="info-value">{{ $bill->lc_no }}</td>
            <td class="info-key">Date</td>
            <td class="info-value">{{ $bill->lc_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="info-key">ITEM</td>
            <td class="info-value">{{ $bill->item }}</td>
            <td class="info-key">VALUE</td>
            <td class="info-value">{{ number_format($bill->value, 2) }}</td>
        </tr>
        <tr>
            <td class="info-key">QTY</td>
            <td class="info-value">{{ $bill->qty }}</td>
            <td class="info-key">WEIGHT</td>
            <td class="info-value">{{ $bill->weight }}</td>
        </tr>
        <tr>
            <td class="info-key">B/E NO</td>
            <td class="info-value">{{ $bill->be_no }}</td>
            <td class="info-key">Date</td>
            <td class="info-value">{{ $bill->be_date?->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="invoice-table">
        <thead>
        <tr>
            <th class="center" style="width:5%">SL NO</th>
            <th class="left" style="width:65%">DESCRIPTION</th>
            <th class="center" style="width:15%">AMOUNT</th>
            <th class="center" style="width:15%">REMARK</th>
        </tr>
        </thead>
        <tbody>
        @forelse($bill->expenses as $index => $exp)
            <tr>
                <td class="center">{{ $index+1 }}</td>
                <td class="left">{{ $exp->expense_type }}</td>
                <td class="center">{{ number_format($exp->amount, 2) }}</td>
                <td class="center"></td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="center">No Expenses Found</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr class="total-row">
            <td colspan="2" class="center"><strong>TOTAL AMOUNT</strong></td>
            <td class="center"><strong>{{ number_format($billTotal, 2) }}</strong></td>
            <td class="center"></td>
        </tr>
        </tfoot>
    </table>
    <br>
@endforeach

@if(count($importBills) > 0)
    <div class="grand-total">
        <p><strong>GRAND TOTAL: {{ number_format($grandTotal, 2) }} TAKA ONLY</strong></p>
    </div>
@endif
