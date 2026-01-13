@extends('layouts.layout')
@section('styles')
    <style>
        .company-header { text-align: center; }
        .company-header h1 { margin: 0; font-size: 22px; font-weight: bold; }
        .company-header p { margin: 2px 0; font-size: 13px; color:#333; }

        .invoice-info { display: flex; justify-content: space-between; margin-top: 10px; font-size: 14px; }
        .invoice-info div { width: 48%; }

        h3 { margin-top: 20px; font-size: 15px; text-transform: uppercase; }

        .info-table { width: 100%; border-collapse: collapse; margin: 10px 0 20px; font-size: 13px; }
        .info-table td { border: 1px solid #222; padding: 6px; vertical-align: top; }
        .info-key {  font-weight: bold; width: 10%; }
        .info-value { width: 40%; }

        .invoice-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .invoice-table th, .invoice-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .invoice-table th {
            background-color: #f4f4f4;
        }
        .right { text-align: right; }
        .center { text-align: center !important; }
        .left { text-align: left !important; }
        .total-row td { font-weight: bold; background: #f9f9f9; }

        .footer-note { margin-top: 40px; font-size: 14px; line-height: 1.6; }

        /* Remove italic from address tag */
        address {
            font-style: normal !important;
            font-size: 13px;
            line-height: 1.5;
            margin: 10px 0;
        }

        .clear {
            margin: 10px 0;
        }

        /* Web view specific alignment */
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
        }
    </style>
@endsection
@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Import Bill Report</h5>

                    <form id="filterForm" class="row g-3 BankBook">
                        <!-- Company Filter -->
                        <div class="col-md-2 form-group">
                            <label>Company</label>
                            <select name="company" id="company" class="form-control form-control-sm">
                                @foreach($companyNames as $key => $name)
                                    <option value="{{ $key }}" {{ $company == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

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
                            <button type="button" id="printBtn" class="btn btn-info btn-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing" id="reportTable">
            @include('partials.importBillReportTable', [
                'importBills' => $importBills,
                'companyAddresses' => $companyAddresses
            ])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Store original values for reset (last bill's values)
            const originalCompany = "{{ $lastBill ? $lastBill->company_name : 'all' }}";
            const originalLcNo = "{{ $lastBill ? $lastBill->lc_no : 'all' }}";
            const originalBeNo = "{{ $lastBill ? $lastBill->be_no : 'all' }}";
            const originalBillNo = "{{ $lastBill ? $lastBill->bill_no : 'all' }}";
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
                $('#company').val(originalCompany);
                $('#lcNo').val(originalLcNo);
                $('#beNo').val(originalBeNo);
                $('#billNo').val(originalBillNo);
                $('#billDate').val(originalBillDate);

                populateDropdown('#lcNo', allLcNos, originalLcNo);
                populateDropdown('#beNo', allBeNos, originalBeNo);
                populateDropdown('#billNo', allBillNos, originalBillNo);

                loadReportData({
                    company: originalCompany,
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

            // When any dropdown changes, update dependent dropdowns
            $('#company, #lcNo, #beNo, #billNo').on('change', function(){
                updateDependentOptions();
            });

            // Function to update dependent dropdowns
            function updateDependentOptions() {
                const company = $('#company').val();
                const lcNo = $('#lcNo').val();
                const beNo = $('#beNo').val();
                const billNo = $('#billNo').val();

                $.ajax({
                    url: "{{ route('importBill.dependent') }}",
                    type: "GET",
                    data: {
                        company: company,
                        lcNo: lcNo,
                        be_no: beNo,
                        bill_no: billNo
                    },
                    success: function(res){
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
                        $('#reportTable').html(res);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#reportTable').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
                    }
                });
            }

            // Print functionality
            function printReport() {
                // Get the report content
                const reportContent = document.getElementById('reportTable').innerHTML;

                // Remove the alert message if no bills found
                let fixedContent = reportContent.replace(
                    /<div class="alert alert-info text-center">[\s\S]*?<\/div>/g,
                    ''
                );

                // Create print-friendly HTML
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Import Bill Report</title>
                        <meta charset="UTF-8">
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 0;
                                padding: 15px;
                                color: #000;
                                font-size: 12px;
                            }

                            .company-header {
                                text-align: center;
                                margin-bottom: 10px;
                            }
                            .company-header h1 {
                                margin: 0;
                                font-size: 20px;
                                font-weight: bold;
                            }
                            .company-header p {
                                margin: 2px 0;
                                font-size: 12px;
                                color:#333;
                            }

                            .invoice-info {
                                display: flex;
                                justify-content: space-between;
                                margin-top: 8px;
                                font-size: 12px;
                                margin-bottom: 8px;
                            }
                            .invoice-info div {
                                width: 48%;
                            }

                            h3 {
                                margin-top: 15px;
                                font-size: 13px;
                                text-transform: uppercase;
                                margin-bottom: 10px;
                            }

                            /* Remove italic from address tag */
                            address {
                                font-style: normal !important;
                                font-size: 13px;
                                line-height: 1.5;
                                margin: 10px 0;
                            }

                            .clear {
                                margin: 10px 0;
                            }

                            .info-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 8px 0 15px;
                                font-size: 11px;
                            }
                            .info-table td {
                                border: 1px solid #222;
                                padding: 5px;
                                vertical-align: top;
                            }
                            .info-key {
                                font-weight: bold;
                                width: 10%;
                            }
                            .info-value {
                                width: 40%;
                            }

                            .invoice-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 15px 0;
                                font-size: 11px;
                            }
                            .invoice-table th,
                            .invoice-table td {
                                border: 1px solid #000;
                                padding: 6px;
                            }
                            .invoice-table th {
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

                            .footer-note {
                                margin-top: 30px;
                                font-size: 12px;
                                line-height: 1.5;
                            }

                            .print-footer {
                                margin-top: 20px;
                                padding-top: 8px;
                                border-top: 1px solid #333;
                                font-size: 10px;
                                color: #666;
                                text-align: center;
                            }

                            /* Page break */
                            .page-break {
                                page-break-after: always;
                            }

                            /* Hide page break indicators in print */
                            @media print {
                                body {
                                    margin: 0;
                                    padding: 10px;
                                }

                                hr {
                                    display: none !important;
                                }

                                .company-header h1 {
                                    font-size: 18px;
                                }

                                .invoice-table {
                                    page-break-inside: avoid;
                                }

                                th {
                                    background-color: #f0f0f0 !important;
                                }

                                /* Ensure address is not italic in print */
                                address {
                                    font-style: normal !important;
                                    font-size: 12px;
                                }

                                .clear {
                                    margin: 8px 0;
                                }

                                .print-footer {
                                    position: fixed;
                                    bottom: 0;
                                    width: 100%;
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
