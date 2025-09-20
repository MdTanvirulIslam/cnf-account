@php
    use Carbon\Carbon;
    $totalAmount = $data->sum('amount');
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
<hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />
<div class="invoice-info">
    <div>
        <strong>Expense of </strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F, Y') }} ||
        Category: {{ $category === 'all' ? 'All' : ($categories[$category] ?? '-') }}  ||
        Sub-Category: {{ $subCategory === 'all' ? 'All' : ($subCategories[$subCategory] ?? '-') }}
    </div>
    <div class="right"><strong> Date:</strong> {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d-m-Y') }}</div>
</div>

<table class="invoice-table" style="width: 100%; margin-top: 10px;">
    <thead>
    <tr>
        <th>SL</th>
        <th>Date</th>
        <th>Category</th>
        <th>Sub-Category</th>
        <th>Note</th>
        <th class="text-end">Amount</th>
    </tr>
    </thead>
    <tbody>
    @forelse($data as $row)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
            <td>{{ $row->category?->category ?? '-' }}</td>
            <td>{{ $row->subCategory?->category ?? '-' }}</td>
            <td>{{ $row->note ?? '' }}</td>
            <td class="text-end">{{ number_format($row->amount, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center">No records found</td>
        </tr>
    @endforelse
    </tbody>
    @if($data->count() > 0)
        <tfoot>
        <tr>
            <th colspan="5" class="text-end">Total</th>
            <th class="text-end">{{ number_format($totalAmount, 2) }}</th>
        </tr>
        </tfoot>
    @endif
</table>
