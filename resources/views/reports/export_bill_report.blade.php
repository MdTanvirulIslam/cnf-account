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
            const originalBillDate = "{{ $lastBill->bill_date ? \Carbon\Carbon::parse($lastBill->bill_date)->format('Y-m-d') : '' }}";

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
                // Reset form values to last bill's values
                $('#buyer').val(originalBuyerId);
                $('#beNo').val(originalBeNo);
                $('#billNo').val(originalBillNo);
                $('#billDate').val(originalBillDate);

                // Reset dropdown options to show ALL options
                populateDropdown('#beNo', allBeNos, originalBeNo);
                populateDropdown('#billNo', allBillNos, originalBillNo);

                // Reload report with last bill's values
                loadReportData({
                    buyer: originalBuyerId,
                    be_no: originalBeNo,
                    bill_no: originalBillNo,
                    bill_date: originalBillDate
                });
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
                        // Only update dropdowns that are set to "all"
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
                    url: "{{ route('export.bill.report') }}",
                    type: "GET",
                    data: formData,
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
        });
    </script>
@endsection
