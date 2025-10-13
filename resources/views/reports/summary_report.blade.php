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
                    <strong>CASH RECEIVED AND PAYMENT STATEMENT FOR THE MONTH {{ $selectedMonth->format('M-Y') }}</strong>
                </div>
                <div class="right">
                    <strong>Date: {{ now()->timezone('Asia/Dhaka')->format('d/m/Y') }}</strong>
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
                    <td>TOTAL OPENING BALANCE {{ $selectedMonth->copy()->subMonth()->format('M') }} {{ $selectedMonth->copy()->subMonth()->startOfMonth()->format('d.m.Y') }}</td>
                    <td class="right {{ $previousMonthClosing < 0 ? 'negative' : '' }}">{{ number_format($previousMonthClosing, 2) }}</td>
                    <td></td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>CASH RECEIVED IN DHAKA BANK</td>
                    <td class="right">{{ number_format($dhakaBankReceived, 2) }}</td>
                    <td></td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>CASH RECEIVED</td>
                    <td class="right">{{ number_format($cashReceived, 2) }}</td>
                    <td></td>
                </tr>

                <tr class="total-row">
                    <td></td>
                    <td>OFFICE BALANCE ON {{ $selectedMonth->startOfMonth()->format('d.m.Y') }} TO {{ $selectedMonth->endOfMonth()->format('d.m.Y') }}</td>
                    <td></td>
                    <td class="left {{ $officeBalance < 0 ? 'negative' : '' }}">{{ number_format($officeBalance, 2) }}</td>
                </tr>

                <tr class="section-header">
                    <td colspan="4">EXPENSES</td>
                </tr>

                <tr>
                    <td>1</td>
                    <td>EXPORT DOCUMENTS MFL {{ $exportData['qty'] }} PCS ( As per Sheet)</td>
                    <td></td>
                    <td class="left">{{ number_format($exportData['total'], 2) }}</td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>IMPORT DOCUMENTS MFL {{ $importData['qty'] }} PCS ( As per Sheet)</td>
                    <td></td>
                    <td class="left">{{ number_format($importData['total'], 2) }}</td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>Office Maintenance Expenses (As Per Statement)</td>
                    <td></td>
                    <td class="left">{{ number_format($officeExpenses, 2) }}</td>
                </tr>
                </tbody>

                <tfoot>
                <tr class="total-row">
                    <th colspan="3" class="right">TOTAL BALANCE {{ $selectedMonth->format('M') }} CLOSING {{ $selectedMonth->endOfMonth()->format('d.m.Y') }}:</th>
                    <th class="right {{ $closingBalance < 0 ? 'negative' : '' }}">{{ number_format($closingBalance, 2) }}</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#excelBtn').on('click', function() {
                if ($('#month').val() !== "{{ $selectedMonth->format('Y-m') }}") {
                    downloadExcelFromCurrentPage();
                } else {
                    downloadExcel();
                }
            });

            function downloadExcelFromCurrentPage() {
                downloadExcel();
            }

            function downloadExcel() {
                try {
                    const table = document.querySelector('.invoice-table');
                    if (!table) {
                        alert('No data available to export.');
                        return;
                    }

                    const ws = XLSX.utils.table_to_sheet(table);
                    const colWidths = [
                        { wch: 5 },   // SL
                        { wch: 50 },  // DESCRIPTION
                        { wch: 15 },  // TOTAL TAKA
                        { wch: 15 }   // G.TOTAL TAKA
                    ];
                    ws['!cols'] = colWidths;

                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Monthly Summary Report');

                    const currentDate = new Date().toISOString().slice(0,10);
                    const selectedMonth = $('#month').val() || "{{ $selectedMonth->format('Y-m') }}";
                    const fileName = `Monthly_Summary_Report_${selectedMonth}_${currentDate}.xlsx`;

                    XLSX.writeFile(wb, fileName);
                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Error exporting to Excel. Please try again.');
                }
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
                const selectedMonth = new Date(data.selectedMonth.date);
                const prevMonth = new Date(selectedMonth);
                prevMonth.setMonth(prevMonth.getMonth() - 1);

                // Get first day of previous month
                const firstDayPrevMonth = new Date(prevMonth.getFullYear(), prevMonth.getMonth(), 1);

                // Get first day of current month
                const firstDayCurrentMonth = new Date(selectedMonth.getFullYear(), selectedMonth.getMonth(), 1);

                // Get last day of current month
                const lastDayCurrentMonth = new Date(selectedMonth.getFullYear(), selectedMonth.getMonth() + 1, 0);

                const formatDate = (date) => {
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}.${month}.${year}`;
                };

                const formatMonth = (date) => date.toLocaleDateString('en-GB', { month: 'short' });
                const formatCurrency = (amount) => parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                // Update header
                $('.invoice-info strong:first').html(`CASH RECEIVED AND PAYMENT STATEMENT FOR THE MONTH ${formatMonth(selectedMonth)}-${selectedMonth.getFullYear()}`);

                // Update table data with corrected dates
                $('.invoice-table tbody tr:eq(1) td:eq(1)').html(`TOTAL OPENING BALANCE ${formatMonth(prevMonth)} ${formatDate(firstDayPrevMonth)}`);
                $('.invoice-table tbody tr:eq(1) td:eq(2)').html(formatCurrency(data.previousMonthClosing)).toggleClass('negative', data.previousMonthClosing < 0);

                $('.invoice-table tbody tr:eq(2) td:eq(2)').html(formatCurrency(data.dhakaBankReceived));
                $('.invoice-table tbody tr:eq(3) td:eq(2)').html(formatCurrency(data.cashReceived));

                // Update office balance row with first day to last day
                $('.invoice-table tbody tr:eq(4) td:eq(1)').html(`OFFICE BALANCE ON ${formatDate(firstDayCurrentMonth)} TO ${formatDate(lastDayCurrentMonth)}`);
                $('.invoice-table tbody tr:eq(4) td:eq(3)').html(formatCurrency(data.officeBalance)).toggleClass('negative', data.officeBalance < 0);

                $('.invoice-table tbody tr:eq(6) td:eq(1)').html(`EXPORT DOCUMENTS MFL ${data.exportData.qty} PCS ( As per Sheet)`);
                $('.invoice-table tbody tr:eq(6) td:eq(3)').html(formatCurrency(data.exportData.total));

                $('.invoice-table tbody tr:eq(7) td:eq(1)').html(`IMPORT DOCUMENTS MFL ${data.importData.qty} PCS ( As per Sheet)`);
                $('.invoice-table tbody tr:eq(7) td:eq(3)').html(formatCurrency(data.importData.total));

                $('.invoice-table tbody tr:eq(8) td:eq(3)').html(formatCurrency(data.officeExpenses));

                // Update footer with last day of month
                $('.invoice-table tfoot tr th:eq(0)').html(`TOTAL BALANCE ${formatMonth(selectedMonth)} CLOSING ${formatDate(lastDayCurrentMonth)}:`);
                $('.invoice-table tfoot tr th:eq(1)').html(formatCurrency(data.closingBalance)).toggleClass('negative', data.closingBalance < 0);

                // Update print date
                $('.invoice-info .right strong').html(`Date: ${new Date().toLocaleDateString('en-GB')}`);
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
