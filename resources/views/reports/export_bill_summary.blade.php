@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing">
        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Export Bill Summary</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 form-group">
                            <label for="month" class="form-label">Select Month</label>
                            <input type="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 form-group">
                            <button type="button" id="resetBtn" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
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
            // Store the original month value
            const originalMonth = "{{ $month }}";

            // Month change event
            $('#month').on('change', function(){
                loadReportData($(this).val());
            });

            // Reset button click event
            $('#resetBtn').on('click', function() {
                // Reset to original month
                $('#month').val(originalMonth);
                // Reload report with original month
                loadReportData(originalMonth);
            });

            // Function to load report data
            function loadReportData(month) {
                $.ajax({
                    url: "{{ route('export.bill.summary.report') }}",
                    type: "GET",
                    data: { month: month },
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
