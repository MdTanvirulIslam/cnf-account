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

        .print-button-container {
            margin-bottom: 15px;
            text-align: right;
        }
        .btn-print {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn-print:hover {
            background: #0056b3;
        }
        .btn-excel {
            padding: 10px 20px;
            background: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s;
            margin-left: 10px;
        }
        .btn-excel:hover {
            background: #218838;
        }

        /* Custom styles for address to remove italic */
        .company-address {
            font-style: normal !important;
            font-weight: normal;
            line-height: 1.4;
            margin-top: 15px;
        }

        /* Override browser default address styles */
        address {
            font-style: normal !important;
            font-weight: normal;
            line-height: 1.4;
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
                margin: 0;
                padding: 0;
            }

            #invoiceCard, #invoiceCard * {
                visibility: visible;
            }

            #invoiceCard {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }

            .print-button-container {
                display: none;
            }

            .company-header h1 {
                font-size: 24px;
            }

            .invoice-table th {
                background-color: #f4f4f4 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .total-row td {
                background: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Ensure address is not italic in print */
            address, .company-address {
                font-style: normal !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="print-button-container">
                <button class="btn-print" onclick="printInvoice()">
                    🖨️ Print Bill
                </button>
                <button class="btn-excel" onclick="exportToExcel()">
                    📊 Export to Excel
                </button>
            </div>

            <div class="card" id="invoiceCard">
                <div class="card-body">
                    <div class="col-xl-12 layout-top-spacing dc-report-table">
                        <div class="company-header">
                            <h1>{{ $companyNameForPrint }}</h1>
                            <p>(SELF C&F AGENTS)</p>
                            @if($bill->company_name == 'EMS APPARELS LTD')
                                <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
                            @else
                                <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
                            @endif
                        </div>
                        <hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />

                        <div class="invoice-info">
                            <div><strong>BILL NO:</strong> {{ $bill->bill_no }}</div>
                            <div class="right"><strong>DATE:</strong> {{ $bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') : '' }}</div>
                        </div>

                        {{-- Use div instead of address tag to avoid italic styling --}}
                        <div class="company-address">
                            <br>
                            TO,<br>
                            {{ $companyNameForPrint }}<br>
                            {{ $companyAddress }}
                        </div>

                        <h3>SUB: FORWARDING BILL (AS PER INVOICE)</h3>

                        <table class="info-table">
                            <tr>
                                <td class="info-key">Buyer Name</td>
                                <td class="info-value">{{ $bill->buyer->name ?? '' }}</td>
                                <td class="info-key">USD</td>
                                <td class="info-value">{{ number_format($bill->usd, 2) }} $</td>
                            </tr>
                            <tr>
                                <td class="info-key">Invoice No</td>
                                <td class="info-value">{{ $bill->invoice_no }}</td>
                                <td class="info-key">Date</td>
                                <td class="info-value">{{ $bill->invoice_date ? \Carbon\Carbon::parse($bill->invoice_date)->format('d/m/Y') : '' }}</td>
                            </tr>
                            <tr>
                                <td class="info-key">B/E No</td>
                                <td class="info-value">{{ $bill->be_no }}</td>
                                <td class="info-key">Date</td>
                                <td class="info-value">{{ $bill->be_date ? \Carbon\Carbon::parse($bill->be_date)->format('d/m/Y') : '' }}</td>
                            </tr>
                            <tr>
                                <td class="info-key">Total CTN</td>
                                <td class="info-value">{{ $bill->total_qty }} CTNS</td>
                                <td class="info-key">Qty PCS</td>
                                <td class="info-value">{{ $bill->qty_pcs }} PCS</td>
                            </tr>
                        </table>

                        <table class="invoice-table">
                            <thead>
                            <tr>
                                <th class="center" style="width: 5%;">SL NO</th>
                                <th style="width: 63%;">DESCRIPTION</th>
                                <th style="width: 15%;">AMOUNT</th>
                                <th style="width: 15%;">REMARK</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($expenseTypes as $index => $type)
                                <tr>
                                    <td class="center">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ strtoupper($type) }}</td>
                                    <td class="right">{{ $expenses[$type] }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td colspan="2" class="right">TOTAL AMOUNT</td>
                                <td class="right">{{ number_format($total, 2) }}</td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="footer-note">
                            <strong>INWARD: {{ strtoupper(convertToTakaWords($total)) }}</strong> <br/><br/>

                            THANKING YOU

                            <br/><br/><br/>
                            {{ strtoupper($companyNameForPrint) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
                    width: 50%;
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
                    width: 20%;
                }
                .info-value {
                    width: 30%;
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
                /* Ensure address is not italic */
                .company-address, address {
                    font-style: normal !important;
                    font-weight: normal;
                    line-height: 1.4;
                }
                @media print {
                    body { margin: 0; padding: 15mm; }
                    .card { border: none; box-shadow: none; }
                    .company-address, address {
                        font-style: normal !important;
                    }
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

        function exportToExcel() {
            console.log('Export to Excel clicked');

            // Define company details
            const companyName = "{{ $companyNameForPrint }}";
            const companyAddress = "{{ $companyAddress }}";

            try {
                // Create HTML table with inline styling
                const tableHTML = `
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 14px;">
        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Company Header Section -->
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px; width: 90%;">
                ${companyName} <br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Bill No and Date -->
        <tr>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold; width: 30%;">BILL NO: {{ $bill->bill_no }}</td>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold; width: 60%;">DATE: {{ $bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') : '' }}</td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Sub Title -->
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 8px; text-align: left; font-weight: bold; font-size: 12px;">SUB: FORWARDING BILL (AS PER INVOICE)</td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Company Address (with normal font style) -->
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 5px; text-align: left; font-style: normal;">
                TO,<br/>
                ${companyName}<br/>
                ${companyAddress}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Buyer Information Table -->
        <tr>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: left;">Buyer Name : {{ $bill->buyer->name ?? '' }}</td>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">USD : {{ number_format($bill->usd, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: left;">Invoice No : {{ $bill->invoice_no }}</td>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">Date : {{ $bill->invoice_date ? \Carbon\Carbon::parse($bill->invoice_date)->format('d/m/Y') : '' }} </td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: left;">B/E No : {{ $bill->be_no }}</td>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">Date : {{ $bill->be_date ? \Carbon\Carbon::parse($bill->be_date)->format('d/m/Y') : '' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: left;">Total CTNS : {{ $bill->total_qty }}</td>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">Qty PCS : {{ $bill->qty_pcs }}</td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Expense Table Header -->
        <tr>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">SL NO</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 50%;">DESCRIPTION</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">AMOUNT</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">REMARK</td>
        </tr>

        <!-- Expense Rows -->
        @foreach($expenseTypes as $index => $type)
                <tr>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td style="border: 1px solid #000000; padding: 5px; text-align: left;">{{ strtoupper($type) }}</td>
                <td style="border: 1px solid #000000; padding: 5px; text-align: center;">{{ $expenses[$type] ?? 0 }}</td>
                <td style="border: 1px solid #000000; padding: 5px; text-align: center;"></td>
            </tr>
        @endforeach

                <!-- Total Row -->
                <tr>
                    <td style="border: 1px solid #000000; padding: 5px;"></td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">TOTAL AMOUNT</td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">{{ $total }}</td>
            <td style="border: 1px solid #000000; padding: 5px;"></td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Inward Row -->
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 8px; text-align: left; font-weight: bold;">INWARD: {{ strtoupper(convertToTakaWords($total)) }}</td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Footer -->
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 8px; text-align: center;">
                THANKING YOU<br/><br/>
                ${companyName.toUpperCase()}
            </td>
        </tr>
    </table>
</body>
</html>
        `;

                // Create a Blob and download
                const blob = new Blob([tableHTML], {
                    type: 'application/vnd.ms-excel'
                });

                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Export_Bill_{{ $bill->bill_no }}_{{ $companyNameForPrint }}.xls`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

                console.log('Excel file generated successfully');

            } catch (error) {
                console.error('Error generating Excel file:', error);
                alert('Error generating Excel file: ' + error.message);
            }
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

        // Fallback if first method fails
        window.printInvoice = printInvoice;
        window.exportToExcel = exportToExcel;
    </script>
@endsection
