@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Export Bill Summary</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 form-group">
                            <label for="month" class="form-label">Select Month</label>
                            <input type="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 form-group d-flex align-items-end">
                            <button type="button" id="resetBtn" class="btn btn-secondary btn-sm me-1">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="button" id="printBtn" class="btn btn-info btn-sm me-1">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button type="button" id="excelBtn" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing dc-report-table" id="reportTable">
            @include('partials.exportBillSummaryTable', ['bills' => $bills, 'month' => $month])
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

            // Excel button click event
            $('#excelBtn').on('click', function() {
                exportToExcel();
            });

            // Function to load report data
            function loadReportData(month) {
                $.ajax({
                    url: "{{ route('export.bill.summary.report') }}",
                    type: "GET",
                    data: { month: month },
                    beforeSend: function() {
                        $('#reportTable').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                    },
                    success: function(response){
                        $('#reportTable').html(response);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#reportTable').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
                    }
                });
            }

            // Print functionality
            function printReport() {
                const currentMonth = $('#month').val();
                const monthName = currentMonth ? new Date(currentMonth + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : 'All Time';

                // Create print-friendly HTML
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Export Bill Summary - ${monthName}</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 20px;
                                color: #000;
                            }
                            .print-container {
                                max-width: 100%;
                            }
                            .print-header {
                                margin-bottom: 20px;
                                border-bottom: 2px solid #333;
                                padding-bottom: 10px;
                            }
                            .print-header h2 {
                                margin: 0 0 10px 0;
                                color: #333;
                            }
                            .print-header p {
                                margin: 5px 0;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 20px 0;
                                font-size: 12px;
                            }
                            th, td {
                                border: 1px solid #ddd;
                                padding: 8px;
                                text-align: left;
                            }
                            th {
                                background-color: #f5f5f5;
                                font-weight: bold;
                            }
                            .text-right {
                                text-align: right;
                            }
                            .text-center {
                                text-align: center;
                            }
                            .total-row {
                                font-weight: bold;
                                background-color: #e9ecef;
                            }
                            .summary-row {
                                font-weight: bold;
                                background-color: #d1ecf1;
                            }
                            .print-footer {
                                margin-top: 30px;
                                padding-top: 10px;
                                border-top: 1px solid #333;
                                font-size: 11px;
                                color: #666;
                            }
                            .no-print { display: none; }
                            @media print {
                                body { margin: 0; }
                                .print-header { border-bottom-color: #000; }
                                th { background-color: #f0f0f0 !important; }
                                .table-responsive { overflow: visible !important; }
                                .card { border: none !important; box-shadow: none !important; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-container">
                            ${document.getElementById('reportTable').innerHTML}
                            <div class="print-footer">
                                <p>Generated by Your Company Name</p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);

                printWindow.document.close();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.onafterprint = function() {
                        printWindow.close();
                    };
                }, 500);
            }

            // Excel export functionality with inline CSS
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
    <table style="border-collapse: collapse; width: 100%; font-family: Arial; font-size: 11px;">
        <!-- Company Header Section -->
        <tr>
            <td colspan="12" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px;">
                MULTI FABS LTD <br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="12" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Report Info -->
        <tr>
            <td colspan="6" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold;">
                Export Statement : ${formattedMonth}
            </td>
            <td colspan="6" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">
                Print Date: ${currentDate}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="12" style="border: none; padding: 10px;"></td>
        </tr>

        <!-- Table Header -->
        <tr>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 10%;">INVOICE NO</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">TOTAL DATE</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 6%;">CTN.</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">INVOICE PCS</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">VALUE($)</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">B/E NO.</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">DATE</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">BILL NO</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">ACTUAL DATE</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 10%;">SUBMITED EXP</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">DF VAT</th>
            <th style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 10%;">APPROVED BILL (TK.)</th>
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

                    const fileName = `Export_Bill_Summary_${formattedMonth.replace(/\s+/g, '_')}.xls`;
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
            <td colspan="12" style="border: 1px solid #000000; padding: 5px; text-align: center;">No records found for ${formattedMonth}.</td>
        </tr>
                    `;
                }

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 12) {
                        const invoiceNo = cells[0].textContent.trim();
                        const totalDate = cells[1].textContent.trim();
                        const ctn = cells[2].textContent.trim();
                        const invoicePcs = cells[3].textContent.trim();
                        const valueUsd = cells[4].textContent.trim();
                        const beNo = cells[5].textContent.trim();
                        const date = cells[6].textContent.trim();
                        const billNo = cells[7].textContent.trim();
                        const actualDate = cells[8].textContent.trim();
                        const submittedExp = cells[9].textContent.trim();
                        const dfVat = cells[10].textContent.trim();
                        const approvedBill = cells[11].textContent.trim();

                        rowsHTML += `
        <tr>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${invoiceNo}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${totalDate}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${ctn}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${invoicePcs}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${valueUsd}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${beNo}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${date}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${billNo}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${actualDate}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${submittedExp}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${dfVat}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${approvedBill}</td>
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

                        if (cells.length >= 4) {
                            const submittedExpTotal = cells[1]?.textContent?.trim() || '0.00';
                            const dfVatTotal = cells[2]?.textContent?.trim() || '0.00';

                            console.log('TFoot totals:', {
                                submittedExpTotal,
                                dfVatTotal
                            });

                            return `
        <tr>
            <td colspan="9" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">GRAND TOTAL</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${submittedExpTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${dfVatTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;"></td>
        </tr>
                            `;
                        }
                    }
                }

                // Calculate totals from data rows as fallback
                console.log('Calculating totals from data rows...');
                let calculatedSubmittedExpTotal = 0;
                let calculatedDfVatTotal = 0;

                const dataRows = document.querySelectorAll('#reportTable table tbody tr');
                dataRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 12) {
                        const submittedExpText = cells[9].textContent.trim();
                        const dfVatText = cells[10].textContent.trim();

                        if (submittedExpText !== '' && submittedExpText !== '-') {
                            calculatedSubmittedExpTotal += parseFloat(submittedExpText.replace(/,/g, '')) || 0;
                        }
                        if (dfVatText !== '' && dfVatText !== '-') {
                            calculatedDfVatTotal += parseFloat(dfVatText.replace(/,/g, '')) || 0;
                        }
                    }
                });

                console.log('Calculated totals:', {
                    submittedExp: calculatedSubmittedExpTotal,
                    dfVat: calculatedDfVatTotal
                });

                return `
        <tr>
            <td colspan="9" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">GRAND TOTAL</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedSubmittedExpTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedDfVatTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;"></td>
        </tr>
                `;
            }
        });
    </script>
@endsection
