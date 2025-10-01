@extends("layouts.layout")

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/dt-global_style.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/custom_dt_custom.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
    <link href="{{ asset("assets/src/assets/css/light/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row layout-spacing">

        {{-- Left: Form --}}
        <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Buyer (Create / Update)</h5>

                    <form id="buyerForm" class="row g-3">
                        @csrf
                        <input type="hidden" id="buyer_id" name="id">

                        <div class="col-md-12 form-group">
                            <label for="name">Name *</label>
                            <input class="form-control form-control-sm" type="text" name="name" id="name" placeholder="Full Name" required>
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="email">Email </label>
                            <input class="form-control form-control-sm" type="email" name="email" id="email" placeholder="email@example.com">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="phone">Phone</label>
                            <input class="form-control form-control-sm" type="text" name="phone" id="phone" placeholder="Phone Number">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="company">Company</label>
                            <input class="form-control form-control-sm" type="text" name="company" id="company" placeholder="Company Name">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="address">Address</label>
                            <input class="form-control form-control-sm" type="text" name="address" id="address" placeholder="Full Address">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="formSubmitBtn">Create</button>
                            <button type="button" class="btn btn-secondary d-none" id="formCancelBtn">Cancel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Right: DataTable --}}
        <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="widget-content widget-content-area br-8">
                <table id="buyerTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th class="checkbox-column text-center">SL</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Phone</th>
                        <th class="text-center">Company</th>
                        <th>Address</th>
                        <th class="text-center dt-no-sorting">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
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

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            const buyerTable = $('#buyerTable').DataTable({
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
                ajax: "{{ route('buyers.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
                    { data: 'name', name: 'name', className:'text-center' },
                    { data: 'email', name: 'email', className:'text-center' },
                    { data: 'phone', name: 'phone', className:'text-center' },
                    { data: 'company', name: 'company', className:'text-center' },
                    { data: 'address', name: 'address' },
                    { data: 'action', name: 'action', orderable:false, searchable:false, className:'text-center' }
                ]
            });

            function resetFormToCreate() {
                $('#buyerForm')[0].reset();
                $('#buyer_id').val('');
                editId = null;
                $('#formSubmitBtn').text('Create');
                $('#formCancelBtn').addClass('d-none');
            }

            $('#buyerForm').on('submit', function (e) {
                e.preventDefault();

                const name  = $('#name').val().trim();
                const email = $('#email').val().trim();

                // Frontend validation
                if (!name) {
                    Swal.fire('Validation Error', 'Name is required.', 'warning');
                    return;
                }

                // If all checks pass → submit via AJAX
                const formData = $(this).serialize();
                const isUpdate = !!$('#buyer_id').val();
                const url      = isUpdate ? ("/buyers/" + $('#buyer_id').val()) : "{{ route('buyers.store') }}";
                const payload  = isUpdate ? (formData + '&_method=PUT') : formData;

                $.post(url, payload)
                    .done(function (res) {
                        resetFormToCreate();
                        buyerTable.ajax.reload(null, false);
                        Swal.fire({
                            position: 'bottom-end',
                            icon: 'success',
                            title: res.message,
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

            $(document).on('click', '.edit-btn', function () {
                const id = $(this).data('id');
                $.get("/buyers/" + id + "/edit")
                    .done(function (data) {
                        $('#buyer_id').val(data.id);
                        $('#name').val(data.name);
                        $('#email').val(data.email);
                        $('#phone').val(data.phone || '');
                        $('#company').val(data.company || '');
                        $('#address').val(data.address || '');

                        editId = id;
                        $('#formSubmitBtn').text('Update');
                        $('#formCancelBtn').removeClass('d-none');
                    })
                    .fail(function () {
                        Swal.fire('Error!', 'Failed to load record for editing.', 'error');
                    });
            });

            $('#formCancelBtn').on('click', function () {
                resetFormToCreate();
            });

            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This buyer will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/buyers/" + id,
                            type: 'POST',
                            data: { _method: 'DELETE' },
                            success: function (res) {
                                buyerTable.ajax.reload(null, false);
                                Swal.fire("Deleted!", res.message, "success");
                                if ($('#buyer_id').val() == id) resetFormToCreate();
                            },
                            error: function () {
                                Swal.fire("Error!", "Something went wrong.", "error");
                            }
                        });
                    }
                });
            });

        });
    </script>
@endsection
