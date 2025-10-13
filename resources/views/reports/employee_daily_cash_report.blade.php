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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
                    <p>Generated by DifferentCoder | Printed on: ${new Date().toLocaleString()}</p>
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

            // Excel export functionality
            function exportToExcel() {
                const table = document.querySelector('#reportTable .invoice-table');

                if (!table) {
                    alert('No data available to export.');
                    return;
                }

                try {
                    // Clone table to avoid modifying original
                    const tableClone = table.cloneNode(true);

                    // Clean up table for Excel (remove action buttons if any)
                    $(tableClone).find('.btn, .no-export, .actions').remove();

                    // Convert table to worksheet
                    const ws = XLSX.utils.table_to_sheet(tableClone);

                    // Set column widths
                    const colWidths = [
                        { wch: 8 },  // SL
                        { wch: 12 }, // Date
                        { wch: 20 }, // Employee Name
                        { wch: 15 }, // Department
                        { wch: 15 }, // Receive Amount
                        { wch: 15 }, // Return Amount
                        { wch: 15 }  // Final Amount
                    ];
                    ws['!cols'] = colWidths;

                    // Create workbook and append worksheet
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Employee Daily Cash Report');

                    // Generate filename with current filters
                    const currentDate = new Date().toISOString().slice(0,10);
                    const currentDepartment = $('#department').val() ? $('#department option:selected').text().substring(0, 10) : 'all';
                    const fileName = `Employee_Daily_Cash_Report_${currentDepartment}_${currentDate}.xlsx`;

                    // Export to Excel
                    XLSX.writeFile(wb, fileName);

                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Error exporting to Excel. Please try again.');
                }
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
