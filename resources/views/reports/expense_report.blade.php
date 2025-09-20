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
                <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm">
            </div>

            <!-- Category -->
            <div class="col-md-3">
                <select name="category" class="form-control form-control-sm">
                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories as $id => $cat)
                        <option value="{{ $id }}" {{ $category == $id ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sub-Category -->
            <div class="col-md-3">
                <select name="sub_category" class="form-control form-control-sm">
                    <option value="all" {{ $subCategory === 'all' ? 'selected' : '' }}>All Sub-Categories</option>
                    @foreach($subCategories as $id => $subCat)
                        <option value="{{ $id }}" {{ $subCategory == $id ? 'selected' : '' }}>{{ $subCat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit -->
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
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
            $('#filter-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('expense.report') }}",
                    type: "GET",
                    data: $(this).serialize(),
                    success: function (res) {
                        $('#report-table').html(res.html);
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        alert("Failed to load data.");
                    }
                });
            });
        });
    </script>
@endsection
