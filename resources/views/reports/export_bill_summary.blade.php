@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Export Bill Summary</h5>
                    <div class="col-md-5 form-group">
                        <input type="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing dc-report-table" id="reportTable">
            @include('partials.exportBillSummaryTable', ['bills' => $bills, 'month' => $month])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $('#month').on('change', function(){
                var month = $(this).val();
                $.ajax({
                    url: "{{ route('export.bill.summary.report') }}",
                    type: "GET",
                    data: { month: month },
                    success: function(response){
                        $('#reportTable').html(response);
                    }
                });
            });
        });
    </script>
@endsection
