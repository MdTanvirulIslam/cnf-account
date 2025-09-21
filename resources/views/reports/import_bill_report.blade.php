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

                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm me-1">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" id="resetBtn" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i> Reset
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
    <script>
        $(document).ready(function() {
            // Store original values for reset (last bill's values)
            const originalLcNo = "{{ $lastBill->lc_no ?? 'all' }}";
            const originalBeNo = "{{ $lastBill->be_no ?? 'all' }}";
            const originalBillNo = "{{ $lastBill->bill_no ?? 'all' }}";
            const originalBillDate = "{{ $lastBill->bill_date ? \Carbon\Carbon::parse($lastBill->bill_date)->format('Y-m-d') : '' }}";

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
                        $('#reportTable').html('<div class="text-center p-3">Loading...</div>');
                    },
                    success: function(res){
                        $('#reportTable').html(res.html);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#reportTable').html('<div class="text-danger p-3">Failed to load data. Please try again.</div>');
                    }
                });
            }
        });
    </script>
@endsection
