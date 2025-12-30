@extends('layouts.layout')

@section('styles')
    <style>
        /* ✅ GLOBAL TABLE STYLES */
        table {
            width: 100%;
            border-collapse: collapse !important;
            table-layout: auto !important;
        }

        th, td {
            border: 1px solid #000 !important;
            text-align: center !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
            padding: 6px 8px;
            font-size: 13px;
        }

        /* ✅ FIX LAST COLUMN (NO BREAK, NO SHRINK) */
        th:last-child,
        td:last-child {
            min-width: 140px !important; /* ✅ FIXED WIDTH */
            max-width: 140px !important;
            white-space: nowrap !important;
        }

        /* ✅ HEADER */
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
        }

        /* ✅ PRINT MODE */
        @media print {

            body {
                margin: 10px;
                padding: 0;
                font-size: 12px;
            }

            table, th, td {
                border: 1px solid #000 !important;
                border-collapse: collapse !important;
            }

            th, td {
                padding: 4px 6px !important;
            }

            /* ✅ Force landscape */
            @page {
                size: landscape;
                margin: 10mm;
            }

            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { page-break-inside: avoid; }

            .no-print { display: none !important; }

            /* ✅ PRINT FIX FOR LAST COLUMN TOO */
            th:last-child,
            td:last-child {
                min-width: 150px !important;
                max-width: 150px !important;
            }
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
               ✅ PRINT FUNCTION - UPDATED
            ================================== */
            function printReport() {
                const monthInput = $('#month').val();
                const monthName = monthInput
                    ? new Date(monthInput + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
                    : 'All Time';

                const printWindow = window.open('', '_blank');

                // Get the HTML content
                const reportContent = document.getElementById('reportTable').innerHTML;

                printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
    <title>Export Bill - ${monthName}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
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

        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
            text-align: center !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
        }
        th {
            background: #f5f5f5 !important;
            font-weight: bold !important;
        }

        .total-row {
            font-weight: bold !important;
            background-color: #f0f0f0 !important;
        }

        @page {
            size: landscape;
            margin: 10mm;
        }

        @media print {
            body {
                margin: 10px;
                padding: 0;
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }

            table, th, td {
                border: 1px solid #000 !important;
            }

            thead {
                display: table-header-group !important;
            }

            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>
    ${reportContent}
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
               ✅ EXPORT EXCEL FUNCTION - SIMPLIFIED
            ================================== */
            function exportToExcel() {
                try {
                    const monthVal = $('#month').val();
                    const formattedMonth = monthVal
                        ? new Date(monthVal + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
                        : 'All_Time';

                    // Get the table content
                    const tableContent = document.getElementById('reportTable').innerHTML;

                    // Create simple HTML table for Excel
                    const excelHTML = `
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            border: 1px solid #000000;
            padding: 5px;
            text-align: center;
        }
        td {
            border: 1px solid #000000;
            padding: 5px;
            text-align: center;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .company-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .company-header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    ${tableContent}
</body>
</html>
                    `;

                    const blob = new Blob([excelHTML], {
                        type: "application/vnd.ms-excel"
                    });

                    const url = URL.createObjectURL(blob);
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = `Export_Bill_Summary_${formattedMonth.replace(/\s+/g, '_')}.xls`;

                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);

                    URL.revokeObjectURL(url);

                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Error exporting to Excel: ' + error.message);
                }
            }

        });
    </script>
@endsection
