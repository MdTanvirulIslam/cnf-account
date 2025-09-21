@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Employee Cash Report</h5>

                    <!-- Filter Form -->
                    <form action="#" method="POST" class="row g-3 employeeCash">
                        @csrf
                        <div class="col-md-3 form-group">
                            <label for="department">Department</label>
                            <select name="department" id="department" class="form-control form-control-sm">
                                <option value="">All Departments</option>
                                <option value="Import" {{ request('department') == 'Import' ? 'selected' : '' }}>Import</option>
                                <option value="Export" {{ request('department') == 'Export' ? 'selected' : '' }}>Export</option>
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label for="paymentType">Payment Type</label>
                            <select name="paymentType" id="paymentType" class="form-control form-control-sm">
                                <option value="">All Types</option>
                                <option value="receive" {{ request('paymentType') == 'receive' ? 'selected' : '' }}>Receive</option>
                                <option value="return" {{ request('paymentType') == 'return' ? 'selected' : '' }}>Return</option>
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label for="month">Month</label>
                            <input type="month" id="month" name="month" class="form-control form-control-sm"
                                   value="{{ $selectedMonth ?? \Carbon\Carbon::now()->format('Y-m') }}">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <label for=""> &nbsp; </label>
                            {{--<button type="submit" class="btn btn-primary btn-sm">Filter</button>--}}
                            <button type="button" id="resetFilter" class="btn btn-secondary btn-sm ml-2">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="col-xl-12 layout-top-spacing dc-report-table" id="report-table">
            @include('partials.employeeCashReportTable', ['transactions' => $transactions, 'selectedMonth' => $selectedMonth ?? \Carbon\Carbon::now()])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // AJAX form submission
            $('.employeeCash').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('employee-cash-report.filter') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#report-table').html(res.html);
                    },
                    error: function(err) {
                        alert('Something went wrong!');
                    }
                });
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#department').val('');
                $('#paymentType').val('');
                $('#month').val('{{ \Carbon\Carbon::now()->format('Y-m') }}');
                $('.employeeCash').submit();
            });

            // Trigger filter on change
            $('#department, #paymentType, #month').on('change', function() {
                $('.employeeCash').submit();
            });
        });
    </script>
@endsection

<style>
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
        color: #333;
    }

    .invoice-info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-size: 14px;
    }
    .invoice-info div {
        width: 48%;
    }

    h3 {
        margin-top: 20px;
        font-size: 15px;
        text-transform: uppercase;
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

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .invoice-table th,
    .invoice-table td {
        border: 1px solid #000;
        padding: 6px 8px;
    }
    .invoice-table th {
        background-color: #f4f4f4;
        text-align: left;
    }
    .right {
        text-align: right;
    }
    .center {
        text-align: center;
    }
    .total-row td {
        font-weight: bold;
        background: #f9f9f9;
    }

    .footer-note {
        margin-top: 20px;
        font-size: 13px;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .ml-2 {
        margin-left: 0.5rem;
    }
</style>
