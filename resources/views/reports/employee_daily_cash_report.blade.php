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

        @media print {
            .no-print {
                display: none !important;
            }

            /* Ensure table footer stays on last page */
            .invoice-table {
                page-break-inside: auto;
            }

            .invoice-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .invoice-table tbody {
                page-break-inside: auto;
            }

            .invoice-table tfoot {
                display: table-footer-group;
                page-break-inside: avoid;
            }

            /* Force footer to bottom of last page */
            .total-row {
                page-break-inside: avoid;
                page-break-after: always;
            }

            /* Ensure proper page breaks */
            .invoice-table {
                border-collapse: collapse;
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Employee Daily Cash Report</h5>

                    <!-- Filter Form -->
                    <form id="filterForm" class="row g-3 employeeDailyCash">
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
                            <label for="employee_id">Employee Name</label>
                            <select name="employee_id" id="employee_id" class="form-control form-control-sm">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
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
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control form-control-sm"
                                   value="{{ $startDate->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control form-control-sm"
                                   value="{{ $endDate->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
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
                    </form>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="col-xl-12 layout-top-spacing" id="reportTable">
            @include('partials.employeeDailyCashReportTable', ['dailyTransactions' => $dailyTransactions, 'startDate' => $startDate, 'endDate' => $endDate])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            // Store original values for reset
            const originalDepartment = "";
            const originalEmployeeId = "";
            const originalPaymentType = "";
            const originalStartDate = "{{ $startDate->format('Y-m-d') }}";
            const originalEndDate = "{{ $endDate->format('Y-m-d') }}";

            // AJAX Filter
            $('#filterForm').on('submit', function(e){
                e.preventDefault();
                loadReportData($(this).serialize());
            });

            // Reset button
            $('#resetBtn').on('click', function() {
                // Reset form values
                $('#department').val(originalDepartment);
                $('#employee_id').val(originalEmployeeId);
                $('#paymentType').val(originalPaymentType);
                $('#start_date').val(originalStartDate);
                $('#end_date').val(originalEndDate);

                // Reload report with default values
                loadReportData({
                    department: originalDepartment,
                    employee_id: originalEmployeeId,
                    paymentType: originalPaymentType,
                    start_date: originalStartDate,
                    end_date: originalEndDate
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

            // Trigger filter on change with debounce
            let filterTimeout;
            $('#department, #employee_id, #paymentType, #start_date, #end_date').on('change', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    $('#filterForm').submit();
                }, 500);
            });

            // Function to load report data
            function loadReportData(formData) {
                $.ajax({
                    url: "{{ route('employee-daily-cash-report.filter') }}",
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $('#reportTable').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                    },
                    success: function(response){
                        if (response.html) {
                            $('#reportTable').html(response.html);
                        } else {
                            $('#reportTable').html('<div class="alert alert-danger">Invalid response format</div>');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#reportTable').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
                    }
                });
            }

            // Print functionality
            function printReport() {
                // Get the report content
                const reportContent = document.getElementById('reportTable').cloneNode(true);

                // Remove tfoot from the table to prevent repetition
                const table = reportContent.querySelector('.invoice-table');
                const tfoot = table.querySelector('tfoot');
                const grandTotalRow = tfoot ? tfoot.innerHTML : '';

                // Remove footer note to prevent repetition
                const footerNote = reportContent.querySelector('.footer-note');
                const summaryContent = footerNote ? footerNote.innerHTML : '';

                if (tfoot) {
                    tfoot.remove();
                }
                if (footerNote) {
                    footerNote.remove();
                }

                // Create print window
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Employee Daily Cash Report</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    color: #000;
                    font-size: 12px;
                }
                .print-container {
                    max-width: 100%;
                }
                .company-header {
                    text-align: center;
                    margin-bottom: 15px;
                }
                .company-header h1 {
                    margin: 0;
                    font-size: 20px;
                    font-weight: bold;
                }
                .company-header p {
                    margin: 2px 0;
                    font-size: 12px;
                }
                .invoice-info {
                    display: flex;
                    justify-content: space-between;
                    margin: 10px 0;
                    font-size: 12px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                    font-size: 11px;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 6px;
                    text-align: left;
                }
                th {
                    background-color: #f5f5f5;
                    font-weight: bold;
                }
                .right {
                    text-align: right;
                }
                .center {
                    text-align: center;
                }
                .total-row {
                    font-weight: bold;
                    background-color: #e9ecef;
                }
                .footer-note {
                    margin-top: 20px;
                    font-size: 11px;
                }

                /* Print-specific styles */
                @media print {
                    body {
                        margin: 0.5cm;
                        font-size: 11px;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }

                    .invoice-table {
                        page-break-inside: auto;
                    }

                    tr {
                        page-break-inside: avoid;
                        page-break-after: auto;
                    }

                    thead {
                        display: table-header-group;
                    }

                    .total-row {
                        background: #e9ecef !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }

                    th {
                        background-color: #f5f5f5 !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }

                    @page {
                        margin: 1cm;
                        size: portrait;
                    }

                    @page :first {
                        margin-top: 1.5cm;
                    }

                    /* Prevent page break immediately before grand total */
                    .grand-total-section {
                        page-break-before: avoid;
                        page-break-inside: avoid;
                    }
                }

                .print-footer {
                    margin-top: 30px;
                    padding-top: 10px;
                    border-top: 1px solid #333;
                    font-size: 10px;
                    color: #666;
                }
                .no-print {
                    display: none !important;
                }
            </style>
        </head>
        <body>
            <div class="print-container">
                ${reportContent.innerHTML}

                <!-- Add grand total as a separate table at the end -->
                ${grandTotalRow ? `
                <div class="grand-total-section">
                    <table class="invoice-table">
                        <tbody>
                            ${grandTotalRow}
                        </tbody>
                    </table>
                </div>
                ` : ''}

                <!-- Add summary as separate element -->
                ${summaryContent ? `
                <div class="footer-note">
                    ${summaryContent}
                </div>
                ` : ''}

                <div class="print-footer">
                    <p>Generated by DifferentCoder | www.differentcoder.com</p>
                </div>
            </div>
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    }, 300);
                };
            <\/script>
        </body>
        </html>
    `);

                printWindow.document.close();
            }

            // Excel export functionality with inline CSS
            function exportToExcel() {
                try {
                    // Get filter values
                    const departmentValue = $('#department').val();
                    const employeeIdValue = $('#employee_id').val();
                    const paymentTypeValue = $('#paymentType').val();
                    const startDateValue = $('#start_date').val();
                    const endDateValue = $('#end_date').val();

                    const currentDepartment = departmentValue ? departmentValue : 'All Departments';
                    const currentEmployee = employeeIdValue ? $('#employee_id option:selected').text() : 'All Employees';
                    const currentPaymentType = paymentTypeValue ? paymentTypeValue.charAt(0).toUpperCase() + paymentTypeValue.slice(1) : 'All Types';
                    const currentDate = new Date().toLocaleDateString('en-GB');

                    // Format date range
                    const formattedStartDate = startDateValue ? new Date(startDateValue).toLocaleDateString('en-GB') : '';
                    const formattedEndDate = endDateValue ? new Date(endDateValue).toLocaleDateString('en-GB') : '';
                    const dateRange = formattedStartDate && formattedEndDate ? `${formattedStartDate} to ${formattedEndDate}` : 'All Time';

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
            <td colspan="7" style="border: 1px solid #000000; padding: 10px; text-align: center; font-weight: bold; font-size: 16px;">
                MULTI FABS LTD <br/>
                (SELF C&F AGENTS)<br/>
                314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="7" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Report Info -->
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 5px; text-align: left; font-weight: bold;">
                Employee Daily Cash Summary: ${dateRange} ||
                Department: ${currentDepartment} ||
                Employee: ${currentEmployee} ||
                Type: ${currentPaymentType}
            </td>
            <td colspan="3" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">
                Report Date: ${currentDate}
            </td>
        </tr>

        <!-- Empty row -->
        <tr>
            <td colspan="7" style="border: none; padding: 5px;"></td>
        </tr>

        <!-- Table Header -->
        <tr>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">SL</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 12%;">Date</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 25%;">Employee Name</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Department</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Receive Amount</td>
            <td style="border: 1px solid #000000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">Return Amount</td>
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

                    const fileName = `Employee_Daily_Cash_Report_${dateRange.replace(/\s+/g, '_')}.xls`;
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
            <td colspan="7" style="border: 1px solid #000000; padding: 5px; text-align: center;">No records found.</td>
        </tr>
                    `;
                }

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 7) {
                        const sl = cells[0].textContent.trim();
                        const date = cells[1].textContent.trim();
                        const employeeName = cells[2].textContent.trim();
                        const department = cells[3].textContent.trim();
                        const receiveAmount = cells[4].textContent.trim();
                        const returnAmount = cells[5].textContent.trim();
                        const finalAmount = cells[6].textContent.trim();

                        rowsHTML += `
        <tr>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${sl}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${date}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${employeeName}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${department}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${receiveAmount}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${returnAmount}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center;">${finalAmount}</td>
        </tr>
                        `;
                    }
                });

                return rowsHTML;
            }

            // Helper function to get total row HTML
            function getTotalRowHTML() {
                console.log('Getting total row HTML for Employee Daily Cash Report...');

                // Method 1: Try to get from tfoot
                const tfoot = document.querySelector('#reportTable table tfoot');
                console.log('TFoot found:', tfoot);

                if (tfoot) {
                    const tfootRows = tfoot.querySelectorAll('tr');
                    console.log('TFoot rows:', tfootRows.length);

                    for (let row of tfootRows) {
                        const cells = row.querySelectorAll('th, td');
                        console.log('TFoot cells:', cells.length);

                        if (cells.length >= 7) {
                            const receiveTotal = cells[4]?.textContent?.trim() || '0.00';
                            const returnTotal = cells[5]?.textContent?.trim() || '0.00';
                            const finalTotal = cells[6]?.textContent?.trim() || '0.00';

                            console.log('TFoot totals:', { receiveTotal, returnTotal, finalTotal });

                            return `
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Grand Total:</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${receiveTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${returnTotal}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${finalTotal}</td>
        </tr>
                            `;
                        }
                    }
                }

                // Method 2: Try to calculate totals from data rows
                console.log('Calculating totals from data rows...');
                let calculatedReceive = 0;
                let calculatedReturn = 0;
                let calculatedFinal = 0;

                const dataRows = document.querySelectorAll('#reportTable table tbody tr');
                dataRows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length === 7) {
                        const receiveText = cells[4].textContent.trim();
                        const returnText = cells[5].textContent.trim();
                        const finalText = cells[6].textContent.trim();

                        if (receiveText && receiveText !== '') {
                            calculatedReceive += parseFloat(receiveText.replace(/,/g, '')) || 0;
                        }
                        if (returnText && returnText !== '') {
                            calculatedReturn += parseFloat(returnText.replace(/,/g, '')) || 0;
                        }
                        if (finalText && finalText !== '') {
                            calculatedFinal += parseFloat(finalText.replace(/,/g, '')) || 0;
                        }
                    }
                });

                console.log('Calculated totals:', {
                    receive: calculatedReceive,
                    return: calculatedReturn,
                    final: calculatedFinal
                });

                if (calculatedReceive !== 0 || calculatedReturn !== 0 || calculatedFinal !== 0) {
                    return `
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Grand Total:</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedReceive.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedReturn.toFixed(2)}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${calculatedFinal.toFixed(2)}</td>
        </tr>
                    `;
                }

                // Method 3: Try to get from hidden data element
                const totalsElement = document.getElementById('employeeDailyCashTotals');
                if (totalsElement) {
                    const totalReceive = totalsElement.getAttribute('data-total-receive') || '0.00';
                    const totalReturn = totalsElement.getAttribute('data-total-return') || '0.00';
                    const totalFinal = totalsElement.getAttribute('data-total-final') || '0.00';

                    return `
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Grand Total:</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${totalReceive}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${totalReturn}</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">${totalFinal}</td>
        </tr>
                    `;
                }

                // Method 4: Default fallback
                console.log('Using default totals');
                return `
        <tr>
            <td colspan="4" style="border: 1px solid #000000; padding: 5px; text-align: right; font-weight: bold;">Grand Total:</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">0.00</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">0.00</td>
            <td style="border: 1px solid #000000; padding: 5px; text-align: center; font-weight: bold;">0.00</td>
        </tr>
                `;
            }

            // Helper function to format date
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
        });
    </script>
@endsection
