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
@endphp

<div class="invoice-info">
    <div>
        <strong>Employee Cash Summary : </strong>
        @if(isset($selectedMonth))
            {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}
        @else
            All Time
        @endif

        @if($hasFilters)
            <br><small style="color: #666;">
                @if(request('department')) Department: {{ request('department') }} @endif
                @if(request('paymentType')) | Type: {{ ucfirst(request('paymentType')) }} @endif
            </small>
        @endif
    </div>
    <div class="right"><strong> Date:</strong> {{ now()->format('d/m/Y') }}</div>
</div>

<table class="invoice-table" style="width: 100%; margin-top: 10px;">
    <thead>
    <tr>
        <th>SL</th>
        <th>Employee Name</th>
        <th>Department</th>
        <th>Payment Type</th>
        <th>Total Amount</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalReceive = 0;
        $totalReturn = 0;
        $totalTransactions = 0;
        $sl = 1;
    @endphp

    @forelse($groupedTransactions as $row)
        @php
            if(isset($row->type) && strtolower($row->type) == 'receive') {
                $totalReceive += $row->total_amount;
            } elseif(isset($row->type) && strtolower($row->type) == 'return') {
                $totalReturn += $row->total_amount;
            }
            $totalTransactions += $row->transaction_count;
        @endphp
        <tr>
            <td>{{ $sl++ }}</td>
            <td>{{ $row->employee_name ?? 'N/A' }}</td>
            <td>{{ $row->department ?? 'N/A' }}</td>
            <td>{{ ucfirst($row->type) }}</td>
            <td class="right">{{ number_format($row->total_amount, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="center">No Data Found</td>
        </tr>
    @endforelse
    </tbody>

    @if($groupedTransactions->count() > 0)
        <tfoot>

        <tr class="total-row">
            <td colspan="3" class="right"><strong>Total Receive:</strong></td>
            <td></td>
            <td class="right"><strong>{{ number_format($totalReceive, 2) }}</strong></td>
        </tr>
        <tr class="total-row">
            <td colspan="3" class="right"><strong>Total Return:</strong></td>
            <td></td>
            <td class="right"><strong>{{ number_format($totalReturn, 2) }}</strong></td>
        </tr>
        <tr class="total-row">
            <td colspan="3" class="right"><strong>Net Amount:</strong></td>
            <td></td>
            <td class="right"><strong>{{ number_format($totalReceive - $totalReturn, 2) }}</strong></td>
        </tr>
        </tfoot>
    @endif
</table>

@if($groupedTransactions->count() > 0)
    <div class="footer-note">
        <p><strong>Summary:</strong>
            {{ $groupedTransactions->count() }} employee transaction group(s) found |
            Total Transactions: {{ $totalTransactions }} |
            Receive: {{ number_format($totalReceive, 2) }} |
            Return: {{ number_format($totalReturn, 2) }} |
            Net: {{ number_format($totalReceive - $totalReturn, 2) }}
        </p>
    </div>
@endif

<!-- Debug information (remove in production) -->
<div style="display: none;">
    <h4>Debug Information:</h4>
    <p>Total Groups: {{ $groupedTransactions->count() }}</p>
</div>
