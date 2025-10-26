@extends('layouts.layout')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/table/datatable/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/custom_dt_custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">

@endsection

@section('content')
    <div class="row layout-spacing ">
        <div class="row mt-2 w-100">
            <div class="col-md-6"><h5 class="card-title">View Export Bills</h5></div>
            <div class="col-md-6 d-flex justify-content-end">
                <a href="{{ route('export-bills.create') }}" class="btn btn-info btn-rounded mb-2 me-4">New Export Bill</a>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing">
            <div class="widget-content widget-content-area br-8">
                <table id="exportBillTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th class="text-center">SL</th>
                        <th>Buyer</th>
                        <th>Invoice No</th>
                        <th>Invoice Date</th>
                        <th>Bill No</th>
                        <th>Bill Date</th>
                        <th>USD</th>
                        <th>Total CTN</th>
                        <th>BE No</th>
                        <th>BE Date</th>
                        <th>Qty PCS</th>
                        <th>Total Amount</th>
                        <th>VAT</th>
                        <th>Note</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th colspan="11" style="text-align:right">Total Amount:</th>
                        <th></th> <!-- Amount Total -->
                        <th></th> <!-- Bank Vat Total -->
                        <th></th> <!-- Action column empty -->
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/table/datatable/datatables.js') }}"></script>
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>

    <script>
        $(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            const table = $('#exportBillTable').DataTable({
                "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "oLanguage": {
                    "oPaginate": { "sPrevious": "Prev", "sNext": "Next" },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearchPlaceholder": "Search..."
                },
                "lengthMenu": [5, 10, 20, 50, 100],
                "pageLength": 10,
                processing: true,
                serverSide: true,
                ajax: "{{ route('export-bills.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'buyer_name', name: 'buyer_name' },
                    { data: 'invoice_no', name: 'invoice_no' },
                    { data: 'invoice_date', name: 'invoice_date' },
                    { data: 'bill_no', name: 'bill_no' },
                    { data: 'bill_date', name: 'bill_date' },
                    { data: 'usd', name: 'usd' },
                    { data: 'total_qty', name: 'total_qty' },
                    { data: 'be_no', name: 'be_no' },
                    { data: 'be_date', name: 'be_date' },
                    { data: 'qty_pcs', name: 'qty_pcs' },
                    { data: 'amount', name: 'amount', className: "text-right" },
                    { data: 'bank_vat_amount', name: 'bank_vat_amount', className: "text-right" },
                    { data: 'note', name: 'note' },
                    { data: 'action', name: 'action', orderable:false, searchable:false, className:'text-center' }
                ],
                 // you can adjust the default order if needed
                drawCallback: function () {
                    // re-init tooltips if needed
                    if (window.bootstrap) {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            if (!el._tooltip) el._tooltip = new bootstrap.Tooltip(el);
                        });
                    }
                },
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();

                    // Helper to parse numbers
                    var intVal = function (i) {
                        return typeof i === 'string'
                            ? i.replace(/[\$,]/g, '') * 1
                            : typeof i === 'number'
                                ? i
                                : 0;
                    };

                    // ====== Amount Total (column index 13) ======
                    var totalAmount = api.column(11, { page: 'current' }).data()
                        .reduce((a, b) => intVal(a) + intVal(b), 0);

                    // ====== Bank VAT Total (column index 14) ======
                    var totalBankVat = api.column(12, { page: 'current' }).data()
                        .reduce((a, b) => intVal(a) + intVal(b), 0);

                    // Update footer cells
                    $(api.column(11).footer()).html(
                        totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    );
                    $(api.column(12).footer()).html(
                        totalBankVat.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    );
                }
            });


            // Delete
            $(document).on('click', '.delete-btn', function () {
                const route = $(this).data('route');
                Swal.fire({
                    title: "Are you sure?",
                    text: "This bill will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: route,
                            type: 'DELETE',
                            success: function (res) {
                                table.ajax.reload(null, false);
                                Swal.fire("Deleted!", res.message || "Deleted successfully.", "success");
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
