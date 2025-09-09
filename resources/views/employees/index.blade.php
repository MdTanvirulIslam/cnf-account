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
                    <h5 class="card-title mb-3">Employee (Create / Update)</h5>

                    <form id="employeeForm" class="row g-3 Employee">
                        @csrf
                        <input type="hidden" id="employee_id" name="id">

                        <div class="col-md-12 form-group">
                            <label for="name">Name</label>
                            <input class="form-control form-control-sm" type="text" name="name" id="name" placeholder="Full Name">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="mobile_number">Mobile Number</label>
                            <input class="form-control form-control-sm" type="text" name="mobile_number" id="mobile_number" placeholder="01XXXXXXXXX">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="address">Address</label>
                            <input class="form-control form-control-sm" type="text" name="address" id="address" placeholder="Employee Address">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="department">Department</label>
                            <select name="department" id="department" class="form-control form-control-sm">
                                <option value="" selected>Select Department</option>
                                <option value="Import">Import</option>
                                <option value="Export">Export</option>
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="note">Note</label>
                            <input class="form-control form-control-sm" type="text" name="note" id="note" placeholder="Additional info...">
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
                <table id="employeeTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th class="checkbox-column text-center">SL</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Mobile</th>
                        <th class="text-center">Address</th>
                        <th class="text-center">Department</th>
                        <th>Note</th>
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

            const employeeTable = $('#employeeTable').DataTable({
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
                ajax: "{{ route('employees.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
                    { data: 'name', name: 'name', className:'text-center' },
                    { data: 'mobile_number', name: 'mobile_number', className:'text-center' },
                    { data: 'address', name: 'address', className:'text-center' },
                    { data: 'department', name: 'department', orderable:false, searchable:false, className:'text-center' },
                    { data: 'note', name: 'note' },
                    { data: 'action', name: 'action', orderable:false, searchable:false, className:'text-center' }
                ]
            });

            function resetFormToCreate() {
                $('#employeeForm')[0].reset();
                $('#employee_id').val('');
                editId = null;
                $('#formSubmitBtn').text('Create');
                $('#formCancelBtn').addClass('d-none');
            }

            $('#employeeForm').on('submit', function (e) {
                e.preventDefault();

                const name   = $('#name').val().trim();
                const mobile = $('#mobile_number').val().trim();
                const address = $('#address').val().trim();
                const department = $('#department').val();

                //  Frontend validation rules
                const mobileRegex = /^(013|016|017|018|019)[0-9]{8}$/;

                if (!name) {
                    Swal.fire('Validation Error', 'Name is required.', 'warning');
                    return;
                }

                if (!mobile) {
                    Swal.fire('Validation Error', 'Mobile number is required.', 'warning');
                    return;
                }

                if (!mobileRegex.test(mobile)) {
                    Swal.fire('Validation Error', 'Mobile number must be 11 digits and start with 013, 016, 017, 018, or 019.', 'warning');
                    return;
                }

                if (!department) {
                    Swal.fire('Validation Error', 'Please select a department.', 'warning');
                    return;
                }

                //  If all checks pass → submit via AJAX
                const formData = $(this).serialize();
                const isUpdate = !!$('#employee_id').val();
                const url      = isUpdate ? ("/employees/" + $('#employee_id').val()) : "{{ route('employees.store') }}";
                const payload  = isUpdate ? (formData + '&_method=PUT') : formData;

                $.post(url, payload)
                    .done(function (res) {
                        resetFormToCreate();
                        employeeTable.ajax.reload(null, false);
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
                $.get("/employees/" + id + "/edit")
                    .done(function (data) {
                        $('#employee_id').val(data.id);
                        $('#name').val(data.name);
                        $('#mobile_number').val(data.mobile_number);
                        $('#address').val(data.address);
                        $('#department').val(data.department);
                        $('#note').val(data.note || '');

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
                    text: "This employee will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/employees/" + id,
                            type: 'POST',
                            data: { _method: 'DELETE' },
                            success: function (res) {
                                employeeTable.ajax.reload(null, false);
                                Swal.fire("Deleted!", res.message, "success");
                                if ($('#employee_id').val() == id) resetFormToCreate();
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
