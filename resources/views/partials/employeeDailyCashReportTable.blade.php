<div class="company-header">
    <h1>MULTI FABS LTD</h1>
    <p>(SELF C&F AGENTS)</p>
    <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
</div>
<hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />

@php
    $currentFilters = request()->all();
    $hasFilters = !empty(array_filter($currentFilters, function($value) {
        return $value !== null && $value !== '';
    }));

    $totalReceive = 0;
    $totalReturn = 0;
    $totalFinal = 0;
    $totalTransactions = 0;
@endphp

<div class="invoice-info">
    <div>
        <strong>Employee Daily Cash Summary : </strong>
        {{ $startDate->format('d-m-Y') }} to {{ $endDate->format('d-m-Y') }}

        @if($hasFilters)
            <br><small style="color: #666;">
                @if(request('department')) Department: {{ request('department') }} @endif
                @if(request('employee_id'))
                    | Employee: {{ \App\Models\Employee::find(request('employee_id'))->name ?? 'Selected Employee' }}
                @endif
                @if(request('paymentType')) | Type: {{ ucfirst(request('paymentType')) }} @endif
            </small>
        @endif
    </div>
    <div class="right"><strong> Report Date:</strong> {{ now()->format('d/m/Y') }}</div>
</div>

<table class="invoice-table">
    <thead>
    <tr style="page-break-inside: avoid; break-inside: avoid;">
        <th>SL</th>
        <th>Date</th>
        <th>Employee Name</th>
        <th>Department</th>
        <th class="right">Receive Amount</th>
        <th class="right">Return Amount</th>
        <th class="right">Final Amount</th>
    </tr>
    </thead>
    <tbody>
    @php
        $sl = 1;
    @endphp

    @forelse($dailyTransactions as $row)
        @php
            $finalAmount = $row->receive_amount - $row->return_amount;
            $totalReceive += $row->receive_amount;
            $totalReturn += $row->return_amount;
            $totalFinal += $finalAmount;
            $totalTransactions += $row->transaction_count;
        @endphp

        <tr style="page-break-inside: avoid; break-inside: avoid;">
            <td>{{ $sl++ }}</td>
            <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
            <td>{{ $row->employee_name ?? 'N/A' }}</td>
            <td>{{ $row->department ?? 'N/A' }}</td>
            <td class="right">{{ number_format($row->receive_amount, 2) }}</td>
            <td class="right">{{ number_format($row->return_amount, 2) }}</td>
            <td class="right">{{ number_format($finalAmount, 2) }}</td>
        </tr>
    @empty
        <tr style="page-break-inside: avoid; break-inside: avoid;">
            <td colspan="7" class="center">No Data Found for the selected date range and filters</td>
        </tr>
    @endforelse
    </tbody>

    @if($dailyTransactions->count() > 0)
        <tfoot style="display: table-footer-group;">
        <tr class="total-row" style="page-break-inside: avoid; break-inside: avoid; page-break-before: avoid; break-before: avoid;">
            <td colspan="4" class="right"><strong>Grand Total:</strong></td>
            <td class="right"><strong>{{ number_format($totalReceive, 2) }}</strong></td>
            <td class="right"><strong>{{ number_format($totalReturn, 2) }}</strong></td>
            <td class="right"><strong>{{ number_format($totalFinal, 2) }}</strong></td>
        </tr>
        </tfoot>
    @endif
</table>

@if($dailyTransactions->count() > 0)
    <div class="footer-note" style="page-break-inside: avoid; break-inside: avoid;">
        <p><strong>Summary:</strong>
            {{ $dailyTransactions->count() }} daily record(s) found |
            Total Receive: {{ number_format($totalReceive, 2) }} |
            Total Return: {{ number_format($totalReturn, 2) }} |
            Net Final Amount: {{ number_format($totalFinal, 2) }}
        </p>
    </div>
@endif
