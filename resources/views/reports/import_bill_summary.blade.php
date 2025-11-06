@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Import Bill Summary</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 form-group">
                            <label for="month" class="form-label">Select Month</label>
                            <input type="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
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
            @include('partials.importBillSummaryTable', ['bills' => $bills, 'month' => $month])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            // Store the original month value
            const originalMonth = "{{ $month }}";

            // Month change event
            $('#month').on('change', function(){
                loadReportData($(this).val());
            });

            // Reset button click event
            $('#resetBtn').on('click', function() {
                // Reset to original month
                $('#month').val(originalMonth);
                // Reload report with original month
                loadReportData(originalMonth);
            });

            // Print button click event
            $('#printBtn').on('click', function() {
                printReport();
            });

            // Excel Export button click event
            $('#excelBtn').on('click', function() {
                exportToExcel();
            });

            // Function to load report data
            function loadReportData(month) {
                $.ajax({
                    url: "{{ route('import.bill.summary.report') }}",
                    type: "GET",
                    data: { month: month },
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

            // Function to print the report
            function printReport() {
                // Create a new window for printing
                var printWindow = window.open('', '_blank');

                // Get the HTML content of the report table
                var reportContent = document.getElementById('reportTable').innerHTML;

                // Get the month value for the report title
                var monthValue = document.getElementById('month').value;
                var formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All Time';

                // Write the print document
                printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Import Bill Summary - ${formattedMonth}</title>
            <style>
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
                    table-layout: fixed; /* Prevents column breaking */
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: center; /* Center all text */
                    vertical-align: middle; /* Center vertically */
                    white-space: nowrap; /* Prevent text wrapping */
                    overflow: hidden; /* Hide overflow */
                    text-overflow: ellipsis; /* Show ... if text too long */
                    font-size: 12px; /* Slightly smaller font to fit content */
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    font-size: 12px;
                    text-align: center; /* Center header text */
                }
                .text-center {
                    text-align: center;
                }
                .text-right {
                    text-align: right;
                }
                .total-row {
                    font-weight: bold;
                    background-color: #e9e9e9;
                }
                /* Set specific column widths to prevent breaking */
                th:nth-child(1), td:nth-child(1) { width: 120px; } /* INVOICE NO */
                th:nth-child(2), td:nth-child(2) { width: 80px; }  /* TOTAL DATE */
                th:nth-child(3), td:nth-child(3) { width: 70px; }  /* CTN. */
                th:nth-child(4), td:nth-child(4) { width: 100px; } /* INVOICE PCS */
                th:nth-child(5), td:nth-child(5) { width: 100px; } /* VALU(E) */
                th:nth-child(6), td:nth-child(6) { width: 90px; }  /* B/E NO. */
                th:nth-child(7), td:nth-child(7) { width: 80px; }  /* DATE */
                th:nth-child(8), td:nth-child(8) { width: 120px; } /* BILL NO */
                th:nth-child(9), td:nth-child(9) { width: 100px; } /* ACTUAL DATE */
                th:nth-child(10), td:nth-child(10) { width: 100px; } /* SUBMITED EXP */
                th:nth-child(11), td:nth-child(11) { width: 80px; } /* DF VAT */
                th:nth-child(12), td:nth-child(12) { width: 120px; } /* APPROVED BILL */

                @media print {
                    body {
                        margin: 0;
                        padding: 15px;
                    }
                    .no-print {
                        display: none !important;
                    }
                    @page {
                        size: landscape;
                        margin: 10mm;
                    }
                    table {
                        width: 100% !important;
                    }
                    th, td {
                        white-space: nowrap !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                        font-size: 11px !important; /* Even smaller for print */
                    }
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

            // Function to export to Excel with inline CSS
            function exportToExcel() {
                try {
                    // Get the month value for the report title
                    const monthValue = $('#month').val();
                    const formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All Time';
                    const currentDate = new Date().toLocaleDateString('en-GB');

                    // Get the table data from the current view
                    const table = document.querySelector('#reportTable table');
                    if (!table) {
                        alert('No data found to export.');
                        return;
                    }

                    // Create HTML table with inline styling for Excel
                    const tableHTML = `
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 14px;">
        <!-- Company Header Section -->
        <tr>
            <td colspan="15" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px;">
                MULTI FABS LTD <br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="15" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Report Info -->
        <tr>
            <td colspan="10" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold;">
                IMPORT BILL STATEMENT : ${formattedMonth}
            </td>
            <td colspan="5" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">
                Date: ${currentDate}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="15" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Table Header -->
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
                    return `
        <tr>
            <td colspan="15" style="border: 1px solid #000000; padding: 5px; text-align: center;">No records found for ${formattedMonth}.</td>
        </tr>
                    `;
                }

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 15) {
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
        </tr>
                        `;
                    }
                });

                return rowsHTML;
            }

            // Helper function to get total row HTML
            function getTotalRowHTML() {
                console.log('Getting total row HTML...');

                // Try to get from tfoot
                const tfoot = document.querySelector('#reportTable table tfoot');
                console.log('TFoot found:', tfoot);

                if (tfoot) {
                    const tfootRows = tfoot.querySelectorAll('tr');
                    console.log('TFoot rows:', tfootRows.length);

                    for (let row of tfootRows) {
                        const cells = row.querySelectorAll('th, td');
                        console.log('TFoot cells:', cells.length);

                        if (cells.length >= 7) {
                            const valueTotal = cells[1]?.textContent?.trim() || '0.00';
                            const portBillTotal = cells[2]?.textContent?.trim() || '0.00';
                            const totalBillAmountTotal = cells[3]?.textContent?.trim() || '0.00';
                            const dfVatTotal = cells[4]?.textContent?.trim() || '0.00';
                            const docFeeTotal = cells[5]?.textContent?.trim() || '0.00';
                            const scanFeeTotal = cells[6]?.textContent?.trim() || '0.00';

                            console.log('TFoot totals:', {
                                valueTotal,
                                portBillTotal,
                                totalBillAmountTotal,
                                dfVatTotal,
                                docFeeTotal,
                                scanFeeTotal
                            });

                            return `
        <tr>
            <td colspan="9" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">GRAND TOTAL</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${valueTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${portBillTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${totalBillAmountTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${dfVatTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${docFeeTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${scanFeeTotal}</td>
        </tr>
                            `;
                        }
                    }
                }

                // Calculate totals from data rows as fallback
                console.log('Calculating totals from data rows...');
                let calculatedValueTotal = 0;
                let calculatedPortBillTotal = 0;
                let calculatedTotalBillAmountTotal = 0;
                let calculatedDfVatTotal = 0;
                let calculatedDocFeeTotal = 0;
                let calculatedScanFeeTotal = 0;

                const dataRows = document.querySelectorAll('#reportTable table tbody tr');
                dataRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 15) {
                        const valueText = cells[9].textContent.trim();
                        const portBillText = cells[10].textContent.trim();
                        const totalBillAmountText = cells[11].textContent.trim();
                        const dfVatText = cells[12].textContent.trim();
                        const docFeeText = cells[13].textContent.trim();
                        const scanFeeText = cells[14].textContent.trim();

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
                    }
                });

                console.log('Calculated totals:', {
                    value: calculatedValueTotal,
                    portBill: calculatedPortBillTotal,
                    totalBill: calculatedTotalBillAmountTotal,
                    dfVat: calculatedDfVatTotal,
                    docFee: calculatedDocFeeTotal,
                    scanFee: calculatedScanFeeTotal
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
        </tr>
                `;
            }
        });
    </script>
@endsection
