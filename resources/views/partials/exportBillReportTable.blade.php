@php $grandTotal = 0; @endphp

@foreach($exportBills as $bill)
    @php
        $billTotal = $bill->expenses->sum('amount');
        $grandTotal += $billTotal;
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
        }
        .right {
            text-align: right;
        }
        .center {
            text-align: center !important;
        }
        .left {
            text-align: left !important;
        }
        .total-row td {
            font-weight: bold;
            background: #f9f9f9;
        }

        .grand-total {
            margin-top: 20px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        /* Web view specific alignment */
        .invoice-table th:nth-child(1),
        .invoice-table td:nth-child(1) {
            text-align: center;
            width: 5%;
        }
        .invoice-table th:nth-child(2),
        .invoice-table td:nth-child(2) {
            text-align: left;
            width: 65%;
        }
        .invoice-table th:nth-child(3),
        .invoice-table td:nth-child(3),
        .invoice-table th:nth-child(4),
        .invoice-table td:nth-child(4) {
            text-align: center;
            width: 15%;
        }
    </style>

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

    <h3>SUB: FORWARDING BILL (AS PER INVOICE)</h3>

    <table class="info-table">
        <tr>
            <td class="info-key">BUYER NAME</td>
            <td class="info-value">{{ $bill->buyer->name }}</td>
            <td class="info-key">USD</td>
            <td class="info-value">{{ number_format($bill->usd, 2) }}</td>
        </tr>
        <tr>
            <td class="info-key">Invoice No</td>
            <td class="info-value">{{ $bill->invoice_no }}</td>
            <td class="info-key">Date</td>
            <td class="info-value">{{ $bill->invoice_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="info-key">B/E No</td>
            <td class="info-value">{{ $bill->be_no }}</td>
            <td class="info-key">Date</td>
            <td class="info-value">{{ $bill->be_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="info-key">Total Qty</td>
            <td class="info-value">{{ $bill->total_qty }}</td>
            <td class="info-key">Qty Pcs</td>
            <td class="info-value">{{ $bill->qty_pcs }}</td>
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

@if(count($exportBills) > 0)
    <div class="grand-total">
        <p><strong>GRAND TOTAL: {{ number_format($grandTotal, 2) }} TAKA ONLY</strong></p>
    </div>
@endif
