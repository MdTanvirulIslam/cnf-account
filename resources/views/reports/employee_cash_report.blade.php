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

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .ml-2 {
            margin-left: 0.5rem;
        }
        .mr-2 {
            margin-right: 0.5rem;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Employee Cash Report</h5>

                    <!-- Filter Form -->
                    <form action="#" method="POST" class="row g-3 employeeCash">
                        @csrf
                        <div class="col-md-2 form-group">
                            <label for="department">Department</label>
                            <select name="department" id="department" class="form-control form-control-sm">
                                <option value="">All Departments</option>
                                <option value="Import" {{ request('department') == 'Import' ? 'selected' : '' }}>Import</option>
                                <option value="Export" {{ request('department') == 'Export' ? 'selected' : '' }}>Export</option>
                            </select>
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="paymentType">Payment Type</label>
                            <select name="paymentType" id="paymentType" class="form-control form-control-sm">
                                <option value="">All Types</option>
                                <option value="receive" {{ request('paymentType') == 'receive' ? 'selected' : '' }}>Receive</option>
                                <option value="return" {{ request('paymentType') == 'return' ? 'selected' : '' }}>Return</option>
                            </select>
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="month">Month</label>
                            <input type="month" id="month" name="month" class="form-control form-control-sm"
                                   value="{{ $selectedMonth ?? \Carbon\Carbon::now()->format('Y-m') }}">
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="resetFilter" class="btn btn-primary btn-sm me-2">Reset</button>
                            <button type="button" id="printReport" class="btn btn-info btn-sm me-2">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button type="button" id="exportExcel" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="col-xl-12 layout-top-spacing dc-report-table" id="report-table">
            @include('partials.employeeCashReportTable', ['groupedTransactions' => $groupedTransactions, 'selectedMonth' => $selectedMonth ?? \Carbon\Carbon::now()])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // AJAX form submission
            $('.employeeCash').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('employee-cash-report.filter') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#report-table').html(res.html);
                    },
                    error: function(err) {
                        alert('Something went wrong!');
                    }
                });
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#department').val('');
                $('#paymentType').val('');
                $('#month').val('{{ \Carbon\Carbon::now()->format('Y-m') }}');
                $('.employeeCash').submit();
            });

            // Trigger filter on change
            $('#department, #paymentType, #month').on('change', function() {
                $('.employeeCash').submit();
            });

            // Print functionality
            $('#printReport').on('click', function() {
                printReport();
            });

            // Excel export functionality
            $('#exportExcel').on('click', function() {
                exportToExcel();
            });

            function printReport() {
                const printContent = document.getElementById('report-table').innerHTML;
                const originalContent = document.body.innerHTML;

                // Create a print-friendly version
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Employee Cash Report</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 20px;
                                color: #000;
                            }
                            .company-header {
                                text-align: center;
                                margin-bottom: 20px;
                            }
                            .company-header h1 {
                                margin: 0;
                                font-size: 22px;
                                font-weight: bold;
                            }
                            .report-title {
                                text-align: center;
                                margin: 20px 0;
                                font-size: 18px;
                                font-weight: bold;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 10px 0;
                                font-size: 12px;
                            }
                            th, td {
                                border: 1px solid #000;
                                padding: 8px;
                                text-align: left;
                            }
                            th {
                                background-color: #f2f2f2;
                                font-weight: bold;
                            }
                            .total-row {
                                font-weight: bold;
                                background-color: #e6e6e6;
                            }
                            .right { text-align: right; }
                            .center { text-align: center; }
                            .no-print { display: none; }
                            @media print {
                                body { margin: 0; }
                                .page-break { page-break-after: always; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="report-title">
                            Employee Cash Report - ${getSelectedMonthText()}
                        </div>
                        ${printContent}

                    </body>
                    </html>
                `);

                printWindow.document.close();
                printWindow.focus();

                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            }

            // Excel export functionality with inline CSS
            function exportToExcel() {
                try {
                    // Get filter values
                    const monthValue = $('#month').val();
                    const departmentValue = $('#department').val();
                    const paymentTypeValue = $('#paymentType').val();

                    const formattedMonth = monthValue ? new Date(monthValue + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : 'All Time';
                    const currentDepartment = departmentValue ? departmentValue : 'All Departments';
                    const currentPaymentType = paymentTypeValue ? paymentTypeValue.charAt(0).toUpperCase() + paymentTypeValue.slice(1) : 'All Types';
                    const currentDate = new Date().toLocaleDateString('en-GB');

                    // Get the table data from the current view
                    const table = document.querySelector('#report-table table');
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
            <td colspan="5" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px;">
                MULTI FABS LTD <br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="5" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Report Info -->
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold;">
                Employee Cash Summary: ${formattedMonth} ||
                Department: ${currentDepartment} ||
                Type: ${currentPaymentType}
            </td>
            <td colspan="2" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">
                Date: ${currentDate}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="5" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Table Header -->
        <tr>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">SL</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 30%;">Employee Name</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 20%;">Department</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 17%;">Payment Type</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 25%;">Total Amount</td>
        </tr>

        <!-- Table Rows -->
        ${getTableRowsHTML()}

        <!-- Total Rows -->
        ${getTotalRowsHTML()}
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

                    const fileName = `Employee_Cash_Report_${formattedMonth.replace(/\s+/g, '_')}.xls`;
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
                const rows = document.querySelectorAll('#report-table table tbody tr');
                let rowsHTML = '';

                // Check if there are any data rows
                if (rows.length === 0) {
                    return `
        <tr>
            <td colspan="5" style="border: 1px solid #000000; padding: 5px; text-align: center;">No records found.</td>
        </tr>
                    `;
                }

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 5) {
                        const sl = cells[0].textContent.trim();
                        const employeeName = cells[1].textContent.trim();
                        const department = cells[2].textContent.trim();
                        const paymentType = cells[3].textContent.trim();
                        const amount = cells[4].textContent.trim();

                        rowsHTML += `
                <tr>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${sl}</td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${employeeName}</td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${department}</td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${paymentType}</td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${amount}</td>
                </tr>
                        `;
                    }
                });

                return rowsHTML;
            }

            // Helper function to get total rows HTML
            function getTotalRowsHTML() {
                console.log('Getting total rows HTML for Employee Cash Report...');

                let totalRowsHTML = '';

                // Method 1: Try to get from tfoot
                const tfoot = document.querySelector('#report-table table tfoot');
                console.log('TFoot found:', tfoot);

                if (tfoot) {
                    const tfootRows = tfoot.querySelectorAll('tr');
                    console.log('TFoot rows:', tfootRows.length);

                    tfootRows.forEach(row => {
                        const cells = row.querySelectorAll('th, td');
                        console.log('TFoot cells:', cells.length);

                        if (cells.length >= 5) {
                            const label = cells[2]?.textContent?.trim() || '';
                            const amount = cells[4]?.textContent?.trim() || '0.00';

                            console.log('TFoot row:', { label, amount });

                            totalRowsHTML += `
        <tr>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${label}</td>
            <td style="border: 1px solid #000000; padding: 5px;"></td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${amount}</td>
        </tr>
                            `;
                        }
                    });

                    if (totalRowsHTML) {
                        return totalRowsHTML;
                    }
                }

                // Method 2: Try to calculate totals from data rows
                console.log('Calculating totals from data rows...');
                let calculatedReceive = 0;
                let calculatedReturn = 0;

                const dataRows = document.querySelectorAll('#report-table table tbody tr');
                dataRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 5) {
                        const paymentType = cells[3].textContent.trim().toLowerCase();
                        const amountText = cells[4].textContent.trim();

                        if (amountText && amountText !== '') {
                            const amount = parseFloat(amountText.replace(/,/g, '')) || 0;

                            if (paymentType === 'receive') {
                                calculatedReceive += amount;
                            } else if (paymentType === 'return') {
                                calculatedReturn += amount;
                            }
                        }
                    }
                });

                const calculatedNet = calculatedReceive - calculatedReturn;

                console.log('Calculated totals:', {
                    receive: calculatedReceive,
                    return: calculatedReturn,
                    net: calculatedNet
                });

                if (calculatedReceive !== 0 || calculatedReturn !== 0) {
                    return `
                <tr>
                    <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total Receive:</td>
                    <td style="border: 1px solid #000000; padding: 5px;"></td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedReceive.toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total Return:</td>
                    <td style="border: 1px solid #000000; padding: 5px;"></td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedReturn.toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Net Amount:</td>
                    <td style="border: 1px solid #000000; padding: 5px;"></td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedNet.toFixed(2)}</td>
                </tr>
                    `;
                }

                // Method 3: Try to get from hidden data element
                const totalsElement = document.getElementById('employeeCashTotals');
                if (totalsElement) {
                    const totalReceive = totalsElement.getAttribute('data-total-receive') || '0.00';
                    const totalReturn = totalsElement.getAttribute('data-total-return') || '0.00';
                    const netAmount = totalsElement.getAttribute('data-net-amount') || '0.00';

                    return `
                <tr>
                    <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total Receive:</td>
                    <td style="border: 1px solid #000000; padding: 5px;"></td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${totalReceive}</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total Return:</td>
                    <td style="border: 1px solid #000000; padding: 5px;"></td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${totalReturn}</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Net Amount:</td>
                    <td style="border: 1px solid #000000; padding: 5px;"></td>
                    <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">${netAmount}</td>
                </tr>
                    `;
                }

                // Method 4: Default fallback
                console.log('Using default totals');
                return `
            <tr>
                <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total Receive:</td>
                <td style="border: 1px solid #000000; padding: 5px;"></td>
                <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">0.00</td>
            </tr>
            <tr>
                <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Total Return:</td>
                <td style="border: 1px solid #000000; padding: 5px;"></td>
                <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">0.00</td>
            </tr>
            <tr>
                <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Net Amount:</td>
                <td style="border: 1px solid #000000; padding: 5px;"></td>
                <td style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">0.00</td>
            </tr>
                `;
            }

            function getSelectedMonthText() {
                const monthInput = document.getElementById('month').value;
                if (monthInput) {
                    const date = new Date(monthInput + '-01');
                    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                }
                return new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            }
        });
    </script>
@endsection


