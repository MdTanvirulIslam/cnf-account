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
        <strong>Employee Cash Expense : </strong>
        @if(isset($selectedMonth))
            from {{ \Carbon\Carbon::parse($selectedMonth)->startOfMonth()->format('d-m-Y') }}
            to {{ \Carbon\Carbon::parse($selectedMonth)->endOfMonth()->format('d-m-Y') }}
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
        <th>ID</th>
        <th>Employee Name</th>
        <th>Department</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Payment Type</th>
        <th>Note</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalReceive = 0;
        $totalReturn = 0;
        $sl = 1;
    @endphp

    @forelse($transactions as $row)
        @php
            // Debug: Check each row
            // \Log::info('Row: ' . json_encode($row));

            if(isset($row->type) && strtolower($row->type) == 'receive') {
                $totalReceive += $row->amount;
            } elseif(isset($row->type) && strtolower($row->type) == 'return') {
                $totalReturn += $row->amount;
            }
        @endphp
        <tr>
            <td>{{ $sl++ }}</td>
            <td>{{ $row->id }}</td>
            <td>{{ $row->employee_name ?? 'N/A' }}</td>
            <td>{{ $row->department ?? 'N/A' }}</td>
            <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
            <td class="right">{{ number_format($row->amount, 2) }}</td>
            <td>{{ ucfirst($row->type) }}</td>
            <td>{{ $row->note }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="center">No Data Found</td>
        </tr>
    @endforelse
    </tbody>

    @if($transactions->count() > 0)
        <tfoot>
        <tr class="total-row">
            <td colspan="5" class="right"><strong>Total Receive:</strong></td>
            <td class="right"><strong>{{ number_format($totalReceive, 2) }}</strong></td>
            <td colspan="3"></td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="right"><strong>Total Return:</strong></td>
            <td class="right"><strong>{{ number_format($totalReturn, 2) }}</strong></td>
            <td colspan="3"></td>
        </tr>
        <tr class="total-row">
            <td colspan="5" class="right"><strong>Net Amount:</strong></td>
            <td class="right"><strong>{{ number_format($totalReceive - $totalReturn, 2) }}</strong></td>
            <td colspan="3"></td>
        </tr>
        </tfoot>
    @endif
</table>

@if($transactions->count() > 0)
    <div class="footer-note">
        <p><strong>Summary:</strong>
            {{ $transactions->count() }} individual transaction(s) found |
            Receive: {{ number_format($totalReceive, 2) }} |
            Return: {{ number_format($totalReturn, 2) }} |
            Net: {{ number_format($totalReceive - $totalReturn, 2) }}
        </p>
    </div>
@endif

<!-- Debug information (remove in production) -->
<div style="display: none;">
    <h4>Debug Information:</h4>
    <p>Total Rows: {{ $transactions->count() }}</p>
    <p>First few transaction IDs:
        @foreach($transactions->take(5) as $t)
            {{ $t->id }},
        @endforeach
    </p>
</div>
