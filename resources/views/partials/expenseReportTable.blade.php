@php
    use Carbon\Carbon;
    $totalAmount = $data->sum('amount');
@endphp

<!-- Hidden element to store totals for Excel export -->
<div id="expenseTotals"
     data-total-amount="{{ number_format($totalAmount, 2) }}"
     style="display: none;">
</div>

<div class="company-header">
    <h1>MULTI FABS LTD</h1>
    <p>(SELF C&F AGENTS)</p>
    <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
</div>
<hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />
<div class="invoice-info">
    <div>
        <strong>Expense of </strong> {{ $month ? \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F, Y') : 'All Time' }} ||
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
        <tr class="total-row">
            <!-- Web view: colspan 5 -->
            <td colspan="5" class="text-end web-total">
                <strong>Total</strong>
            </td>
            <!-- Print view: colspan 3 -->
            <td colspan="3" class="text-end print-total">
                <strong>Total</strong>
            </td>
            <td class="text-end"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
        </tr>
        </tfoot>
    @endif
</table>

<!-- Debug element to check if totals are calculated correctly -->
<script>
    console.log('Expense Report Totals:', {
        totalAmount: {{ $totalAmount }},
        formattedTotal: '{{ number_format($totalAmount, 2) }}',
        rowCount: {{ $data->count() }}
    });
</script>
