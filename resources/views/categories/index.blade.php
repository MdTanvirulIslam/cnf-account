@extends("layouts.layout")

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/dt-global_style.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/custom_dt_custom.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
@endsection

@section('content')
    <div class="row layout-spacing ">

        <!-- Form -->
        <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" id="formTitle">New Category Entry</h5>

                    <form id="categoryForm" method="POST" class="row g-3 ExpenseForm">
                        @csrf
                        <input type="hidden" id="category_id" name="id">

                        <div class="col-md-12 form-group">
                            <label for="category">Category Name</label>
                            <input class="form-control form-control-sm" type="text" name="category" id="category" placeholder="Enter Category Name">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="parent_id">Parent Category</label>
                            <select name="parent_id" id="parent_id" class="form-control form-control-sm">
                                <option value="">-- Root --</option>
                                @foreach($parents as $p)
                                    <option value="{{ $p->id }}">{{ $p->category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Create</button>
                            <button type="button" class="btn btn-secondary d-none" id="cancelBtn">Cancel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="widget-content widget-content-area br-8">
                <table id="categoryTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr class="text-center">
                        <th>SL</th>
                        <th>Category</th>
                        <th>Parent Category</th>
                        <th class="dt-no-sorting">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody> {{-- DataTables server-side --}}
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
            let editId = null;

            // CSRF
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // DataTable
            const categoryTable = $('#categoryTable').DataTable({
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
                ajax: "{{ route('categories.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
                    { data: 'category',   name: 'category', className:'text-center' },
                    { data: 'parent_name',name: 'parent_name', orderable:false, searchable:false, className:'text-center' },
                    { data: 'action',     name: 'action', orderable:false, searchable:false, className:'text-center' }
                ],
                drawCallback: function () {
                    // re-init bootstrap tooltips if you use them
                    if (window.bootstrap) {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            if (!el._tooltip) el._tooltip = new bootstrap.Tooltip(el);
                        });
                    }
                }
            });

            function resetForm() {
                $('#categoryForm')[0].reset();
                $('#category_id').val('');
                editId = null;
                $('#submitBtn').text('Create');
                $('#formTitle').text('New Category Entry');
                $('#cancelBtn').addClass('d-none');
            }

            // Create / Update submit
            $('#categoryForm').on('submit', function (e) {
                e.preventDefault();

                const id = $('#category_id').val();
                const isUpdate = !!id;
                const url = isUpdate ? `/categories/${id}` : `{{ route('categories.store') }}`;
                let data = $(this).serialize();
                if (isUpdate) data += '&_method=PUT'; // method spoofing for Laravel

                $.post(url, data)
                    .done(function (res) {
                        // If new category, ensure it appears in parent dropdown
                        if (res.data && !isUpdate) {
                            const exists = $(`#parent_id option[value="${res.data.id}"]`).length > 0;
                            if (!exists) {
                                $('#parent_id').append(
                                    `<option value="${res.data.id}">${res.data.category}</option>`
                                );
                            }
                        }

                        resetForm();
                        categoryTable.ajax.reload(null, false);

                        Swal.fire({
                            position: 'bottom-end',
                            icon: 'success',
                            title: res.message || (isUpdate ? 'Category updated!' : 'Category created!'),
                            showConfirmButton: false,
                            timer: 1500
                        });
                    })
                    .fail(function (xhr) {
                        let msg = 'Something went wrong.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('\n');
                        }
                        Swal.fire('Error!', msg, 'error');
                    });
            });

            // Edit
            $(document).on('click', '.edit-btn', function () {
                const id = $(this).data('id');

                $.get(`/categories/${id}/edit`)
                    .done(function (data) {
                        $('#category_id').val(data.id);
                        $('#category').val(data.category);
                        $('#parent_id').val(data.parent_id);

                        editId = id;
                        $('#submitBtn').text('Update');
                        $('#formTitle').text('Update Category');
                        $('#cancelBtn').removeClass('d-none');

                        $('html, body').animate({ scrollTop: $('.ExpenseForm').offset().top - 80 }, 300);
                    })
                    .fail(function () {
                        Swal.fire('Error!', 'Failed to load category.', 'error');
                    });
            });

            // Cancel
            $('#cancelBtn').on('click', function () {
                resetForm();
            });

            // Delete
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This category will be moved to trash.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(`/categories/${id}`, { _method: 'DELETE' })
                            .done(function (res) {
                                categoryTable.ajax.reload(null, false);
                                // If the deleted category was selected in the form as parent, clear it
                                if ($('#parent_id').val() == id) $('#parent_id').val('');
                                // If currently editing this category, reset form
                                if ($('#category_id').val() == id) resetForm();

                                Swal.fire("Deleted!", res.message || "Deleted successfully.", "success");
                            })
                            .fail(function () {
                                Swal.fire("Error!", "Delete failed.", "error");
                            });
                    }
                });
            });

        });
    </script>
@endsection
