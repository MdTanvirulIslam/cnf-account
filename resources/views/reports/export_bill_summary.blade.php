@extends('layouts.layout')

@section('styles')
    <style>
        /* ✅ Global table center alignment */
        table th, table td {
            text-align: center !important;
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing">

        <!-- Month Filter -->
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

        <!-- Report Table -->
        <div class="col-xl-12 layout-top-spacing dc-report-table" id="reportTable">
            @include('partials.exportBillSummaryTable', ['bills' => $bills, 'month' => $month])
        </div>

    </div>
@endsection



@section('scripts')
    <script>
        $(document).ready(function(){

            const originalMonth = "{{ $month }}";

            $('#month').on('change', function(){
                loadReportData($(this).val());
            });

            $('#resetBtn').on('click', function() {
                $('#month').val(originalMonth);
                loadReportData(originalMonth);
            });

            $('#printBtn').on('click', function() {
                printReport();
            });

            $('#excelBtn').on('click', function() {
                exportToExcel();
            });

            /* ================================
               ✅ Load report via AJAX
            ================================== */
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
                    error: function() {
                        $('#reportTable').html('<div class="alert alert-danger">Failed to load data.</div>');
                    }
                });
            }

            /* ================================
               ✅ PRINT FUNCTION
            ================================== */
            function printReport() {
                const monthInput = $('#month').val();
                const monthName = monthInput
                    ? new Date(monthInput + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
                    : 'All Time';

                const printWindow = window.open('', '_blank');

                printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
<title>Export Bill - ${monthName}</title>
<style>
    body { font-family: Arial; font-size: 12px; margin: 20px; }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    th, td {
        border: 1px solid #000;
        padding: 4px 6px;
        text-align: center;
        white-space: nowrap;
    }
    th { background: #f5f5f5; font-weight: bold; }

    @page { size: landscape; margin: 10mm; }
</style>
</head>
<body>
    ${document.getElementById('reportTable').innerHTML}
</body>
</html>
        `);

                printWindow.document.close();

                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 300);
            }

            /* ================================
               ✅ EXPORT EXCEL FUNCTION
            ================================== */
            function exportToExcel() {

                const monthVal = $('#month').val();
                const formattedMonth = monthVal
                    ? new Date(monthVal + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
                    : 'All_Time';

                const table = document.querySelector('#reportTable table');
                if (!table) return alert("No data available!");

                let html = `
<html><head><meta charset="UTF-8"></head><body>
<table style="border-collapse:collapse;width:100%">
`;

                html += table.outerHTML.replace(/<th/g, '<th style="text-align:center;border:1px solid #000;padding:5px;"');
                html += table.outerHTML.replace(/<td/g, '<td style="text-align:center;border:1px solid #000;padding:5px;"');

                html += `</table></body></html>`;

                const blob = new Blob([html], { type: "application/vnd.ms-excel" });
                const a = document.createElement("a");

                a.href = URL.createObjectURL(blob);
                a.download = `Export_Bill_Summary_${formattedMonth}.xls`;
                a.click();
            }

        });
    </script>
@endsection
