@php
    use Carbon\Carbon;

    $receiveTotal = $data->filter(fn($r) => strtolower((string) $r->type) === 'receive')->sum('amount');
    $withdrawTotal = $data->filter(fn($r) => strtolower((string) $r->type) !== 'receive')->sum('amount');
    $finalTotal = $receiveTotal - $withdrawTotal;

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
        text-align: left;
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
<hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;"/>
<div class="invoice-info">
    <div>
        <strong>Bank Book</strong>
        For the Month of {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F, Y') }}
        and Bank is {{ (string) $bank === 'all' ? 'All Banks' : $bank }}
    </div>
    <div class="right">
        <strong>Date:</strong> {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d-m-Y') }}
    </div>
</div>

<table class="invoice-table" style="width: 100%; margin-top: 10px;">
    <thead>
    <tr>
        <th style="width:120px">Date</th>
        <th>Type</th>
        <th>Note</th>
        <th class="text-end">Received Amount</th>
        <th class="text-end">Withdrawal Amount</th>
        <th class="text-end">Final Amount</th>
    </tr>
    </thead>
    <tbody>
    @forelse($data as $row)
        @php
            $received = strtolower((string) $row->type) === 'receive' ? $row->amount : 0;
            $withdraw = strtolower((string) $row->type) !== 'receive' ? $row->amount : 0;
            $final = $received - $withdraw;
        @endphp
        <tr>
            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>
            <td>{{ $row->type }}</td>
            <td>{{ $row->note ?? '' }}</td>

            <td class="text-end">
                @if($received > 0)
                    {{ number_format($received, 2) }}
                @else
                    -
                @endif
            </td>

            <td class="text-end">
                @if($withdraw > 0)
                    {{ number_format($withdraw, 2) }}
                @else
                    -
                @endif
            </td>

            <td class="text-end">
                {{ number_format($final, 2) }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center">No records found.</td>
        </tr>
    @endforelse
    </tbody>

    @if($data->count() > 0)
        <tfoot>
        <tr>
            <th colspan="3" class="text-end">Total</th>
            <th class="text-end">{{ number_format($receiveTotal, 2) }}</th>
            <th class="text-end">{{ number_format($withdrawTotal, 2) }}</th>
            <th class="text-end">{{ number_format($finalTotal, 2) }}</th>
        </tr>
        </tfoot>
    @endif
</table>
