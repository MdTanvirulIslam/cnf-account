@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Export Bill Report</h5>

                    <form id="filterForm" class="row g-3">
                        <!-- Buyer -->
                        <div class="col-md-2 form-group">
                            <label>Buyer</label>
                            <select name="buyer" id="buyer" class="form-control form-control-sm">
                                <option value="all">All Buyers</option>
                                @foreach($buyers as $id => $name)
                                    <option value="{{ $id }}" {{ $buyerId == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- B/E No -->
                        <div class="col-md-2 form-group">
                            <label>B/E No</label>
                            <select name="be_no" id="beNo" class="form-control form-control-sm">
                                <option value="all">All B/E Nos</option>
                                @foreach($beNos as $be)
                                    <option value="{{ $be }}" {{ $beNo == $be ? 'selected' : '' }}>{{ $be }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bill No -->
                        <div class="col-md-2 form-group">
                            <label>Bill No</label>
                            <select name="bill_no" id="billNo" class="form-control form-control-sm">
                                <option value="all">All Bill Nos</option>
                                @foreach($billNos as $bill)
                                    <option value="{{ $bill }}" {{ $billNo == $bill ? 'selected' : '' }}>{{ $bill }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bill Date -->
                        <div class="col-md-2 form-group">
                            <label>Date</label>
                            <input type="date" name="bill_date" id="billDate" value="{{ $billDate }}" class="form-control form-control-sm">
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
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing" id="reportTable">
            @include('partials.exportBillReportTable', ['exportBills' => $exportBills, 'grandTotal' => $grandTotal])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Store original values for reset (last bill's values)
            const originalBuyerId = "{{ $lastBill->buyer_id ?? 'all' }}";
            const originalBeNo = "{{ $lastBill->be_no ?? 'all' }}";
            const originalBillNo = "{{ $lastBill->bill_no ?? 'all' }}";
            const originalBillDate = "{{ \Carbon\Carbon::parse($lastBill?->bill_date ?? now())->format('Y-m-d') }}";

            // Store all options for reset
            const allBeNos = @json($allBeNos);
            const allBillNos = @json($allBillNos);

            // AJAX Filter
            $('#filterForm').on('submit', function(e){
                e.preventDefault();
                loadReportData($(this).serialize());
            });

            // Reset button - restore to last bill's values
            $('#resetBtn').on('click', function() {
                $('#buyer').val(originalBuyerId);
                $('#beNo').val(originalBeNo);
                $('#billNo').val(originalBillNo);
                $('#billDate').val(originalBillDate);

                populateDropdown('#beNo', allBeNos, originalBeNo);
                populateDropdown('#billNo', allBillNos, originalBillNo);

                loadReportData({
                    buyer: originalBuyerId,
                    be_no: originalBeNo,
                    bill_no: originalBillNo,
                    bill_date: originalBillDate
                });
            });

            // Print button
            $('#printBtn').on('click', function() {
                printReport();
            });

            // When any dropdown changes, update dependent dropdowns
            $('#buyer, #beNo, #billNo').on('change', function(){
                updateDependentOptions();
            });

            // Function to update dependent dropdowns
            function updateDependentOptions() {
                const buyerId = $('#buyer').val();
                const beNo = $('#beNo').val();
                const billNo = $('#billNo').val();

                $.ajax({
                    url: "{{ route('exportBill.dependent') }}",
                    type: "GET",
                    data: {
                        buyer: buyerId,
                        be_no: beNo,
                        bill_no: billNo
                    },
                    success: function(res){
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

                if (options.includes(selectedValue) && selectedValue !== 'all') {
                    dropdown.val(selectedValue);
                } else {
                    dropdown.val('all');
                }
            }

            // Function to load report data
            function loadReportData(formData) {
                $.ajax({
                    url: "{{ route('export.bill.report') }}",
                    type: "GET",
                    data: formData,
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
                const currentBuyer = $('#buyer option:selected').text();
                const currentBeNo = $('#beNo option:selected').text();
                const currentBillNo = $('#billNo option:selected').text();
                const currentBillDate = $('#billDate').val();

                // Get the report content
                const reportContent = document.getElementById('reportTable').innerHTML;

                // Fix the footer colspan in the content
                let fixedContent = reportContent.replace(
                    /<td colspan="2" class="right">TOTAL AMOUNT<\/td>/g,
                    '<td colspan="2" class="center" style="text-align: center;">TOTAL AMOUNT</td>'
                );

                // Create print-friendly HTML
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Export Bill Report</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 20px;
                                color: #000;
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
                                color:#333;
                            }
                            .invoice-info {
                                display: flex;
                                justify-content: space-between;
                                margin-top: 10px;
                                font-size: 14px;
                                margin-bottom: 10px;
                            }
                            .invoice-info div {
                                width: 48%;
                            }
                            h3 {
                                margin-top: 20px;
                                font-size: 15px;
                                text-transform: uppercase;
                                margin-bottom: 15px;
                            }
                            .info-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 10px 0 20px;
                                font-size: 13px;
                            }
                            .info-table td {
                                border: 1px solid #222;
                                padding: 6px;
                                vertical-align: top;
                            }
                            .info-key {
                                font-weight: bold;
                                width: 10%;
                            }
                            .info-value {
                                width: 40%;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 20px 0;
                                font-size: 12px;
                            }
                            th, td {
                                border: 1px solid #000;
                                padding: 8px;
                            }
                            th {
                                background-color: #f5f5f5;
                                font-weight: bold;
                            }
                            .right {
                                text-align: right;
                            }
                            .center {
                                text-align: center !important;
                            }
                            .left {
                                text-align: left !important;
                            }
                            .total-row {
                                font-weight: bold;
                                background-color: #e9ecef;
                            }
                            .total-row td {
                                font-weight: bold;
                                background-color: #f9f9f9 !important;
                            }
                            .print-footer {
                                margin-top: 30px;
                                padding-top: 10px;
                                border-top: 1px solid #333;
                                font-size: 11px;
                                color: #666;
                                text-align: center;
                            }
                            .grand-total {
                                margin-top: 20px;
                                font-size: 14px;
                                font-weight: bold;
                                text-align: center;
                                border-top: 1px solid #000;
                                padding-top: 10px;
                            }
                            .no-print { display: none; }

                            /* Specific table alignment for print */
                            .invoice-table {
                                width: 100%;
                                border-collapse: collapse;
                            }
                            .invoice-table th:nth-child(1),
                            .invoice-table td:nth-child(1) {
                                text-align: center;
                                width: 5%;
                            }
                            .invoice-table th:nth-child(2),
                            .invoice-table td:nth-child(2) {
                                text-align: left;
                                width: 65%;
                            }
                            .invoice-table th:nth-child(3),
                            .invoice-table td:nth-child(3),
                            .invoice-table th:nth-child(4),
                            .invoice-table td:nth-child(4) {
                                text-align: center;
                                width: 15%;
                            }

                            /* Fix total row alignment */
                            .invoice-table .total-row td:first-child {
                                text-align: center !important;
                            }
                            .invoice-table .total-row td:nth-child(2) {
                                text-align: center !important;
                            }

                            @media print {
                                body {
                                    margin: 15px;
                                    font-size: 12px;
                                }
                                .company-header h1 {
                                    font-size: 20px;
                                }
                                .invoice-table {
                                    page-break-inside: avoid;
                                }
                                th {
                                    background-color: #f0f0f0 !important;
                                }
                                .table-responsive {
                                    overflow: visible !important;
                                }

                                /* Ensure description column is left-aligned in print */
                                .invoice-table th:nth-child(2),
                                .invoice-table td:nth-child(2) {
                                    text-align: left !important;
                                }
                                .invoice-table th:nth-child(1),
                                .invoice-table td:nth-child(1),
                                .invoice-table th:nth-child(3),
                                .invoice-table td:nth-child(3),
                                .invoice-table th:nth-child(4),
                                .invoice-table td:nth-child(4) {
                                    text-align: center !important;
                                }

                                /* Fix total row in print */
                                .total-row td {
                                    background-color: #e9ecef !important;
                                }
                                .invoice-table .total-row td[colspan] {
                                    text-align: center !important;
                                }
                                .invoice-table .total-row td.right {
                                    text-align: center !important;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-container">
                            ${fixedContent}
                            <div class="print-footer">
                                <p>Generated by DifferentCoder || www.differentcoder.com</p>
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
        });
    </script>
@endsection
