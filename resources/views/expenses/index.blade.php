@extends("layouts.layout")

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/dt-global_style.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/custom_dt_custom.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Add / Edit Expense</h5>

                    <form id="expenseForm">
                        @csrf
                        <input type="hidden" id="expense_id" name="expense_id">

                        <div class="form-group mb-3">
                            <label>Category</label>
                            <select name="category_id" id="category_id" class="form-control form-control-sm">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Sub Category</label>
                            <select name="sub_category_id" id="sub_category_id" class="form-control form-control-sm">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Date</label>
                            <input type="date" class="form-control form-control-sm" name="date" id="date">
                        </div>

                        <div class="form-group mb-3">
                            <label>Amount</label>
                            <input type="number" class="form-control form-control-sm" name="amount" id="amount" placeholder="0.00">
                        </div>

                        <div class="form-group mb-3">
                            <label>Note</label>
                            <input type="text" class="form-control form-control-sm" name="note" id="note" placeholder="Note">
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="widget-content widget-content-area br-8">
                <table id="expenseTable" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Note</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
    <script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>

    <script>
        $(function () {
            let expenseTable = $('#expenseTable').DataTable({
                "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "oLanguage": {
                    "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    "sSearchPlaceholder": "Search...",
                    "sLengthMenu": "Results :  _MENU_",
                },
                "lengthMenu": [5, 10, 20, 50, 100],
                "pageLength": 10,
                processing: true,
                serverSide: true,
                ajax: "{{ route('expenses.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'category', name: 'category' },
                    { data: 'sub_category', name: 'sub_category' },
                    { data: 'date', name: 'date' },
                    { data: 'amount', name: 'amount' },
                    { data: 'note', name: 'note' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // ✅ Load Sub Categories dynamically
            $('#category_id').on('change', function () {
                let categoryId = $(this).val();
                $('#sub_category_id').empty().append('<option value="">Select Sub Category</option>');
                if (categoryId) {
                    $.get('/get-subcategories/' + categoryId, function (data) {
                        $.each(data, function (i, sub) {
                            $('#sub_category_id').append('<option value="'+sub.id+'">'+sub.category+'</option>');
                        });
                    });
                }
            });

            // ✅ Save & Update
            $('#expenseForm').submit(function (e) {
                e.preventDefault();

                let id = $('#expense_id').val();
                let method = id ? 'PUT' : 'POST';
                let url = id ? '/expenses/' + id : "{{ route('expenses.store') }}";

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function (res) {
                        Swal.fire('Success', res.message, 'success');
                        $('#expenseForm')[0].reset();
                        $('#expense_id').val('');
                        expenseTable.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    }
                });
            });

            // ✅ Edit
            $(document).on('click', '.edit-btn', function () {
                let id = $(this).data('id');
                $.get('/expenses/' + id + '/edit', function (res) {
                    $('#expense_id').val(res.id);
                    $('#category_id').val(res.category_id).trigger('change');

                    setTimeout(() => {
                        $('#sub_category_id').val(res.sub_category_id);
                    }, 500);

                    $('#date').val(res.date);
                    $('#amount').val(res.amount);
                    $('#note').val(res.note);
                });
            });

            // ✅ Delete
            $(document).on('click', '.delete-btn', function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This expense will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/expenses/' + id,
                            type: 'DELETE',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                Swal.fire('Deleted!', res.message, 'success');
                                expenseTable.ajax.reload(null, false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
