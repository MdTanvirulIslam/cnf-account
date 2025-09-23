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
    <!-- Include SheetJS library for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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

            // Function to export to Excel
            function exportToExcel() {
                try {
                    // Get the table from inside the reportTable div
                    const table = document.querySelector('#reportTable table');

                    if (!table) {
                        alert('No table data found to export. Please try loading the report first.');
                        return;
                    }

                    // Create a new workbook
                    const wb = XLSX.utils.book_new();

                    // Convert table to worksheet
                    const ws = XLSX.utils.table_to_sheet(table);

                    // Apply number formatting for currency columns if needed
                    // This ensures Excel recognizes numbers as currency
                    const range = XLSX.utils.decode_range(ws['!ref']);
                    for (let R = range.s.r + 1; R <= range.e.r; ++R) {
                        // Assuming currency columns are the last 3 columns
                        for (let C = range.e.c - 2; C <= range.e.c; ++C) {
                            const cellAddress = XLSX.utils.encode_cell({r: R, c: C});
                            if (ws[cellAddress] && ws[cellAddress].v) {
                                // Format as number
                                ws[cellAddress].t = 'n';
                                // Optional: Add currency format
                                ws[cellAddress].z = '$#,##0.00';
                            }
                        }
                    }

                    // Auto-size columns for better Excel display
                    if (!ws['!cols']) ws['!cols'] = [];
                    for (let i = 0; i <= range.e.c; i++) {
                        ws['!cols'][i] = { width: 15 };
                    }

                    // Add worksheet to workbook
                    XLSX.utils.book_append_sheet(wb, ws, "Import Bill Summary");

                    // Generate Excel file and trigger download
                    const month = $('#month').val();
                    const monthFormatted = month ? new Date(month + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All_Time';
                    const fileName = `Import_Bill_Summary_${monthFormatted.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0,10)}.xlsx`;

                    XLSX.writeFile(wb, fileName);

                } catch (error) {
                    console.error("Error exporting to Excel:", error);
                    alert("Error exporting to Excel. Please try again.");
                }
            }
        });
    </script>
@endsection
