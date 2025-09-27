@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Import Bill Report</h5>

                    <form id="filterForm" class="row g-3 BankBook">
                        <!-- L/C No -->
                        <div class="col-md-2 form-group">
                            <label>L/C No</label>
                            <select name="lcNo" id="lcNo" class="form-control form-control-sm">
                                <option value="all">All</option>
                                @foreach($allLcNos as $lc)
                                    <option value="{{ $lc }}" {{ $lcNo == $lc ? 'selected' : '' }}>{{ $lc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- B/E No -->
                        <div class="col-md-2 form-group">
                            <label>B/E No</label>
                            <select name="be_no" id="beNo" class="form-control form-control-sm">
                                <option value="all">All</option>
                                @foreach($allBeNos as $be)
                                    <option value="{{ $be }}" {{ $beNo == $be ? 'selected' : '' }}>{{ $be }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bill No -->
                        <div class="col-md-2 form-group">
                            <label>Bill No</label>
                            <select name="bill_no" id="billNo" class="form-control form-control-sm">
                                <option value="all">All</option>
                                @foreach($allBillNos as $bill)
                                    <option value="{{ $bill }}" {{ $billNo == $bill ? 'selected' : '' }}>{{ $bill }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bill Date -->
                        <div class="col-md-2 form-group">
                            <label>Date</label>
                            <input type="date" name="billDate" id="billDate" value="{{ $billDate }}" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm me-1">
                                <i class="fas fa-filter"></i> Filter
                            </button>
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

        <div class="col-xl-12 layout-top-spacing" id="reportTable">
            @include('partials.importBillReportTable', ['importBills'=>$importBills])
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Include SheetJS for Excel export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script>
        $(document).ready(function() {
            // Store original values for reset (last bill's values)
            const originalLcNo = "{{ $lastBill->lc_no ?? 'all' }}";
            const originalBeNo = "{{ $lastBill->be_no ?? 'all' }}";
            const originalBillNo = "{{ $lastBill->bill_no ?? 'all' }}";
            const originalBillDate = "{{ $lastBill?->bill_date ? \Carbon\Carbon::parse($lastBill->bill_date)->format('Y-m-d') : '' }}";


            // Store all options for reset
            const allLcNos = @json($allLcNos);
            const allBeNos = @json($allBeNos);
            const allBillNos = @json($allBillNos);

            // AJAX Filter
            $('#filterForm').on('submit', function(e){
                e.preventDefault();
                loadReportData($(this).serialize());
            });

            // Reset button - restore to last bill's values
            $('#resetBtn').on('click', function() {
                // Reset form values to last bill's values
                $('#lcNo').val(originalLcNo);
                $('#beNo').val(originalBeNo);
                $('#billNo').val(originalBillNo);
                $('#billDate').val(originalBillDate);

                // Reset dropdown options to show ALL options
                populateDropdown('#lcNo', allLcNos, originalLcNo);
                populateDropdown('#beNo', allBeNos, originalBeNo);
                populateDropdown('#billNo', allBillNos, originalBillNo);

                // Reload report with last bill's values
                loadReportData({
                    lcNo: originalLcNo,
                    be_no: originalBeNo,
                    bill_no: originalBillNo,
                    billDate: originalBillDate
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

            // When any dropdown changes, update dependent dropdowns
            $('#lcNo, #beNo, #billNo').on('change', function(){
                updateDependentOptions();
            });

            // Function to update dependent dropdowns
            function updateDependentOptions() {
                const lcNo = $('#lcNo').val();
                const beNo = $('#beNo').val();
                const billNo = $('#billNo').val();

                $.ajax({
                    url: "{{ route('importBill.dependent') }}",
                    type: "GET",
                    data: {
                        lcNo: lcNo,
                        be_no: beNo,
                        bill_no: billNo
                    },
                    success: function(res){
                        // Only update dropdowns that are set to "all"
                        if ($('#lcNo').val() === 'all') {
                            populateDropdown('#lcNo', res.lcNos, 'all');
                        }
                        if ($('#beNo').val() === 'all') {
                            populateDropdown('#beNo', res.beNos, 'all');
                        }
                        if ($('#billNo').val() === 'all') {
                            populateDropdown('#billNo', res.billNos, 'all');
                        }
                    }
                });
            }

            // Function to populate a dropdown
            function populateDropdown(selector, options, selectedValue) {
                const dropdown = $(selector);
                const currentVal = dropdown.val();

                dropdown.empty().append('<option value="all">All</option>');

                options.forEach(function(value) {
                    dropdown.append('<option value="' + value + '">' + value + '</option>');
                });

                // Set selected value
                if (options.includes(selectedValue) && selectedValue !== 'all') {
                    dropdown.val(selectedValue);
                } else {
                    dropdown.val('all');
                }
            }

            // Function to load report data
            function loadReportData(formData) {
                $.ajax({
                    url: "{{ route('import.bill.report') }}",
                    type: "GET",
                    data: formData,
                    beforeSend: function() {
                        $('#reportTable').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                    },
                    success: function(res){
                        $('#reportTable').html(res.html);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#reportTable').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
                    }
                });
            }

            // Print functionality
            function printReport() {
                const currentLcNo = $('#lcNo option:selected').text();
                const currentBeNo = $('#beNo option:selected').text();
                const currentBillNo = $('#billNo option:selected').text();
                const currentBillDate = $('#billDate').val();

                // Create print-friendly HTML
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Import Bill Report</title>
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
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-container">

                            ${document.getElementById('reportTable').innerHTML}
                            <div class="print-footer">
                                <p>Generated by Import Bill Management System</p>
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
                        { wch: 15 }, // L/C No
                        { wch: 15 }, // B/E No
                        { wch: 15 }, // Bill No
                        { wch: 12 }, // Bill Date
                        { wch: 20 }, // Supplier
                        { wch: 15 }, // Amount
                        { wch: 15 }, // Currency
                        { wch: 20 }  // Description
                    ];
                    ws['!cols'] = colWidths;

                    // Create workbook and append worksheet
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Import Bill Report');

                    // Generate filename with current filters
                    const currentLcNo = $('#lcNo').val() !== 'all' ? $('#lcNo').val() : 'all';
                    const fileName = `Import_Bill_Report_${currentLcNo}_${new Date().toISOString().slice(0,10)}.xlsx`;

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
