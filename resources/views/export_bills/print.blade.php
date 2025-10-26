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
                            <h1>MULTI FABS LTD</h1>
                            <p>(SELF C&F AGENTS)</p>
                            <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
                        </div>
                        <hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />

                        <div class="invoice-info">
                            <div><strong>BILL NO:</strong> {{ $bill->bill_no }}</div>
                            <div class="right"><strong>DATE:</strong> {{ $bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') : '' }}</div>
                        </div>

                        <h3>SUB: FORWARDING BILL (AS PER INVOICE)</h3>

                        <table class="info-table">
                            <tr>
                                <td class="info-key">Buyer Name</td>
                                <td class="info-value">{{ $bill->buyer->name ?? '' }}</td>
                                <td class="info-key">USD</td>
                                <td class="info-value">{{ number_format($bill->usd, 2) }}</td>
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
                                <td class="info-value">{{ $bill->total_qty }}</td>
                                <td class="info-key">Qty Pcs</td>
                                <td class="info-value">{{ $bill->qty_pcs }}</td>
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
                                    <td class="right">{{ number_format($expenses[$type] ?? 0, 2) }}</td>
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
                            <strong>INWARD: {{ strtoupper(convertToTakaWords($total)) }}</strong>
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

        function exportToExcel() {
            console.log('Export to Excel clicked'); // Debug log

            try {
                // Create workbook
                const workbook = XLSX.utils.book_new();

                // Prepare data array with exact structure
                const excelData = [
                    // Company Header
                    ["MULTI FABS LTD"],
                    ["(SELF C&F AGENTS)"],
                    ["314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG."],
                    [""],
                    ["", "", "", "", "BILL NO: {{ $bill->bill_no }}", "DATE: {{ $bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') : '' }}"],
                    [""],
                    ["SUB: FORWARDING BILL (AS PER INVOICE)"],
                    [""],

                    // Buyer Information Table
                    ["Buyer Name", "{{ $bill->buyer->name ?? '' }}", "", "", "USD", "{{ number_format($bill->usd, 2) }}"],
                    ["Invoice No", "{{ $bill->invoice_no }}", "", "", "Date", "{{ $bill->invoice_date ? \Carbon\Carbon::parse($bill->invoice_date)->format('d/m/Y') : '' }}"],
                    ["B/E No", "{{ $bill->be_no }}", "", "", "Date", "{{ $bill->be_date ? \Carbon\Carbon::parse($bill->be_date)->format('d/m/Y') : '' }}"],
                    ["Total CTN", "{{ $bill->total_qty }}", "", "", "Qty Pcs", "{{ $bill->qty_pcs }}"],
                    [""],

                    // Expense Table Header
                    ["SL NO", "DESCRIPTION", "AMOUNT", "REMARK"],
                ];

                // Add expense rows
                @foreach($expenseTypes as $index => $type)
                excelData.push([
                    "{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}",
                    "{{ strtoupper($type) }}",
                    {{ $expenses[$type] ?? 0 }},
                    ""
                ]);
                @endforeach

                // Add total row
                excelData.push(["", "TOTAL AMOUNT", {{ $total }}, ""]);
                excelData.push([""]);
                excelData.push(["INWARD: {{ strtoupper(convertToTakaWords($total)) }}"]);

                // Create worksheet
                const worksheet = XLSX.utils.aoa_to_sheet(excelData);

                // Apply basic styling
                const range = XLSX.utils.decode_range(worksheet['!ref']);

                for(let R = range.s.r; R <= range.e.r; ++R) {
                    for(let C = range.s.c; C <= range.e.c; ++C) {
                        const cell_address = {c:C, r:R};
                        const cell_ref = XLSX.utils.encode_cell(cell_address);

                        if(!worksheet[cell_ref]) continue;

                        // Company header styling
                        if(R === 0) {
                            worksheet[cell_ref].s = {
                                font: { bold: true, sz: 16 },
                                alignment: { horizontal: "center" }
                            };
                        }
                        else if(R === 1 || R === 2) {
                            worksheet[cell_ref].s = {
                                font: { sz: 11 },
                                alignment: { horizontal: "center" }
                            };
                        }
                        // Bill info
                        else if(R === 4 && (C === 4 || C === 5)) {
                            worksheet[cell_ref].s = {
                                font: { bold: true }
                            };
                        }
                        // SUB title
                        else if(R === 6) {
                            worksheet[cell_ref].s = {
                                font: { bold: true, sz: 12 },
                                alignment: { horizontal: "center" }
                            };
                        }
                        // Table headers
                        else if(R === 12) {
                            worksheet[cell_ref].s = {
                                font: { bold: true },
                                alignment: { horizontal: "center" },
                                border: {
                                    top: { style: "thin" },
                                    left: { style: "thin" },
                                    bottom: { style: "thin" },
                                    right: { style: "thin" }
                                }
                            };
                        }
                        // Expense rows
                        else if(R >= 13 && R < 13 + {{ count($expenseTypes) }}) {
                            if(C === 0) { // SL NO
                                worksheet[cell_ref].s = {
                                    alignment: { horizontal: "center" },
                                    border: {
                                        left: { style: "thin" },
                                        bottom: { style: "thin" },
                                        right: { style: "thin" }
                                    }
                                };
                            }
                            else if(C === 2) { // AMOUNT
                                worksheet[cell_ref].s = {
                                    alignment: { horizontal: "right" },
                                    border: {
                                        bottom: { style: "thin" },
                                        right: { style: "thin" }
                                    }
                                };
                            }
                            else { // DESCRIPTION and REMARK
                                worksheet[cell_ref].s = {
                                    border: {
                                        bottom: { style: "thin" },
                                        right: { style: "thin" }
                                    }
                                };
                            }
                        }
                        // Total row
                        else if(R === 13 + {{ count($expenseTypes) }}) {
                            if(C === 1 || C === 2) {
                                worksheet[cell_ref].s = {
                                    font: { bold: true },
                                    border: {
                                        top: { style: "thin" },
                                        left: { style: "thin" },
                                        bottom: { style: "thin" },
                                        right: { style: "thin" }
                                    }
                                };
                            }
                            if(C === 2) {
                                worksheet[cell_ref].s.alignment = { horizontal: "right" };
                            }
                        }
                        // INWARD row
                        else if(R === 15 + {{ count($expenseTypes) }}) {
                            worksheet[cell_ref].s = {
                                font: { bold: true }
                            };
                        }
                    }
                }

                // Set column widths
                worksheet['!cols'] = [
                    { wch: 8 },   // SL NO
                    { wch: 50 },  // DESCRIPTION
                    { wch: 15 },  // AMOUNT
                    { wch: 15 }   // REMARK
                ];

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(workbook, worksheet, "Export Bill");

                // Generate and download Excel file
                const fileName = `Export_Bill_{{ $bill->bill_no }}.xlsx`;
                XLSX.writeFile(workbook, fileName);

                console.log('Excel file generated successfully'); // Debug log

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
