@extends('layouts.layout')
@section('styles')
    <style>
        .company-header { text-align: center; }
        .company-header h1 { margin: 0; font-size: 22px; font-weight: bold; }
        .company-header p { margin: 2px 0; font-size: 13px; color: #333; }
        .invoice-info { display: flex; justify-content: space-between; margin-top: 10px; font-size: 14px; }
        .invoice-info div { width: 48%; }
        .invoice-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .invoice-table th, .invoice-table td { border: 1px solid #000; padding: 6px 8px; }
        .invoice-table th { background-color: #f4f4f4; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .total-row td { font-weight: bold; background: #f9f9f9; }
        .section-header { font-weight: bold; background-color: #e9ecef; }
        .loading-overlay {
            display: none; position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; background: rgba(255,255,255,0.8);
            z-index: 1000; justify-content: center; align-items: center;
        }
        .negative { color: red; }
        .action-buttons { margin-bottom: 15px; text-align: right; }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
            .invoice-table th { background-color: #f4f4f4 !important; }
            .total-row td { background: #f9f9f9 !important; }
            .section-header { background-color: #e9ecef !important; }
        }
    </style>
@endsection
@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Monthly Summary Report</h5>
                    <form class="row g-3 BankBook" id="bankBookForm">
                        <div class="col-md-3 form-group">
                            <input type="month" id="month" name="month" class="form-control form-control-sm"
                                   value="{{ $selectedMonth->format('Y-m') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="button" id="resetFilter" class="btn btn-secondary btn-sm">Reset</button>
                            <button type="button" id="printBtn" class="btn btn-info btn-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button type="button" id="excelBtn" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing dc-report-table" id="reportContent">
            <!-- Hidden element to store totals for Excel export -->
            <div id="summaryReportTotals"
                 data-previous-month-closing="{{ number_format($previousMonthClosing, 2) }}"
                 data-dhaka-bank-received="{{ number_format($dhakaBankReceived, 2) }}"
                 data-cash-received="{{ number_format($cashReceived, 2) }}"
                 data-office-balance="{{ number_format($officeBalance, 2) }}"
                 data-export-total="{{ number_format($exportData['total'], 2) }}"
                 data-import-total="{{ number_format($importData['total'], 2) }}"
                 data-office-expenses="{{ number_format($officeExpenses, 2) }}"
                 data-closing-balance="{{ number_format($closingBalance, 2) }}"
                 data-month-text="{{ $selectedMonth->format('M-Y') }}"
                 style="display: none;">
            </div>

            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>

            <div class="company-header">
                <h1>MULTI FABS LTD</h1>
                <p>(SELF C&F AGENTS)</p>
                <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
            </div>

            <hr style="border: none; border-top: 1px solid #222; margin: 10px 0 18px 0;" />

            <div class="invoice-info">
                <div>
                    <strong id="reportTitle">CASH RECEIVED AND PAYMENT STATEMENT FOR THE MONTH {{ $selectedMonth->format('M-Y') }}</strong>
                </div>
                <div class="right">
                    <strong id="reportDate">Date: {{ now()->timezone('Asia/Dhaka')->format('d/m/Y') }}</strong>
                </div>
            </div>

            <table class="invoice-table" style="width: 100%; margin-top: 10px;">
                <thead>
                <tr>
                    <th>SL</th>
                    <th>DESCRIPTION</th>
                    <th>TOTAL TAKA</th>
                    <th>G.TOTAL TAKA.</th>
                </tr>
                </thead>

                <tbody>
                <tr class="section-header">
                    <td colspan="4">Bank in</td>
                </tr>

                <tr>
                    <td>1</td>
                    <td id="openingBalanceDesc">TOTAL OPENING BALANCE {{ $selectedMonth->format('M') }} {{ $selectedMonth->startOfMonth()->format('d.m.Y') }}</td>
                    <td class="right {{ $previousMonthClosing < 0 ? 'negative' : '' }}" id="openingBalanceAmount">{{ number_format($previousMonthClosing, 2) }}</td>
                    <td></td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>CASH RECEIVED IN DHAKA BANK</td>
                    <td class="right" id="dhakaBankAmount">{{ number_format($dhakaBankReceived, 2) }}</td>
                    <td></td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>CASH RECEIVED</td>
                    <td class="right" id="cashReceivedAmount">{{ number_format($cashReceived, 2) }}</td>
                    <td></td>
                </tr>

                <tr class="total-row">
                    <td></td>
                    <td id="officeBalanceDesc">OFFICE BALANCE ON {{ $selectedMonth->startOfMonth()->format('d.m.Y') }} TO {{ $selectedMonth->endOfMonth()->format('d.m.Y') }}</td>
                    <td></td>
                    <td class="left {{ $officeBalance < 0 ? 'negative' : '' }}" id="officeBalanceAmount">{{ number_format($officeBalance, 2) }}</td>
                </tr>

                <tr class="section-header">
                    <td colspan="4">EXPENSES</td>
                </tr>

                <tr>
                    <td>1</td>
                    <td id="exportDesc">EXPORT DOCUMENTS MFL {{ $exportData['qty'] }} PCS ( As per Sheet)</td>
                    <td></td>
                    <td class="left" id="exportAmount">{{ number_format($exportData['total'], 2) }}</td>
                </tr>

                <tr>
                    <td>2</td>
                    <td id="importDesc">IMPORT DOCUMENTS MFL {{ $importData['qty'] }} PCS ( As per Sheet)</td>
                    <td></td>
                    <td class="left" id="importAmount">{{ number_format($importData['total'], 2) }}</td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Office Maintenance Expenses (As Per Statement)</td>
                    <td></td>
                    <td class="left" id="officeExpensesAmount">{{ number_format($officeExpenses, 2) }}</td>
                </tr>
                </tbody>

                <tfoot>
                <tr class="total-row">
                    <th colspan="3" class="right" id="closingBalanceLabel">TOTAL BALANCE {{ $selectedMonth->format('M') }} CLOSING {{ $selectedMonth->endOfMonth()->format('d.m.Y') }}:</th>
                    <th class="right {{ $closingBalance < 0 ? 'negative' : '' }}" id="closingBalanceAmount">{{ number_format($closingBalance, 2) }}</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#excelBtn').on('click', function() {
                exportToExcel();
            });

            // Excel export functionality with inline CSS
            function exportToExcel() {
                try {
                    // Get filter values
                    const monthValue = $('#month').val();
                    const formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : 'All Time';
                    const currentDate = new Date().toLocaleDateString('en-GB');

                    // Get the table data from the current view
                    const table = document.querySelector('#reportContent table');
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
            <td colspan="4" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px;">
                MULTI FABS LTD <br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Report Info -->
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold;">
                CASH RECEIVED AND PAYMENT STATEMENT FOR THE MONTH ${formattedMonth}
            </td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">
                Date: ${currentDate}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="4" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Table Header -->
        <tr>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">SL</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 60%;">DESCRIPTION</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 16%;">TOTAL TAKA</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 16%;">G.TOTAL TAKA.</td>
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

                    const fileName = `Monthly_Summary_Report_${formattedMonth.replace(/\s+/g, '_')}.xls`;
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
                const rows = document.querySelectorAll('#reportContent table tbody tr');
                let rowsHTML = '';

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 4) {
                        const sl = cells[0].textContent.trim();
                        const description = cells[1].textContent.trim();
                        const totalTaka = cells[2].textContent.trim();
                        const gTotalTaka = cells[3].textContent.trim();

                        // Check if it's a section header
                        if (row.classList.contains('section-header')) {
                            rowsHTML += `
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold; background-color: #e9ecef;">${description}</td>
        </tr>
                            `;
                        } else {
                            rowsHTML += `
        <tr>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${sl}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: left;">${description}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right;">${totalTaka}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right;">${gTotalTaka}</td>
        </tr>
                            `;
                        }
                    }
                });

                return rowsHTML;
            }

            // Helper function to get total row HTML
            function getTotalRowHTML() {
                console.log('Getting total row HTML for Monthly Summary Report...');

                // Method 1: Try to get from tfoot
                const tfoot = document.querySelector('#reportContent table tfoot');
                console.log('TFoot found:', tfoot);

                if (tfoot) {
                    const tfootRows = tfoot.querySelectorAll('tr');
                    console.log('TFoot rows:', tfootRows.length);

                    for (let row of tfootRows) {
                        const cells = row.querySelectorAll('th, td');
                        console.log('TFoot cells:', cells.length);

                        if (cells.length >= 4) {
                            const label = cells[0]?.textContent?.trim() || '';
                            const amount = cells[3]?.textContent?.trim() || '0.00';

                            console.log('TFoot totals:', { label, amount });

                            return `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${label}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${amount}</td>
        </tr>
                            `;
                        }
                    }
                }

                // Method 2: Try to get from hidden data element
                const totalsElement = document.getElementById('summaryReportTotals');
                if (totalsElement) {
                    const closingBalance = totalsElement.getAttribute('data-closing-balance') || '0.00';
                    const monthText = totalsElement.getAttribute('data-month-text') || 'Current Month';

                    return `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">TOTAL BALANCE ${monthText} CLOSING:</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${closingBalance}</td>
        </tr>
                    `;
                }

                // Method 3: Try to get from elements with IDs
                const closingBalanceElement = document.getElementById('closingBalanceAmount');
                const closingBalanceLabel = document.getElementById('closingBalanceLabel');

                if (closingBalanceElement && closingBalanceLabel) {
                    const amount = closingBalanceElement.textContent.trim();
                    const label = closingBalanceLabel.textContent.trim();

                    return `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${label}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${amount}</td>
        </tr>
                    `;
                }

                // Method 4: Default fallback
                console.log('Using default closing balance');
                const reportTitle = document.getElementById('reportTitle');
                const monthFromTitle = reportTitle ? reportTitle.textContent.match(/MONTH\s+([A-Za-z0-9-]+)/)?.[1] : 'Current Month';

                return `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">TOTAL BALANCE ${monthFromTitle} CLOSING:</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">0.00</td>
        </tr>
                `;
            }

            const originalAction = "{{ route('summary.report') }}";

            // Month change event
            $('#month').on('change', function() {
                loadReportData();
            });

            // Reset filter button
            $('#resetFilter').on('click', function() {
                $('#month').val('{{ \Carbon\Carbon::now()->format('Y-m') }}');
                loadReportData();
            });

            // Print button functionality
            $('#printBtn').on('click', function() {
                printReport();
            });

            // Function to load report data via AJAX
            function loadReportData() {
                const month = $('#month').val();
                const url = `${originalAction}?month=${month}&ajax=true`;

                $('#loadingOverlay').show();

                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        updateReportContent(response);
                        $('#loadingOverlay').hide();
                    },
                    error: function() {
                        alert('Error loading report data. Please try again.');
                        $('#loadingOverlay').hide();
                    }
                });
            }

            // Function to update report content with new data
            function updateReportContent(data) {
                // Safe date parsing with validation
                let selectedMonth;
                try {
                    // Try to parse the date from the response
                    if (data.selectedMonth && data.selectedMonth.date) {
                        selectedMonth = new Date(data.selectedMonth.date);
                    } else if (data.selectedMonth) {
                        selectedMonth = new Date(data.selectedMonth);
                    } else {
                        // Fallback to current month
                        selectedMonth = new Date();
                    }

                    // Validate the date
                    if (isNaN(selectedMonth.getTime())) {
                        throw new Error('Invalid date received');
                    }
                } catch (error) {
                    console.error('Date parsing error:', error);
                    // Fallback to current date
                    selectedMonth = new Date();
                }

                // Get first day of selected month
                const firstDayCurrentMonth = new Date(selectedMonth.getFullYear(), selectedMonth.getMonth(), 1);

                // Get last day of selected month
                const lastDayCurrentMonth = new Date(selectedMonth.getFullYear(), selectedMonth.getMonth() + 1, 0);

                // Safe date formatting function
                const formatDate = (date) => {
                    if (!date || isNaN(date.getTime())) {
                        console.error('Invalid date provided to formatDate:', date);
                        return 'Invalid Date';
                    }
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}.${month}.${year}`;
                };

                // Safe month formatting
                const formatMonth = (date) => {
                    if (!date || isNaN(date.getTime())) {
                        console.error('Invalid date provided to formatMonth:', date);
                        return 'Invalid Month';
                    }
                    return date.toLocaleDateString('en-GB', { month: 'short' });
                };

                // Safe currency formatting
                const formatCurrency = (amount) => {
                    const num = parseFloat(amount);
                    if (isNaN(num)) {
                        console.error('Invalid amount provided to formatCurrency:', amount);
                        return '0.00';
                    }
                    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                };

                console.log('Selected Month:', selectedMonth);
                console.log('First Day Current Month:', firstDayCurrentMonth);
                console.log('Last Day Current Month:', lastDayCurrentMonth);

                // Update header with selected month
                $('#reportTitle').html(`CASH RECEIVED AND PAYMENT STATEMENT FOR THE MONTH ${formatMonth(selectedMonth)}-${selectedMonth.getFullYear()}`);

                // Update table data - ALL dates now use the SELECTED month
                $('#openingBalanceDesc').html(`TOTAL OPENING BALANCE ${formatMonth(selectedMonth)} ${formatDate(firstDayCurrentMonth)}`);
                $('#openingBalanceAmount').html(formatCurrency(data.previousMonthClosing)).toggleClass('negative', data.previousMonthClosing < 0);

                $('#dhakaBankAmount').html(formatCurrency(data.dhakaBankReceived));
                $('#cashReceivedAmount').html(formatCurrency(data.cashReceived));

                // Update office balance row with first day to last day of SELECTED month
                $('#officeBalanceDesc').html(`OFFICE BALANCE ON ${formatDate(firstDayCurrentMonth)} TO ${formatDate(lastDayCurrentMonth)}`);
                $('#officeBalanceAmount').html(formatCurrency(data.officeBalance)).toggleClass('negative', data.officeBalance < 0);

                $('#exportDesc').html(`EXPORT DOCUMENTS MFL ${data.exportData.qty} PCS ( As per Sheet)`);
                $('#exportAmount').html(formatCurrency(data.exportData.total));

                $('#importDesc').html(`IMPORT DOCUMENTS MFL ${data.importData.qty} PCS ( As per Sheet)`);
                $('#importAmount').html(formatCurrency(data.importData.total));

                $('#officeExpensesAmount').html(formatCurrency(data.officeExpenses));

                // Update footer with last day of SELECTED month
                $('#closingBalanceLabel').html(`TOTAL BALANCE ${formatMonth(selectedMonth)} CLOSING ${formatDate(lastDayCurrentMonth)}:`);
                $('#closingBalanceAmount').html(formatCurrency(data.closingBalance)).toggleClass('negative', data.closingBalance < 0);

                // Update print date
                $('#reportDate').html(`Date: ${new Date().toLocaleDateString('en-GB')}`);

                // Update hidden data element for Excel export
                $('#summaryReportTotals').attr({
                    'data-previous-month-closing': formatCurrency(data.previousMonthClosing),
                    'data-dhaka-bank-received': formatCurrency(data.dhakaBankReceived),
                    'data-cash-received': formatCurrency(data.cashReceived),
                    'data-office-balance': formatCurrency(data.officeBalance),
                    'data-export-total': formatCurrency(data.exportData.total),
                    'data-import-total': formatCurrency(data.importData.total),
                    'data-office-expenses': formatCurrency(data.officeExpenses),
                    'data-closing-balance': formatCurrency(data.closingBalance),
                    'data-month-text': `${formatMonth(selectedMonth)}-${selectedMonth.getFullYear()}`
                });
            }

            // Print function
            function printReport() {
                const reportContent = $('#reportContent').html();

                // Create print window
                const printWindow = window.open('', '_blank', 'width=1000,height=600');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Monthly Summary Report - {{ $selectedMonth->format('M Y') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .company-header { text-align: center; }
                            .company-header h1 { margin: 0; font-size: 22px; font-weight: bold; }
                            .company-header p { margin: 2px 0; font-size: 13px; color: #333; }
                            .invoice-info { display: flex; justify-content: space-between; margin-top: 10px; font-size: 14px; }
                            .invoice-info div { width: 48%; }
                            .invoice-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
                            .invoice-table th, .invoice-table td { border: 1px solid #000; padding: 6px 8px; }
                            .invoice-table th { background-color: #f4f4f4; text-align: left; }
                            .right { text-align: right; }
                            .center { text-align: center; }
                            .total-row td { font-weight: bold; background: #f9f9f9; }
                            .section-header { font-weight: bold; background-color: #e9ecef; }
                            .negative { color: red; }
                            @media print {
                                body { margin: 0; }
                                .invoice-table th { background-color: #f4f4f4 !important; }
                                .total-row td { background: #f9f9f9 !important; }
                                .section-header { background-color: #e9ecef !important; }
                            }
                        </style>
                    </head>
                    <body>
                        ${reportContent}
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
        });
    </script>
@endsection
