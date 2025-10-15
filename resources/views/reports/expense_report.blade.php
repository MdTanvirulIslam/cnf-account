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

        /* Hide print total in web view */
        .print-total {
            display: none !important;
        }

        @media print {
            .web-total {
                display: none !important;
            }
            .print-total {
                display: table-cell !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h3>Expense Report</h3>

                    <form id="filter-form" class="row g-3 mb-3">
                        <div class="col-md-2">
                            <input type="month" name="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-2">
                            <select name="category" id="category" class="form-control form-control-sm">
                                <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
                                @foreach($categories as $id => $cat)
                                    <option value="{{ $id }}" {{ $category == $id ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="sub_category" id="sub_category" class="form-control form-control-sm">
                                <option value="all" {{ $subCategory === 'all' ? 'selected' : '' }}>All Sub-Categories</option>
                                @foreach($subCategories as $id => $subCat)
                                    <option value="{{ $id }}" {{ $subCategory == $id ? 'selected' : '' }}>{{ $subCat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm me-1">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" id="reset-btn" class="btn btn-secondary btn-sm me-1">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="button" id="print-btn" class="btn btn-info btn-sm me-1">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button type="button" id="excel-btn" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </form>

                    <!-- Report Table -->
                    <div id="report-table">
                        @include('partials.expenseReportTable', [
                            'data' => $data,
                            'month' => $month,
                            'category' => $category,
                            'subCategory' => $subCategory,
                            'categories' => $categories,
                            'subCategories' => $subCategories,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function () {
            const originalMonth = $('#month').val();
            const originalCategory = $('#category').val();
            const originalSubCategory = $('#sub_category').val();

            // Filter form submission
            $('#filter-form').on('submit', function (e) {
                e.preventDefault();
                loadReportData($(this).serialize());
            });

            // Reset button click
            $('#reset-btn').on('click', function () {
                $('#month').val(originalMonth);
                $('#category').val('all');
                $('#sub_category').val('all');
                loadReportData({
                    month: originalMonth,
                    category: 'all',
                    sub_category: 'all'
                });
            });

            // Print button click
            $('#print-btn').on('click', function () {
                printReport();
            });

            // Excel button click
            $('#excel-btn').on('click', function () {
                exportToExcel();
            });

            // Load report data via AJAX
            function loadReportData(formData) {
                $.ajax({
                    url: "{{ route('expense.report') }}",
                    type: "GET",
                    data: formData,
                    beforeSend: function() {
                        $('#report-table').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                    },
                    success: function (res) {
                        $('#report-table').html(res.html);
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        $('#report-table').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
                    }
                });
            }

            // Print functionality
            function printReport() {
                const currentMonth = $('#month').val();
                const currentCategory = $('#category option:selected').text();

                // Create print-friendly HTML
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Expense Report - ${currentMonth}</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 20px;
                                color: #000;
                                line-height: 1.4;
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
                                font-size: 22px;
                                font-weight: bold;
                            }
                            .company-header p {
                                margin: 2px 0;
                                font-size: 13px;
                            }
                            .separator {
                                border: none;
                                border-top: 1px solid #222;
                                margin: 10px 0 18px 0;
                            }
                            .invoice-info {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 20px;
                                font-size: 14px;
                            }
                            .invoice-info div {
                                width: 48%;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 20px 0;
                                font-size: 14px;
                            }
                            th, td {
                                border: 1px solid #000;
                                padding: 8px 10px;
                                text-align: left;
                            }
                            th {
                                background-color: #f4f4f4;
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
                                background-color: #f9f9f9;
                            }
                            .print-footer {
                                margin-top: 30px;
                                padding-top: 10px;
                                border-top: 1px solid #333;
                                font-size: 12px;
                                color: #666;
                            }
                            /* Hide sub-category and note columns in print */
                            th:nth-child(4), td:nth-child(4),
                            th:nth-child(5), td:nth-child(5) {
                                display: none;
                            }
                            /* Hide web total, show print total */
                            .web-total {
                                display: none !important;
                            }
                            .print-total {
                                display: table-cell !important;
                            }
                            @media print {
                                body { margin: 15px; }
                                th:nth-child(4), td:nth-child(4),
                                th:nth-child(5), td:nth-child(5) {
                                    display: none;
                                }
                                .web-total {
                                    display: none !important;
                                }
                                .print-total {
                                    display: table-cell !important;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-container">
                            <div class="company-header">
                                <h1>MULTI FABS LTD</h1>
                                <p>(SELF C&F AGENTS)</p>
                                <p>314, SK. MUJIB ROAD, CHOWDHURY BHABAN (4TH FLOOR) AGRABAD, CHITTAGONG.</p>
                            </div>
                            <hr class="separator" />

                            <div class="invoice-info">
                                <div>
                                    <strong>Expense of </strong> ${getFormattedMonth(currentMonth)} ||
                                    Category: ${currentCategory === 'all' ? 'All' : currentCategory}
                                </div>
                                <div style="text-align: right;">
                                    <strong>Date:</strong> ${new Date().toLocaleDateString('en-GB')}
                                </div>
                            </div>

                            ${getTableForPrint()}

                            <div class="print-footer">
                                <p>Generated by Expense Management System</p>
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

            // Helper function to format month
            function getFormattedMonth(monthString) {
                if (!monthString) return 'All Time';
                const date = new Date(monthString + '-01');
                return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            }

            // Helper function to get table for print
            function getTableForPrint() {
                const table = document.querySelector('#report-table table');
                if (!table) return '<p>No data available</p>';

                const tableClone = table.cloneNode(true);

                // Remove any action buttons or unnecessary elements
                $(tableClone).find('.btn, .no-export').remove();

                return tableClone.outerHTML;
            }

            // Excel export functionality
            function exportToExcel() {
                const table = document.querySelector('#report-table table');
                if (!table) {
                    alert('No data available to export.');
                    return;
                }

                try {
                    const tableClone = table.cloneNode(true);

                    // Remove sub-category and note columns for Excel
                    $(tableClone).find('th:nth-child(4), td:nth-child(4)').remove();
                    $(tableClone).find('th:nth-child(5), td:nth-child(5)').remove();

                    // Remove action buttons
                    $(tableClone).find('.btn, .no-export').remove();

                    const ws = XLSX.utils.table_to_sheet(tableClone);
                    const colWidths = [
                        { wch: 8 },  // SL
                        { wch: 15 }, // Date
                        { wch: 20 }, // Category
                        { wch: 15 }  // Amount
                    ];
                    ws['!cols'] = colWidths;

                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Expense Report');

                    const currentMonth = $('#month').val();
                    const fileName = `Expense_Report_${currentMonth || 'all_time'}.xlsx`;
                    XLSX.writeFile(wb, fileName);

                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Error exporting to Excel. Please try again.');
                }
            }

            // Dynamic sub-category loading
            $('#category').on('change', function() {
                const categoryId = $(this).val();
                if (categoryId === 'all') {
                    $('#sub_category').val('all');
                }
            });
        });
    </script>
@endsection
