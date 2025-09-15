@extends("layouts.layout")

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/table/datatable/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/custom_dt_custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endsection

@section('content')
    <div class="row layout-spacing">

        {{-- Left: Form --}}
        <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Account (Create / Update)</h5>

                    <form id="accountForm" class="row g-3">
                        @csrf
                        <input type="hidden" id="account_id" name="id">

                        <div class="col-md-12">
                            <label>Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-12">
                            <label>Opening Balance</label>
                            <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="form-control form-control-sm" required>
                        </div>

                        <div class="d-flex gap-2 mt-2">
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
                <table id="accountTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th class="text-center">SL</th>
                        <th>Name</th>
                        <th>Opening Balance</th>
                        <th>Balance</th>
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
    <script src="{{ asset('assets/src/plugins/src/table/datatable/datatables.js') }}"></script>
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>

    <script>
        $(function(){
            let editId = null;

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // DataTable
            const accountTable = $('#accountTable').DataTable({
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
                ajax: "{{ route('accounts.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'opening_balance', name: 'opening_balance' },
                    { data: 'balance', name: 'balance' },
                    { data: 'action', name: 'action', orderable:false, searchable:false, className:'text-center' }
                ]
            });

            // Reset form
            function resetFormToCreate() {
                $('#accountForm')[0].reset();
                $('#account_id').val('');
                editId = null;
                $('#formSubmitBtn').text('Create');
                $('#formCancelBtn').addClass('d-none');
            }

            // Submit
            $('#accountForm').on('submit', function(e){
                e.preventDefault();

                const isUpdate = !!$('#account_id').val();
                const url      = isUpdate ? "/accounts/"+$('#account_id').val() : "{{ route('accounts.store') }}";
                const payload  = isUpdate ? $(this).serialize() + '&_method=PUT' : $(this).serialize();

                $.post(url, payload)
                    .done(function(res){
                        resetFormToCreate();
                        accountTable.ajax.reload(null, false);
                        Swal.fire('Success', res.message, 'success');
                    })
                    .fail(function(xhr){
                        let msg = 'Something went wrong.';
                        if(xhr.responseJSON && xhr.responseJSON.errors){
                            msg = Object.values(xhr.responseJSON.errors).map(e=>e[0]).join('\n');
                        }
                        Swal.fire('Error!', msg, 'error');
                    });
            });

            // Edit
            $(document).on('click', '.edit-btn', function(){
                const id = $(this).data('id');
                $.get("/accounts/"+id+"/edit").done(function(data){
                    $('#account_id').val(data.id);
                    $('#name').val(data.name);
                    $('#opening_balance').val(data.opening_balance);

                    editId = id;
                    $('#formSubmitBtn').text('Update');
                    $('#formCancelBtn').removeClass('d-none');
                });
            });

            $('#formCancelBtn').on('click', function(){ resetFormToCreate(); });

            // Delete
            $(document).on('click', '.delete-btn', function(){
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This record will be deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result)=>{
                    if(result.isConfirmed){
                        $.ajax({
                            url: "/accounts/"+id,
                            type: 'POST',
                            data: {_method:'DELETE'},
                            success: function(res){
                                accountTable.ajax.reload(null,false);
                                Swal.fire('Deleted!', res.message, 'success');
                                if($('#account_id').val() == id) resetFormToCreate();
                            },
                            error: function(){ Swal.fire('Error!', 'Something went wrong.', 'error'); }
                        });
                    }
                });
            });

        });
    </script>
@endsection
