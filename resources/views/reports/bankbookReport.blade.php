@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Bank Book Report</h4>

                    <!-- FILTER FORM -->
                    <form id="filterForm" class="mb-4 row g-3 BankBook">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <select name="bank" id="bank" class="form-control form-control-sm">
                                    @foreach($banks as $b)
                                        <option value="{{ $b }}" {{ (isset($bank) && $bank === $b) ? 'selected' : '' }}>
                                            {{ $b }}
                                        </option>
                                    @endforeach
                                    <option value="all" {{ (isset($bank) && strtolower($bank) === 'all') ? 'selected' : '' }}>All Banks</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <input type="month" id="month" name="month" class="form-control form-control-sm"
                                       value="{{ $month ?? \Carbon\Carbon::now()->format('Y-m') }}">
                            </div>

                            <div class="col-md-2">
                                <select name="type" id="type" class="form-control form-control-sm">
                                    <option value="all" {{ (isset($type) && strtolower($type) === 'all') ? 'selected' : '' }}>All</option>
                                    <option value="Receive" {{ (isset($type) && $type === 'Receive') ? 'selected' : '' }}>Receive</option>
                                    <option value="Withdraw" {{ (isset($type) && $type === 'Withdraw') ? 'selected' : '' }}>Withdraw</option>
                                    <option value="Pay Order" {{ (isset($type) && $type === 'Pay Order') ? 'selected' : '' }}>Pay Order</option>
                                    <option value="Bank Transfer" {{ (isset($type) && $type === 'Bank Transfer') ? 'selected' : '' }}>Bank Transfer</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group d-flex align-items-end">
                                <button type="button" id="resetBtn" class="btn btn-secondary btn-sm me-1">Reset</button>
                                <button type="button" id="printBtn" class="btn btn-info btn-sm me-1">
                                    <i class="fas fa-print"></i> Print
                                </button>
                                <button type="button" id="excelBtn" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                            </div>

                        </div>
                    </form>

                    <!-- REPORT TABLE (this is replaced dynamically by AJAX) -->
                    <div id="reportTable">
                        @include('partials.bankbookReportTable', ['data' => $data, 'month' => $month ?? '', 'bank' => $bank ?? ''])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Store original values for reset
            const originalMonth = $('#month').val();
            const originalBank = $('#bank').val();
            const originalType = $('#type').val();

            // helper to load
            function loadReport(params) {
                $('#reportTable').html('<div class="py-4 text-center">Loading...</div>');
                $.ajax({
                    url: "{{ route('bankbook.report') }}",
                    type: 'GET',
                    data: params,
                    success: function (res) {
                        $('#reportTable').html(res.html);
                    },
                    error: function (xhr, status, err) {
                        $('#reportTable').html('<div class="text-danger p-3">Could not load data...</div>');
                        console.error(xhr, status, err);
                    }
                });
            }

            // Trigger AJAX when any filter changes
            $('#bank, #month, #type').on('change', function () {
                loadReport({
                    bank: $('#bank').val(),
                    month: $('#month').val(),
                    type: $('#type').val()
                });
            });

            // Reset button
            $('#resetBtn').on('click', function () {
                // Reset form values
                $('#month').val(originalMonth);
                $('#bank').val(originalBank);
                $('#type').val(originalType);

                // Reload with reset values
                loadReport({
                    bank: originalBank,
                    month: originalMonth,
                    type: originalType
                });
            });

            // Print button
            $('#printBtn').on('click', function() {
                printReport();
            });

            // Excel button
            $('#excelBtn').on('click', function() {
                exportToExcel();
            });

            // Function to print the report
            function printReport() {
                // Create a new window for printing
                var printWindow = window.open('', '_blank');

                // Get the HTML content of the report table
                var reportContent = document.getElementById('reportTable').innerHTML;

                // Get filter values for the report title
                var monthValue = document.getElementById('month').value;
                var bankValue = document.getElementById('bank').value;
                var typeValue = document.getElementById('type').value;

                var formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All Time';
                var formattedBank = bankValue === 'all' ? 'All Banks' : bankValue;
                var formattedType = typeValue === 'all' ? 'All Types' : typeValue;

                // Write the print document
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Bank Book Report - ${formattedMonth}</title>
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
                            .filter-info {
                                margin-bottom: 15px;
                                font-size: 14px;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                                font-size: 12px;
                            }
                            th, td {
                                border: 1px solid #ddd;
                                padding: 8px;
                                text-align: left;
                            }
                            th {
                                background-color: #f2f2f2;
                                font-weight: bold;
                            }
                            .text-end {
                                text-align: right;
                            }
                            .table-active {
                                font-weight: bold;
                                background-color: #e9e9e9;
                            }
                            @media print {
                                body {
                                    margin: 0;
                                    padding: 15px;
                                }
                                .no-print {
                                    display: none !important;
                                }
                                @page {
                                    margin: 10mm;
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
                    }, 250);
                };
            }

            // Function to export to Excel with inline CSS
            function exportToExcel() {
                try {
                    // Get filter values
                    const monthValue = $('#month').val();
                    const bankValue = $('#bank').val();
                    const typeValue = $('#type').val();

                    const formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All Time';
                    const formattedBank = bankValue === 'all' ? 'All Banks' : bankValue;
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
            <td colspan="6" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px;">
                MULTI FABS LTD <br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="6" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Report Info -->
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold;">
                Bank Book For the Month of ${formattedMonth} and Bank is ${formattedBank}
            </td>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">
                Date: ${currentDate}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="6" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Table Header -->
        <tr>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Date</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Type</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 30%;">Note</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Received Amount</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Withdrawal Amount</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Final Amount</td>
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

                    const fileName = `Bank_Book_${formattedMonth.replace(/\s+/g, '_')}_${formattedBank.replace(/\s+/g, '_')}.xls`;
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
            <td colspan="6" style="border: 1px solid #000000; padding: 5px; text-align: center;">No records found.</td>
        </tr>
                    `;
                }

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 6) {
                        const date = cells[0].textContent.trim();
                        const type = cells[1].textContent.trim();
                        const note = cells[2].textContent.trim();
                        const received = cells[3].textContent.trim();
                        const withdrawal = cells[4].textContent.trim();
                        const final = cells[5].textContent.trim();

                        rowsHTML += `
        <tr>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${date}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: left;">${type}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: left;">${note}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${received === '-' ? '' : received}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${withdrawal === '-' ? '' : withdrawal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${final}</td>
        </tr>
                        `;
                    }
                });

                return rowsHTML;
            }

            // Helper function to get total row HTML - COMPLETELY REWRITTEN
            function getTotalRowHTML() {
                console.log('Getting total row HTML...');

                // Method 1: Try to get from tfoot with detailed logging
                const tfoot = document.querySelector('#reportTable table tfoot');
                console.log('TFoot found:', tfoot);

                if (tfoot) {
                    const tfootRows = tfoot.querySelectorAll('tr');
                    console.log('TFoot rows:', tfootRows.length);

                    for (let row of tfootRows) {
                        const cells = row.querySelectorAll('th, td');
                        console.log('TFoot cells:', cells.length);

                        if (cells.length >= 6) {
                            const receiveTotal = cells[3]?.textContent?.trim() || '0.00';
                            const withdrawTotal = cells[4]?.textContent?.trim() || '0.00';
                            const finalTotal = cells[5]?.textContent?.trim() || '0.00';

                            console.log('TFoot totals:', { receiveTotal, withdrawTotal, finalTotal });

                            return `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${receiveTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${withdrawTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${finalTotal}</td>
        </tr>
                            `;
                        }
                    }
                }

                // Method 2: Try to calculate totals from data rows
                console.log('Calculating totals from data rows...');
                let calculatedReceiveTotal = 0;
                let calculatedWithdrawTotal = 0;

                const dataRows = document.querySelectorAll('#reportTable table tbody tr');
                dataRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 6) {
                        const receivedText = cells[3].textContent.trim();
                        const withdrawalText = cells[4].textContent.trim();

                        if (receivedText !== '-' && receivedText !== '') {
                            calculatedReceiveTotal += parseFloat(receivedText.replace(/,/g, '')) || 0;
                        }
                        if (withdrawalText !== '-' && withdrawalText !== '') {
                            calculatedWithdrawTotal += parseFloat(withdrawalText.replace(/,/g, '')) || 0;
                        }
                    }
                });

                const calculatedFinalTotal = calculatedReceiveTotal - calculatedWithdrawTotal;

                console.log('Calculated totals:', {
                    receive: calculatedReceiveTotal,
                    withdraw: calculatedWithdrawTotal,
                    final: calculatedFinalTotal
                });

                if (calculatedReceiveTotal !== 0 || calculatedWithdrawTotal !== 0) {
                    return `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedReceiveTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedWithdrawTotal.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedFinalTotal.toFixed(2)}</td>
        </tr>
                    `;
                }

                // Method 3: Default fallback
                console.log('Using default totals');
                return `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">0.00</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">0.00</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">0.00</td>
        </tr>
                `;
            }
        });
    </script>
@endsection
