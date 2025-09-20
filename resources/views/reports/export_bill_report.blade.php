@extends('layouts.layout')

@section('content')
    <div class="container">
        <h4>Export Bill Report</h4>

        <form id="filterForm" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label>Buyer</label>
                    <select name="buyer" id="buyer" class="form-control form-control-sm">
                        <option value="all">All Buyers</option>
                        @foreach($buyers as $id => $name)
                            <option value="{{ $id }}" {{ $buyerId==$id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>B/E No</label>
                    <select name="be_no" id="be_no" class="form-control form-control-sm">
                        <option value="all">All</option>
                        @foreach($beNos as $be)
                            <option value="{{ $be }}" {{ $beNo==$be ? 'selected' : '' }}>{{ $be }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Bill No</label>
                    <select name="bill_no" id="bill_no" class="form-control form-control-sm">
                        <option value="all">All</option>
                        @foreach($billNos as $bill)
                            <option value="{{ $bill }}" {{ $billNo==$bill ? 'selected' : '' }}>{{ $bill }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Date</label>
                    <input type="date" name="bill_date" value="{{ $billDate }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </form>

        <div id="reportTable">
            @include('partials.exportBillReportTable')
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // On Buyer change -> reload table + BE/Bill dropdowns
            $('#buyer').on('change', function () {
                let buyerId = $(this).val();

                // Reload table
                $.ajax({
                    url: '/export/bill/report',
                    type: 'GET',
                    data: { buyer: buyerId },
                    success: function (response) {
                        $('#reportTable').html(response);
                    }
                });

                // Reload dependent dropdowns
                $.ajax({
                    url: '/export/bill/report',
                    type: 'GET',
                    data: { buyer: buyerId, ajaxDropdown: 1 },
                    success: function (data) {
                        $('#be_no').empty().append('<option value="all">All</option>');
                        $.each(data.beNos, function (i, v) {
                            $('#be_no').append('<option value="' + v + '">' + v + '</option>');
                        });

                        $('#bill_no').empty().append('<option value="all">All</option>');
                        $.each(data.billNos, function (i, v) {
                            $('#bill_no').append('<option value="' + v + '">' + v + '</option>');
                        });
                    }
                });
            });

            // Normal filter submit (all dropdowns + date)
            $('#filterForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: '/export/bill/report',
                    type: 'GET',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#reportTable').html(response);
                    }
                });
            });
        });

    </script>
@endsection
