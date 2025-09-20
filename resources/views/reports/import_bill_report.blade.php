@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Import Bill Report</h5>

                    <form id="filterForm" class="row g-3 BankBook">
                        <!-- L/C No -->
                        <div class="col-md-3 form-group">
                            <label>L/C No</label>
                            <select  name="lcNo" class="form-control form-control-sm">
                                <option value="all">All</option>
                                @foreach($lcNos as $lc)
                                    <option value="{{ $lc }}" {{ $lcNo==$lc?'selected':'' }}>{{ $lc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- B/E No -->
                        <div class="col-md-3 form-group">
                            <label>B/E No</label>
                            <select name="be_no" class="form-control form-control-sm">
                                <option value="all">All</option>
                                @foreach($beNos as $be)
                                    <option value="{{ $be }}" {{ $beNo==$be?'selected':'' }}>{{ $be }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bill No -->
                        <div class="col-md-3 form-group">
                            <label>Bill No</label>
                            <select name="bill_no" class="form-control form-control-sm">
                                <option value="all">All</option>
                                @foreach($billNos as $bill)
                                    <option value="{{ $bill }}" {{ $billNo==$bill?'selected':'' }}>{{ $bill }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bill Date -->
                        <div class="col-md-2 form-group">
                            <label>Date</label>
                            <input type="date" name="billDate" value="{{ $billDate }}" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-1 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
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
            // AJAX Filter
            $('#filterForm').on('submit', function(e){
                e.preventDefault();
                $.ajax({
                    url: "{{ route('import.bill.report') }}",
                    type: "GET",
                    data: $(this).serialize(),
                    success: function(res){
                        $('#reportTable').html(res.html);
                    }
                });
            });

            // Dependent Dropdowns
            $('select[name="lcNo"], select[name="be_no"], select[name="bill_no"]').on('change', function(){

                let lcNo = $('#lcNo').val();
                let beNo = $('#beNo').val();
                let billNo = $('#billNo').val();

                $.ajax({
                    url: "{{ route('importBill.dependent') }}",
                    type: "GET",
                    data: { lcNo: lcNo, be_no: beNo, bill_no: billNo },
                    success: function(res){
                        function fillOptions(selector, data, currentVal){
                            let sel = $(selector);
                            sel.empty().append('<option value="all">All</option>');
                            data.forEach(function(val){
                                sel.append('<option value="'+val+'" '+(val==currentVal?'selected':'')+'>'+val+'</option>');
                            });
                        }

                        fillOptions('#lcNo', res.lcNos, lcNo);
                        fillOptions('#beNo', res.beNos, beNo);
                        fillOptions('#billNo', res.billNos, billNo);
                    }
                });
            });

        });
    </script>


@endsection
