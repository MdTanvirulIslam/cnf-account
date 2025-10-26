@extends("layouts.layout")

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/dt-global_style.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/custom_dt_custom.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
@endsection

@section('content')


    <div class="row layout-spacing ">

        {{-- Left: Form --}}
        <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">BankBook (Create / Update)</h5>

                    <form id="bankbookForm" class="row g-3 BankBook">
                        @csrf
                        <input type="hidden" id="bankbook_id" name="id"> {{-- set when editing --}}

                        <div class="col-md-12 form-group">
                            <label>To Account *</label>
                            <select name="account_id" id="account_id" class="form-control form-control-sm">
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->name }} (Balance: {{ number_format($account->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="created_at">Creation Date</label>
                            <input type="date"
                                   name="created_at"
                                   id="created_at"
                                   class="form-control"
                                   value="{{ old('created_at') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control form-control-sm">
                                <option value="" selected>Select Type</option>
                                <option value="Receive">Receive</option>
                                {{--<option value="Withdraw">Withdraw</option>--}}
                                <option value="Pay Order">Pay Order</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div id="from_account_wrapper" class="col-md-12 form-group d-none">
                            <label>From Account *</label>
                            <select name="from_account_id" id="from_account_id" class="form-control form-control-sm">
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->name }} (Balance: {{ number_format($account->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>



                        <div class="col-md-12 form-group">
                            <label for="amount">Money</label>
                            <input class="form-control form-control-sm" type="text" name="amount" id="amount" placeholder="1000">
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="note">Note</label>
                            <input class="form-control form-control-sm" type="text" name="note" id="note" placeholder="More information for the future..">
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
                <table id="bankbookTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th class="checkbox-column text-center">SL</th>
                        <th class="text-center">Bank Name</th>
                        <th class="text-center">Type</th>
                        <th>Amount</th>
                        <th>Date</th>
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
    {{-- Load vendor scripts FIRST --}}

    <script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
    <script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>

    <script>
        $(function () {
            let editId = null;

            // CSRF for all requests
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Toggle From Account visibility
            function toggleFromAccount(type) {
                if (type === 'Bank Transfer') {
                    $('#from_account_wrapper').removeClass('d-none');
                } else {
                    $('#from_account_wrapper').addClass('d-none');
                    $('#from_account_id').val('');
                }
            }

            $('#type').on('change', function () {
                toggleFromAccount($(this).val());
            });

            // DataTable init
            const bankbookTable = $('#bankbookTable').DataTable({
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
                processing: true,
                serverSide: true,
                ajax: "{{ route('bankbooks.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
                    { data: 'bank_name',   name: 'bank_name', className:'text-center' }, // comes from account relation
                    { data: 'type',        name: 'type',      orderable:false, searchable:false, className:'text-center' },
                    { data: 'amount',      name: 'amount' },
                    { data: 'date',      name: 'date' },
                    { data: 'note',        name: 'note' },
                    { data: 'action',      name: 'action', orderable:false, searchable:false, className:'text-center' }
                ],
                drawCallback: function () {
                    // Re-init Bootstrap tooltips after each draw
                    if (window.bootstrap) {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            // Prevent duplicate instances
                            if (!el._tooltip) {
                                el._tooltip = new bootstrap.Tooltip(el);
                            }
                        });
                    }
                }
            });

            // Helper: reset form to Create mode
            function resetFormToCreate() {
                $('#bankbookForm')[0].reset();
                $('#bankbook_id').val('');
                editId = null;
                $('#formSubmitBtn').text('Create');
                $('#formCancelBtn').addClass('d-none');
                toggleFromAccount('');
            }

            // Submit (Create / Update)
            $('#bankbookForm').on('submit', function (e) {
                e.preventDefault();

                // normalize amount
                const amountVal = ($('#amount').val() || '').toString().replace(/[^0-9.\-]/g, '');
                $('#amount').val(amountVal);

                const formData = $(this).serialize();

                const isUpdate = !!$('#bankbook_id').val();
                const url      = isUpdate ? ("/bankbooks/" + $('#bankbook_id').val()) : "{{ route('bankbooks.store') }}";
                const payload  = isUpdate ? (formData + '&_method=PUT') : formData;

                $.post(url, payload)
                    .done(function (res) {
                        resetFormToCreate();
                        bankbookTable.ajax.reload(null, false);
                        Swal.fire({
                            position: 'bottom-end',
                            icon: 'success',
                            title: res.message || (isUpdate ? 'BankBook updated!' : 'BankBook created!'),
                            showConfirmButton: false,
                            timer: 1500
                        });
                    })
                    .fail(function (xhr) {
                        let msg = 'Something went wrong.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('\n');
                        }
                        Swal.fire('Error!', msg, 'error');
                    });
            });


            // Edit click → fetch row by id and fill the form
            $(document).on('click', '.edit-btn', function () {
                const id = $(this).data('id');
                $.get("/bankbooks/" + id + "/edit")
                    .done(function (data) {
                        $('#bankbook_id').val(data.id);
                        $('#account_id').val(data.account_id);
                        $('#type').val(data.type);
                        $('#amount').val(data.amount);

                        // Fix: Format created_at date for HTML input
                        if (data.created_at) {
                            const createdDate = new Date(data.created_at);
                            const formattedDate = createdDate.toISOString().split('T')[0];
                            $('#created_at').val(formattedDate);
                        } else {
                            $('#created_at').val('');
                        }

                        $('#note').val(data.note || '');

                        // Set from_account if transfer
                        if (data.type === 'Bank Transfer') {
                            $('#from_account_id').val(data.from_account_id || '');
                        } else {
                            $('#from_account_id').val('');
                        }

                        toggleFromAccount(data.type);

                        editId = id;
                        $('#formSubmitBtn').text('Update');
                        $('#formCancelBtn').removeClass('d-none');

                        // scroll to form
                        $('html, body').animate({ scrollTop: $('.BankBook').offset().top - 80 }, 300);
                    })
                    .fail(function () {
                        Swal.fire('Error!', 'Failed to load record for editing.', 'error');
                    });
            });

            // Cancel editing
            $('#formCancelBtn').on('click', function () {
                resetFormToCreate();
            });

            // Delete with SweetAlert (unchanged)
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');

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
                            url: "/bankbooks/" + id,
                            type: 'POST',
                            data: { _method: 'DELETE' },
                            success: function (res) {
                                bankbookTable.ajax.reload(null, false);
                                Swal.fire("Deleted!", res.message || "Deleted successfully.", "success");
                                if ($('#bankbook_id').val() == id) resetFormToCreate();
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
