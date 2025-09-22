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
    <!-- Include SheetJS for Excel export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

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

            // Excel export functionality
            function exportToExcel() {
                const table = document.querySelector('#reportTable table');

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

                    // Set column widths (adjust based on your table structure)
                    const colWidths = [
                        { wch: 20 }, // Buyer
                        { wch: 15 }, // B/E No
                        { wch: 15 }, // Bill No
                        { wch: 12 }, // Date
                        { wch: 25 }, // Product
                        { wch: 12 }, // Quantity
                        { wch: 15 }, // Unit Price
                        { wch: 15 }, // Amount
                        { wch: 10 }  // Currency
                    ];
                    ws['!cols'] = colWidths;

                    // Create workbook and append worksheet
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Export Bill Summary');

                    // Generate filename with current month
                    const currentMonth = $('#month').val();
                    const monthName = currentMonth ? new Date(currentMonth + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : 'All_Time';
                    const fileName = `Export_Bill_Summary_${monthName.replace(' ', '_')}.xlsx`;

                    // Export to Excel
                    XLSX.writeFile(wb, fileName);

                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Error exporting to Excel. Please try again.');
                }
            }
        });
    </script>
@endsection
