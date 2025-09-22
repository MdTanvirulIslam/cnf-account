@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">

        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h3>Expense Report</h3>

                    <form id="filter-form" class="row g-3 mb-3">
                        <!-- Month -->
                        <div class="col-md-2">
                            <input type="month" name="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                        </div>

                        <!-- Category -->
                        <div class="col-md-2">
                            <select name="category" id="category" class="form-control form-control-sm">
                                <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
                                @foreach($categories as $id => $cat)
                                    <option value="{{ $id }}" {{ $category == $id ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sub-Category -->
                        <div class="col-md-2">
                            <select name="sub_category" id="sub_category" class="form-control form-control-sm">
                                <option value="all" {{ $subCategory === 'all' ? 'selected' : '' }}>All Sub-Categories</option>
                                @foreach($subCategories as $id => $subCat)
                                    <option value="{{ $id }}" {{ $subCategory == $id ? 'selected' : '' }}>{{ $subCat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
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
    <!-- Include SheetJS for Excel export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script>
        $(document).ready(function () {
            // Store original values for reset
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
                const currentSubCategory = $('#sub_category option:selected').text();

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
                                font-size: 14px;
                            }
                            th, td {
                                border: 1px solid #ddd;
                                padding: 10px;
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
                            .print-footer {
                                margin-top: 30px;
                                padding-top: 10px;
                                border-top: 1px solid #333;
                                font-size: 12px;
                                color: #666;
                            }
                            @media print {
                                body { margin: 0; }
                                .print-header { border-bottom-color: #000; }
                                th { background-color: #f0f0f0 !important; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-container">

                            ${document.getElementById('report-table').innerHTML}
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

            // Excel export functionality
            function exportToExcel() {
                const table = document.querySelector('#report-table table');

                if (!table) {
                    alert('No data available to export.');
                    return;
                }

                try {
                    // Clone table to avoid modifying original
                    const tableClone = table.cloneNode(true);

                    // Clean up table for Excel (remove any unnecessary elements)
                    $(tableClone).find('.btn, .no-export').remove();

                    // Convert table to worksheet
                    const ws = XLSX.utils.table_to_sheet(tableClone);

                    // Set column widths
                    const colWidths = [
                        { wch: 15 }, // Date
                        { wch: 20 }, // Category
                        { wch: 20 }, // Sub Category
                        { wch: 30 }, // Description
                        { wch: 15 }  // Amount
                    ];
                    ws['!cols'] = colWidths;

                    // Create workbook and append worksheet
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Expense Report');

                    // Generate filename
                    const currentMonth = $('#month').val();
                    const fileName = `Expense_Report_${currentMonth || 'all_time'}.xlsx`;

                    // Export to Excel
                    XLSX.writeFile(wb, fileName);

                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Error exporting to Excel. Please try again.');
                }
            }

            // Dynamic sub-category loading (optional enhancement)
            $('#category').on('change', function() {
                const categoryId = $(this).val();
                if (categoryId === 'all') {
                    // Reset sub-categories to show all
                    $('#sub_category').val('all');
                }
                // You can add AJAX sub-category loading here if needed
            });
        });
    </script>
@endsection
