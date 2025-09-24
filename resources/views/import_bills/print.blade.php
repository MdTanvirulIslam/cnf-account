@extends('layouts.layout')

@section('styles')
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

        .invoice-table th, .invoice-table td {
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

        /* Print button styles */
        .print-button-container {
            text-align: right;
            margin-bottom: 20px;
        }

        .btn-print {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-print:hover {
            background-color: #0056b3;
        }

        /* Print styles */
        @media print {
            .print-button-container {
                display: none;
            }

            body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
            }

            .card {
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .layout-spacing, .layout-top-spacing {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card-body {
                padding: 0;
                margin: 0;
            }

            /* Ensure proper page breaks */
            .dc-report-table {
                page-break-inside: avoid;
            }

            /* Improve print quality */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Hide unnecessary elements during print */
        @media print {
            .navbar, .sidebar, .footer, .layout-spacing:not(:first-child) {
                display: none !important;
            }

            body * {
                visibility: hidden;
            }

            .card, .card * {
                visibility: visible;
            }

            .card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing ">
        <div class="col-xl-12 layout-top-spacing">
            <div class="print-button-container">
                <button class="btn-print" id="printButton">
                    🖨️ Print Bill
                </button>
            </div>

            <div class="card" id="invoiceCard">
                <div class="card-body">
                    <div class="company-header">
                        <h1>MULTI FABS LTD</h1>
                        <p>(SELF C&F AGENTS)</p>
                        <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
                    </div>
                    <hr style="border:none;border-top:1px solid #222;margin:10px 0 18px 0">

                    <div class="invoice-info">
                        <div><strong>BILL NO:</strong> {{ $bill->bill_no }}</div>
                        <div class="right">
                            <strong>DATE:</strong> {{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}</div>
                    </div>

                    <h3>SUB: CLEARING BILL FOR {{ strtoupper($bill->item ?? '') }}</h3>

                    <table class="info-table">
                        <tr>
                            <td class="info-key">L/C NO</td>
                            <td class="info-value">{{ $bill->lc_no }}</td>
                            <td class="info-key">Date</td>
                            <td class="info-value">{{ $bill->lc_date ? \Carbon\Carbon::parse($bill->lc_date)->format('d/m/Y') : '' }}</td>
                        </tr>
                        <tr>
                            <td class="info-key">ITEM</td>
                            <td class="info-value">{{ $bill->item }}</td>
                            <td class="info-key">VALUE</td>
                            <td class="info-value">{{ number_format($bill->value,2) }}</td>
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
                            <td class="info-value">{{ $bill->be_date ? \Carbon\Carbon::parse($bill->be_date)->format('d/m/Y') : '' }}</td>
                        </tr>
                    </table>

                    <table class="invoice-table">
                        <thead>
                        <tr>
                            <th class="center" style="width:5%">SL NO</th>
                            <th style="width:65%">DESCRIPTION</th>
                            <th style="width:15%">AMOUNT</th>
                            <th style="width:15%">REMARK</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bill->expenses as $i => $expense)
                            <tr>
                                <td class="center">{{ $i+1 }}</td>
                                <td>{{ strtoupper($expense->expense_type) }}</td>
                                <td class="right">{{ $expense->amount > 0 ? number_format($expense->amount,2) : '' }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2" class="right">TOTAL AMOUNT</td>
                            <td class="right">{{ number_format($total,2) }}</td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="footer-note">
                        <strong>INWARD: {{ strtoupper(convertToTakaWords($total)) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function printInvoice() {
            // Store the original body content and styles
            const originalBody = document.body.innerHTML;
            const originalStyles = document.querySelectorAll('style, link[rel="stylesheet"]');

            // Get the invoice card content
            const invoiceContent = document.getElementById('invoiceCard').outerHTML;

            // Create a new window for printing
            const printWindow = window.open('', '_blank', 'width=800,height=600');

            // Write the print content
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Bill - {{ $bill->bill_no }}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 20px;
                            background: white;
                        }
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
                        @media print {
                            body { margin: 0; padding: 15mm; }
                            .card { border: none; box-shadow: none; }
                        }
                    </style>
                </head>
                <body>
                    ${invoiceContent}
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() {
                                window.close();
                            }, 500);
                        };
                    <\/script>
                </body>
                </html>
            `);

            printWindow.document.close();
        }

        // Alternative method if popup is blocked
        function alternativePrint() {
            const printContent = document.getElementById('invoiceCard').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }

        // Keyboard shortcut
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printInvoice();
            }
        });

        // Attach event listener to print button
        document.addEventListener('DOMContentLoaded', function() {
            const printButton = document.getElementById('printButton');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    printInvoice();
                });
            }
        });

        // Fallback if first method fails
        window.printInvoice = printInvoice;
    </script>
@endsection
