@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
        <h4 class="mb-3">Bank Book Report</h4>

        <!-- FILTER FORM: keep your visual classes if you already have different design -->
        <form id="filterForm" class="mb-4 row g-3 BankBook">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="bank" id="bank" class="form-control form-control-sm">
                        @foreach($banks as $b)
                            <option value="{{ $b }}" {{ (isset($bank) && $bank === $b) ? 'selected' : '' }}>
                                {{ $b }}
                            </option>
                        @endforeach
                        <option value="all" {{ (isset($bank) && strtolower($bank) === 'all') ? 'selected' : '' }}>All Banks</option>
                    </select>
                </div>

                <div class="col-md-3">

                    <input type="month" id="month" name="month" class="form-control form-control-sm"
                           value="{{ $month ?? \Carbon\Carbon::now()->format('Y-m') }}">
                </div>

                <div class="col-md-3">

                    <select name="type" id="type" class="form-control form-control-sm">
                        <option value="all" {{ (isset($type) && strtolower($type) === 'all') ? 'selected' : '' }}>All</option>
                        <option value="Receive" {{ (isset($type) && $type === 'Receive') ? 'selected' : '' }}>Receive</option>
                        <option value="Withdraw" {{ (isset($type) && $type === 'Withdraw') ? 'selected' : '' }}>Withdraw</option>
                        <option value="Pay Order" {{ (isset($type) && $type === 'Pay Order') ? 'selected' : '' }}>Pay Order</option>
                        <option value="Bank Transfer" {{ (isset($type) && $type === 'Bank Transfer') ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <button type="button" id="reloadBtn" class="btn btn-primary">Reload</button>
                </div>
            </div>
        </form>

        <!-- REPORT TABLE (this is replaced dynamically by AJAX) -->
        <div id="reportTable">
            @include('partials.bankbookReportTable', ['data' => $data])
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // helper to load
            function loadReport(params) {
                $('#reportTable').html('<div class="py-4 text-center">Loading...</div>');
                $.ajax({
                    url: "{{ route('bankbook.report') }}",
                    type: 'GET',
                    data: params,
                    success: function (res) {
                        $('#reportTable').html(res.html);
                    },
                    error: function (xhr, status, err) {
                        $('#reportTable').html('<div class="text-danger p-3">Could not load data...</div>');
                        console.error(xhr, status, err);
                    }
                });
            }

            // Trigger AJAX when any filter changes
            $('#bank, #month, #type').on('change', function () {
                loadReport({
                    bank: $('#bank').val(),
                    month: $('#month').val(),
                    type: $('#type').val()
                });
            });

            // optional reload button
            $('#reloadBtn').on('click', function () {
                loadReport({
                    bank: $('#bank').val(),
                    month: $('#month').val(),
                    type: $('#type').val()
                });
            });

        });
    </script>
@endsection
