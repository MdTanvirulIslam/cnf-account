@extends('layouts.layout')

@section('content')
    <div class="container mt-4">

        <h3 class="mb-3">Buyer Management</h3>

        <button class="btn btn-primary mb-3" id="createNewBuyer">+ Add Buyer</button>

        <table class="table table-bordered" id="buyerTable">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Company</th>
                <th width="150px">Action</th>
            </tr>
            </thead>
        </table>
    </div>

    <!-- Buyer Modal -->
    <div class="modal fade" id="buyerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="buyerForm">
                @csrf
                <input type="hidden" name="buyer_id" id="buyer_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buyer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea name="address" id="address" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Company</label>
                            <input type="text" name="company" id="company" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {

            // CSRF Setup
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });

            // Init DataTable
            var table = $('#buyerTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('buyers.index') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'address', name: 'address'},
                    {data: 'company', name: 'company'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            // Show Modal (Create)
            $('#createNewBuyer').click(function () {
                $('#buyerForm')[0].reset();
                $('#buyer_id').val('');
                let buyerModal = new bootstrap.Modal(document.getElementById('buyerModal'));
                buyerModal.show();
            });

            // Edit Buyer
            $(document).on('click', '.editBuyer', function () {
                let id = $(this).data('id');
                $.get("{{ url('buyers') }}/" + id + "/edit", function (data) {
                    $('#buyer_id').val(data.id);
                    $('#name').val(data.name);
                    $('#email').val(data.email);
                    $('#phone').val(data.phone);
                    $('#address').val(data.address);
                    $('#company').val(data.company);

                    let buyerModal = new bootstrap.Modal(document.getElementById('buyerModal'));
                    buyerModal.show();
                });
            });

            // Save Buyer (Create/Update)
            $('#buyerForm').submit(function (e) {
                e.preventDefault();
                $('#saveBtn').prop('disabled', true);

                var formData = $(this).serialize();
                $.post("{{ route('buyers.store') }}", formData, function (res) {
                    $('#buyerModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Success', res.message, 'success');
                    $('#saveBtn').prop('disabled', false);
                }).fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON.message ?? 'Something went wrong!', 'error');
                    $('#saveBtn').prop('disabled', false);
                });
            });

            // Delete Buyer
            $(document).on('click', '.deleteBuyer', function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This record will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ url('buyers') }}/" + id,
                            success: function (res) {
                                table.ajax.reload(null, false);
                                Swal.fire('Deleted!', res.message, 'success');
                            },
                            error: function (xhr) {
                                Swal.fire('Error', xhr.responseJSON.message ?? 'Delete failed!', 'error');
                            }
                        });
                    }
                });
            });

        });
    </script>
@endsection
