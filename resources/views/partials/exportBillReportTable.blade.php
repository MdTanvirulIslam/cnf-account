@if(count($exportBills) > 0)
    @foreach($exportBills as $processedBill)
        @php
            $bill = $processedBill['bill'];
            $allExpenses = $processedBill['expenses']; // This is now an associative array
            $billTotal = $processedBill['total'];

            $companyNameForPrint = $processedBill['companyNameForPrint'];
            $companyAddress = $processedBill['companyAddress'];

            // Convert total to words
            $totalInWords = convertToTakaWords($billTotal);
        @endphp

        <div style="page-break-after: always;"> <!-- Add page break for printing -->
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
                    margin-bottom: 20px;
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

                .footer-note {
                    margin-top: 40px;
                    font-size: 14px;
                    line-height: 1.6;
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

                @media print {
                    body {
                        margin: 0;
                        padding: 20px;
                        font-size: 12px;
                    }
                    .company-header h1 {
                        font-size: 20px;
                    }
                    .invoice-table {
                        font-size: 12px;
                    }
                    .footer-note {
                        margin-top: 30px;
                        font-size: 13px;
                    }
                    address {
                        font-style: normal !important;
                        font-size: 13px;
                        line-height: 1.5;
                        margin: 10px 0;
                    }

                    .clear {
                        margin: 10px 0;
                    }
                }
            </style>

            <div class="company-header">
                <h1>MULTI FABS LTD</h1>
                <p>(SELF C&F AGENTS)</p>
                <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
            </div>
            <hr style="border:none;border-top:1px solid #222;margin:10px 0 18px 0">
            <div class="invoice-info">
                <div><strong>BILL NO:</strong> {{ $bill->bill_no ?? 'N/A' }}</div>
                <div class="right"><strong>DATE:</strong> {{ $bill->bill_date?->format('d/m/Y') ?? 'N/A' }}</div>
            </div>
            <br>
            <div class="clear">
                <address>
                    TO,<br>
                    {{ $companyNameForPrint ?? 'N/A' }}<br>
                    {{ $companyAddress ?? 'N/A' }}
                </address>
            </div>
            <h3>SUB: FORWARDING BILL (AS PER INVOICE)</h3>

            <table class="info-table">
                <tr>
                    <td class="info-key">BUYER NAME</td>
                    <td class="info-value">{{ $bill->buyer->name ?? 'N/A' }}</td>
                    <td class="info-key">USD</td>
                    <td class="info-value">{{ number_format($bill->usd ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="info-key">Invoice No</td>
                    <td class="info-value">{{ $bill->invoice_no ?? 'N/A' }}</td>
                    <td class="info-key">Date</td>
                    <td class="info-value">{{ $bill->invoice_date?->format('d/m/Y') ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-key">B/E No</td>
                    <td class="info-value">{{ $bill->be_no ?? 'N/A' }}</td>
                    <td class="info-key">Date</td>
                    <td class="info-value">{{ $bill->be_date?->format('d/m/Y') ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-key">Total Qty</td>
                    <td class="info-value">{{ $bill->total_qty ?? 'N/A' }}</td>
                    <td class="info-key">Qty Pcs</td>
                    <td class="info-value">{{ $bill->qty_pcs ?? 'N/A' }}</td>
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
                @php $index = 1; @endphp
                @foreach($allExpenses as $expenseType => $amount)
                    <tr>
                        <td class="center">{{ $index++ }}</td>
                        <td class="left">{{ $expenseType }}</td>
                        <td class="center">
                            @if($amount !== null && $amount > 0)
                                {{ number_format($amount, 2) }}
                            @else
                                0.00
                            @endif
                        </td>
                        <td class="center"></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="center"><strong>TOTAL AMOUNT</strong></td>
                    <td class="center"><strong>{{ number_format($billTotal, 2) }}</strong></td>
                    <td class="center"></td>
                </tr>
                </tfoot>
            </table>
            <div class="footer-note">
                <strong>INWARD: {{ strtoupper($totalInWords) }}</strong> <br/><br/>

                THANKING YOU

                <br/><br/><br/>
                {{ strtoupper($companyNameForPrint) }}
            </div>
        </div> <!-- End of page break div -->

        @if(!$loop->last)
            <hr style="border:none;border-top:2px dashed #333;margin:30px 0;">
        @endif
    @endforeach
@else
    <div class="alert alert-info text-center">
        No export bills found for the selected criteria.
    </div>
@endif
