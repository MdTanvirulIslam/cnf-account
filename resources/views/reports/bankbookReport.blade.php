@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Bank Book Report</h4>

                    <!-- FILTER FORM: keep your visual classes if you already have different design -->
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
                        @include('partials.bankbookReportTable', ['data' => $data])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Include SheetJS library for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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

            // Reload button
            $('#reloadBtn').on('click', function () {
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

                    // Apply number formatting for currency columns
                    const range = XLSX.utils.decode_range(ws['!ref']);
                    for (let R = range.s.r + 1; R <= range.e.r; ++R) {
                        // Format amount and balance columns (assuming they are the last two columns)
                        for (let C = range.e.c - 1; C <= range.e.c; ++C) {
                            const cellAddress = XLSX.utils.encode_cell({r: R, c: C});
                            if (ws[cellAddress] && ws[cellAddress].v) {
                                // Format as number
                                ws[cellAddress].t = 'n';
                                // Add currency format
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
                    XLSX.utils.book_append_sheet(wb, ws, "Bank Book Report");

                    // Generate Excel file and trigger download
                    const month = $('#month').val();
                    const bank = $('#bank').val();
                    const type = $('#type').val();

                    const monthFormatted = month ? new Date(month + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'All_Time';
                    const bankFormatted = bank === 'all' ? 'All_Banks' : bank.replace(/\s+/g, '_');
                    const typeFormatted = type === 'all' ? 'All_Types' : type.replace(/\s+/g, '_');

                    const fileName = `Bank_Book_Report_${monthFormatted.replace(/\s+/g, '_')}_${bankFormatted}_${typeFormatted}.xlsx`;

                    XLSX.writeFile(wb, fileName);

                } catch (error) {
                    console.error("Error exporting to Excel:", error);
                    alert("Error exporting to Excel. Please try again.");
                }
            }
        });
    </script>
@endsection
