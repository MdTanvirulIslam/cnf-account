@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Import Bill Summary</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 form-group">
                            <label for="month" class="form-label">Select Month</label>
                            <input type="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                        </div>

                        <!-- ADDED: Company Filter -->
                        <div class="col-md-3 form-group">
                            <label for="company" class="form-label">Select Company</label>
                            <select name="company" id="company" class="form-control form-control-sm">
                                @foreach($companyNames as $key => $name)
                                    <option value="{{ $key }}" {{ $company == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 form-group d-flex align-items-end">
                            <button type="button" id="resetBtn" class="btn btn-primary btn-sm me-1">
                                <i class="fas fa-filter"></i> Reset
                            </button>
                            <button type="button" id="printBtn" class="btn btn-secondary btn-sm me-1">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button type="button" id="excelBtn" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing dc-report-table" id="reportTable">
            @include('partials.importBillSummaryTable', ['bills' => $bills, 'month' => $month, 'company' => $company])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            // Store the original values
            const originalMonth = "{{ $month }}";
            const originalCompany = "{{ $company }}";

            // Month change event
            $('#month').on('change', function(){
                loadReportData($(this).val(), $('#company').val());
            });

            // ADDED: Company change event
            $('#company').on('change', function(){
                loadReportData($('#month').val(), $(this).val());
            });

            // Reset button click event
            $('#resetBtn').on('click', function() {
                // Reset to original values
                $('#month').val(originalMonth);
                $('#company').val(originalCompany);
                // Reload report with original values
                loadReportData(originalMonth, originalCompany);
            });

            // Print button click event
            $('#printBtn').on('click', function() {
                printReport();
            });

            // Excel Export button click event
            $('#excelBtn').on('click', function() {
                exportToExcel();
            });

            // Function to load report data - UPDATED TO INCLUDE COMPANY
            function loadReportData(month, company) {
                $.ajax({
                    url: "{{ route('import.bill.summary.report') }}",
                    type: "GET",
                    data: {
                        month: month,
                        company: company
                    },
                    beforeSend: function() {
                        $('#reportTable').html('<div class="text-center p-3">Loading...</div>');
                    },
                    success: function(response){
                        $('#reportTable').html(response);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#reportTable').html('<div class="text-danger p-3">Failed to load data. Please try again.</div>');
                    }
                });
            }

            // Function to print the report - ONLY UPDATE HEADER TO INCLUDE COMPANY
            function printReport() {
                // Create a new window for printing
                var printWindow = window.open('', '_blank');

                // Get the HTML content of the report table
                var reportContent = document.getElementById('reportTable').innerHTML;

                // Get the month and company values for the report title
                var monthValue = document.getElementById('month').value;
                var companySelect = document.getElementById('company');
                var companyName = companySelect.options[companySelect.selectedIndex].text;
                var formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All Time';

                // Write the print document - USING YOUR ORIGINAL STYLING
                printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
    <title>Import Bill Summary - ${formattedMonth}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #000;
        }

        .print-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .print-header h2 {
            margin: 0;
            color: #000;
        }

        .print-header p {
            margin: 5px 0 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        /* CRITICAL: Prevent grand total from repeating */
        tfoot {
            display: table-row-group !important;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
        }

        .text-center, .center {
            text-align: center;
        }

        .text-right, .right {
            text-align: right;
        }

        .text-left, .left {
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background-color: #e9e9e9;
        }

        /* Column widths - EXACTLY AS YOUR ORIGINAL */
        th:nth-child(1), td:nth-child(1) { width: 8%; }
        th:nth-child(2), td:nth-child(2) { width: 6%; }
        th:nth-child(3), td:nth-child(3) { width: 5%; }
        th:nth-child(4), td:nth-child(4) { width: 7%; }
        th:nth-child(5), td:nth-child(5) { width: 5%; }
        th:nth-child(6), td:nth-child(6) { width: 10%; }
        th:nth-child(7), td:nth-child(7) { width: 5%; }
        th:nth-child(8), td:nth-child(8) { width: 7%; }
        th:nth-child(9), td:nth-child(9) { width: 8%; }
        th:nth-child(10), td:nth-child(10) { width: 7%; }
        th:nth-child(11), td:nth-child(11) { width: 7%; }
        th:nth-child(12), td:nth-child(12) { width: 7%; }
        th:nth-child(13), td:nth-child(13) { width: 6%; }
        th:nth-child(14), td:nth-child(14) { width: 5%; }
        th:nth-child(15), td:nth-child(15) { width: 5%; }
        th:nth-child(16), td:nth-child(16) { width: 5%; }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                margin: 0;
                padding: 10px;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: landscape;
                margin: 8mm;
            }

            /* CRITICAL: Prevent grand total from repeating on every page */
            tfoot {
                display: table-row-group !important;
            }

            thead {
                display: table-header-group !important;
            }

            table {
                width: 100% !important;
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            th, td {
                font-size: 8px !important;
                padding: 3px 2px !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                overflow: visible !important;
                text-overflow: clip !important;
            }

            /* Ensure column widths in print - EXACTLY AS YOUR ORIGINAL */
            th:nth-child(1), td:nth-child(1) { width: 8% !important; }
            th:nth-child(2), td:nth-child(2) { width: 6% !important; }
            th:nth-child(3), td:nth-child(3) { width: 5% !important; }
            th:nth-child(4), td:nth-child(4) { width: 7% !important; }
            th:nth-child(5), td:nth-child(5) { width: 5% !important; }
            th:nth-child(6), td:nth-child(6) { width: 10% !important; }
            th:nth-child(7), td:nth-child(7) { width: 5% !important; }
            th:nth-child(8), td:nth-child(8) { width: 7% !important; }
            th:nth-child(9), td:nth-child(9) { width: 8% !important; }
            th:nth-child(10), td:nth-child(10) { width: 7% !important; }
            th:nth-child(11), td:nth-child(11) { width: 7% !important; }
            th:nth-child(12), td:nth-child(12) { width: 7% !important; }
            th:nth-child(13), td:nth-child(13) { width: 6% !important; }
            th:nth-child(14), td:nth-child(14) { width: 5% !important; }
            th:nth-child(15), td:nth-child(15) { width: 5% !important; }
            th:nth-child(16), td:nth-child(16) { width: 5% !important; }
        }
    </style>
</head>
<body>
    <div>${reportContent}</div>
</body>
</html>
`);

                printWindow.document.close();

                // Wait for the content to load before printing
                printWindow.onload = function() {
                    printWindow.focus();
                    setTimeout(function() {
                        printWindow.print();
                        // printWindow.close(); // Uncomment to automatically close after printing
                    }, 250);
                };
            }

            // Function to export to Excel - UPDATED TO INCLUDE COMPANY
            function exportToExcel() {
                try {
                    // Get the month and company values for the report title
                    const monthValue = $('#month').val();
                    const companySelect = document.getElementById('company');
                    const companyName = companySelect.options[companySelect.selectedIndex].text;
                    const formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All Time';
                    const currentDate = new Date().toLocaleDateString('en-GB');

                    // Get the table data from the current view
                    const table = document.querySelector('#reportTable table');
                    if (!table) {
                        alert('No data found to export.');
                        return;
                    }

                    // Create HTML table with inline styling for Excel - USING YOUR ORIGINAL STYLING
                    const tableHTML = `
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 14px;">
        <!-- Company Header Section - UPDATED TO SHOW SELECTED COMPANY -->
        <tr>
            <td colspan="16" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px;">
                ${companyName}<br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="16" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Report Info -->
        <tr>
            <td colspan="10" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold;">
                IMPORT BILL STATEMENT : ${formattedMonth}
            </td>
            <td colspan="6" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">
                Date: ${currentDate}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="16" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Table Header - EXACTLY YOUR ORIGINAL -->
        <tr>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">L/C NO.</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">B/E</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">BE DT</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">BILL NO.</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">BILL DT</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 12%;">GOODS NAME</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 5%;">QNTY</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">NET WEIGHT</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">MONTH</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">VALUE</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">PORT BILL</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">TOTAL BILL AMOUNT</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">DF VAT</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">DOC FEE</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">SCAN FEE</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">ITC</th>
        </tr>

        <!-- Table Rows -->
        ${getTableRowsHTML()}

        <!-- Total Row -->
        ${getTotalRowHTML()}
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

                    const fileName = `Import_Bill_Summary_${formattedMonth.replace(/\s+/g, '_')}.xls`;
                    a.download = fileName;

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

            // Helper function to get table rows HTML
            function getTableRowsHTML() {
                const rows = document.querySelectorAll('#reportTable table tbody tr');
                let rowsHTML = '';

                // Check if there are any data rows
                if (rows.length === 0) {
                    const companySelect = document.getElementById('company');
                    const companyName = companySelect.options[companySelect.selectedIndex].text;
                    return `
        <tr>
            <td colspan="16" style="border: 1px solid #000000; padding: 5px; text-align: center;">No records found.</td>
        </tr>
                    `;
                }

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 16) {
                        const lcNo = cells[0].textContent.trim();
                        const beNo = cells[1].textContent.trim();
                        const beDate = cells[2].textContent.trim();
                        const billNo = cells[3].textContent.trim();
                        const billDate = cells[4].textContent.trim();
                        const goodsName = cells[5].textContent.trim();
                        const qty = cells[6].textContent.trim();
                        const netWeight = cells[7].textContent.trim();
                        const month = cells[8].textContent.trim();
                        const value = cells[9].textContent.trim();
                        const portBill = cells[10].textContent.trim();
                        const totalBillAmount = cells[11].textContent.trim();
                        const dfVat = cells[12].textContent.trim();
                        const docFee = cells[13].textContent.trim();
                        const scanFee = cells[14].textContent.trim();
                        const itc = cells[15].textContent.trim();

                        rowsHTML += `
        <tr>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${lcNo}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${beNo}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${beDate}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${billNo}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${billDate}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${goodsName}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${qty}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${netWeight}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${month}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${value}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${portBill}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${totalBillAmount}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${dfVat}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${docFee}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${scanFee}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${itc}</td>
        </tr>
                        `;
                    }
                });

                return rowsHTML;
            }

            // Helper function to get total row HTML
            function getTotalRowHTML() {
                // Try to get from tfoot
                const tfoot = document.querySelector('#reportTable table tfoot');

                if (tfoot) {
                    const tfootRows = tfoot.querySelectorAll('tr');

                    for (let row of tfootRows) {
                        const cells = row.querySelectorAll('th, td');

                        if (cells.length >= 8) {
                            const valueTotal = cells[1]?.textContent?.trim() || '0.00';
                            const portBillTotal = cells[2]?.textContent?.trim() || '0.00';
                            const totalBillAmountTotal = cells[3]?.textContent?.trim() || '0.00';
                            const dfVatTotal = cells[4]?.textContent?.trim() || '0.00';
                            const docFeeTotal = cells[5]?.textContent?.trim() || '0.00';
                            const scanFeeTotal = cells[6]?.textContent?.trim() || '0.00';
                            const itcTotal = cells[7]?.textContent?.trim() || '0.00';

                            return `
        <tr>
            <td colspan="9" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">GRAND TOTAL</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${valueTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${portBillTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${totalBillAmountTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${dfVatTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${docFeeTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${scanFeeTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${itcTotal}</td>
        </tr>
                            `;
                        }
                    }
                }

                // Calculate totals from data rows as fallback
                let calculatedValueTotal = 0;
                let calculatedPortBillTotal = 0;
                let calculatedTotalBillAmountTotal = 0;
                let calculatedDfVatTotal = 0;
                let calculatedDocFeeTotal = 0;
                let calculatedScanFeeTotal = 0;
                let calculatedItcTotal = 0;

                const dataRows = document.querySelectorAll('#reportTable table tbody tr');
                dataRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 16) {
                        const valueText = cells[9].textContent.trim();
                        const portBillText = cells[10].textContent.trim();
                        const totalBillAmountText = cells[11].textContent.trim();
                        const dfVatText = cells[12].textContent.trim();
                        const docFeeText = cells[13].textContent.trim();
                        const scanFeeText = cells[14].textContent.trim();
                        const itcText = cells[15].textContent.trim();

                        if (valueText !== '' && valueText !== '-') {
                            calculatedValueTotal += parseFloat(valueText.replace(/,/g, '')) || 0;
                        }
                        if (portBillText !== '' && portBillText !== '-') {
                            calculatedPortBillTotal += parseFloat(portBillText.replace(/,/g, '')) || 0;
                        }
                        if (totalBillAmountText !== '' && totalBillAmountText !== '-') {
                            calculatedTotalBillAmountTotal += parseFloat(totalBillAmountText.replace(/,/g, '')) || 0;
                        }
                        if (dfVatText !== '' && dfVatText !== '-') {
                            calculatedDfVatTotal += parseFloat(dfVatText.replace(/,/g, '')) || 0;
                        }
                        if (docFeeText !== '' && docFeeText !== '-') {
                            calculatedDocFeeTotal += parseFloat(docFeeText.replace(/,/g, '')) || 0;
                        }
                        if (scanFeeText !== '' && scanFeeText !== '-') {
                            calculatedScanFeeTotal += parseFloat(scanFeeText.replace(/,/g, '')) || 0;
                        }
                        if (itcText !== '' && itcText !== '-') {
                            calculatedItcTotal += parseFloat(itcText.replace(/,/g, '')) || 0;
                        }
                    }
                });

                return `
        <tr>
            <td colspan="9" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">GRAND TOTAL</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedValueTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedPortBillTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedTotalBillAmountTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedDfVatTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedDocFeeTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedScanFeeTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedItcTotal.toFixed(2)}</td>
        </tr>
                `;
            }
        });
    </script>
@endsection
