@extends("layouts.layout")

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/dt-global_style.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/custom_dt_custom.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
    <link href="{{ asset("assets/src/assets/css/light/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="row layout-spacing">

        {{-- Left: Form --}}
        <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ $department }} Employee Transaction (Create / Update)</h5>

                    <form id="transactionForm" class="row g-3 Transaction">
                        @csrf
                        <input type="hidden" id="transaction_id" name="id">

                        <div class="col-md-12 form-group">
                            <label for="employee_id">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control form-control-sm">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="date">Date</label>
                            <input type="date" class="form-control form-control-sm" name="date" id="date" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="amount">Amount</label>
                            <input type="text" class="form-control form-control-sm" name="amount" id="amount" placeholder="1000">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control form-control-sm">
                                <option value="">Select Type</option>
                                <option value="receive">Receive</option>
                                <option value="return">Return</option>
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
                <table id="transactionTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th class="checkbox-column text-center">SL</th>
                        <th class="text-center">Employee</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Type</th>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function () {
            const baseRoute = "{{ strtolower($department) }}";
            let editId = null;

            $('#employee_id').select2({ width: '100%' });

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            const transactionTable = $('#transactionTable').DataTable({
                "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "oLanguage": {
                    "oPaginate": { "sPrevious": "Prev", "sNext": "Next" },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearchPlaceholder": "Search..."
                },
                "lengthMenu": [5, 10, 20, 50, 100],
                "pageLength": 10,
                processing: true,
                serverSide: true,
                ajax: "{{ route('transactions.' . strtolower($department)) }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
                    { data: 'employee_name', name: 'employee_name', className:'text-center' },
                    { data: 'date', name: 'date', className:'text-center' },
                    { data: 'amount', name: 'amount', className:'text-center' },
                    { data: 'type', name: 'type', orderable:false, searchable:false, className:'text-center' },
                    { data: 'note', name: 'note' },
                    { data: 'action', name: 'action', orderable:false, searchable:false, className:'text-center' }
                ]
            });

            function resetFormToCreate() {
                $('#transactionForm')[0].reset();
                $('#transaction_id').val(''); // <-- critical fix
                $('#employee_id').val('').trigger('change');
                editId = null;
                $('#formSubmitBtn').text('Create');
                $('#formCancelBtn').addClass('d-none');
            }

            $('#transactionForm').on('submit', function (e) {
                e.preventDefault();

                const employee_id = $('#employee_id').val();
                const date        = $('#date').val();
                const amount      = $('#amount').val();
                const type        = $('#type').val();

                if (!employee_id) { Swal.fire('Validation Error', 'Select employee.', 'warning'); return; }
                if (!date)        { Swal.fire('Validation Error', 'Select date.', 'warning'); return; }
                if (!amount || isNaN(amount) || Number(amount) <= 0) { Swal.fire('Validation Error', 'Amount must be > 0.', 'warning'); return; }
                if (!type)        { Swal.fire('Validation Error', 'Select type.', 'warning'); return; }

                const formData = $(this).serialize();
                const isUpdate = $('#transaction_id').val() && $('#transaction_id').val() !== '';
                const url      = isUpdate ? ("/transactions/" + baseRoute + "/" + $('#transaction_id').val()) : "/transactions/" + baseRoute;
                const payload  = isUpdate ? (formData + '&_method=PUT') : formData;

                $.post(url, payload)
                    .done(function (res) {
                        resetFormToCreate();
                        transactionTable.ajax.reload(null, false);
                        Swal.fire({ position: 'bottom-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1500 });
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
                const route = $(this).data('route');

                $.get(route)
                    .done(function (data) {
                        $('#transaction_id').val(data.id);
                        $('#employee_id').val(data.employee_id).trigger('change');
                        $('#date').val(data.date);
                        $('#amount').val(data.amount);
                        $('#type').val(data.type);
                        $('#note').val(data.note || '');

                        editId = data.id;
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
                const route = $(this).data('route');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This record will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: route,
                            type: 'POST',
                            data: { _method: 'DELETE' },
                            success: function (res) {
                                transactionTable.ajax.reload(null, false);
                                Swal.fire("Deleted!", res.message, "success");
                                if ($('#transaction_id').val() == id) resetFormToCreate();
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
