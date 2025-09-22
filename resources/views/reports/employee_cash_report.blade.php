@extends('layouts.layout')

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

                        <div class="col-md-4 form-group d-flex align-items-end">
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
            @include('partials.employeeCashReportTable', ['transactions' => $transactions, 'selectedMonth' => $selectedMonth ?? \Carbon\Carbon::now()])
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
                        <div style="margin-top: 30px; text-align: right; font-size: 12px;">
                            <p>Printed on: ${new Date().toLocaleDateString()}</p>
                        </div>
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

            function exportToExcel() {
                // Get table data
                const table = document.querySelector('.invoice-table');
                const data = [];

                // Add headers
                const headers = [];
                table.querySelectorAll('thead th').forEach(th => {
                    headers.push(th.innerText);
                });
                data.push(headers);

                // Add rows
                table.querySelectorAll('tbody tr').forEach(tr => {
                    const row = [];
                    tr.querySelectorAll('td').forEach(td => {
                        row.push(td.innerText);
                    });
                    data.push(row);
                });

                // Create worksheet
                const ws = XLSX.utils.aoa_to_sheet(data);

                // Create workbook
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Employee Cash Report');

                // Generate file name
                const fileName = `employee-cash-report-${getSelectedMonthText().toLowerCase().replace(' ', '-')}-${new Date().getTime()}.xlsx`;

                // Download file
                XLSX.writeFile(wb, fileName);
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


