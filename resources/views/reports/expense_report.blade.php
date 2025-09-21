@extends('layouts.layout')

@section('content')
    <div class="row layout-spacing ">

        <!-- Content -->
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h3>Expense Report</h3>

                    <form id="filter-form" class="row g-3 mb-3">
                        <!-- Month -->
                        <div class="col-md-3">
                            <input type="month" name="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                        </div>

                        <!-- Category -->
                        <div class="col-md-3">
                            <select name="category" id="category" class="form-control form-control-sm">
                                <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
                                @foreach($categories as $id => $cat)
                                    <option value="{{ $id }}" {{ $category == $id ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sub-Category -->
                        <div class="col-md-3">
                            <select name="sub_category" id="sub_category" class="form-control form-control-sm">
                                <option value="all" {{ $subCategory === 'all' ? 'selected' : '' }}>All Sub-Categories</option>
                                @foreach($subCategories as $id => $subCat)
                                    <option value="{{ $id }}" {{ $subCategory == $id ? 'selected' : '' }}>{{ $subCat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" id="reset-btn" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>

                    <!-- Report Table -->
                    <div id="report-table">
                        @include('partials.expenseReportTable', [
                            'data' => $data,
                            'month' => $month,
                            'category' => $category,
                            'subCategory' => $subCategory,
                            'categories' => $categories,
                            'subCategories' => $subCategories,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Store original values for reset
            const originalMonth = $('#month').val();
            const originalCategory = $('#category').val();
            const originalSubCategory = $('#sub_category').val();

            // Filter form submission
            $('#filter-form').on('submit', function (e) {
                e.preventDefault();
                loadReportData($(this).serialize());
            });

            // Reset button click
            $('#reset-btn').on('click', function () {
                // Reset form values
                $('#month').val(originalMonth);
                $('#category').val('all');
                $('#sub_category').val('all');

                // Load report with reset values
                loadReportData({
                    month: originalMonth,
                    category: 'all',
                    sub_category: 'all'
                });
            });

            // Function to load report data
            function loadReportData(formData) {
                $.ajax({
                    url: "{{ route('expense.report') }}",
                    type: "GET",
                    data: formData,
                    beforeSend: function() {
                        $('#report-table').html('<div class="text-center p-3">Loading...</div>');
                    },
                    success: function (res) {
                        $('#report-table').html(res.html);
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        $('#report-table').html('<div class="text-danger p-3">Failed to load data. Please try again.</div>');
                    }
                });
            }
        });
    </script>
@endsection
