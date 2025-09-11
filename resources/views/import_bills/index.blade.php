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
            <div class="col-md-6"><h5 class="card-title">View Import Bills</h5></div>
            <div class="col-md-6 d-flex justify-content-end">
                <a href="{{ route('import-bills.create') }}" class="btn btn-info btn-rounded mb-2 me-4">New Import Bill</a>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing">
            <div class="widget-content widget-content-area br-8">
                <table id="importBillTable" class="table style-3 dt-table-hover">
                    <thead>
                        <tr>
                            <th>#</th> {{-- DT_RowIndex --}}
                            <th class="text-center">L/C NO</th>
                            <th class="text-center">L/C DATE</th>
                            <th class="text-center">B/E NO</th>
                            <th class="text-center">B/E DATE</th>
                            <th class="text-center">BILL NO</th>
                            <th class="text-center">BILL DATE</th>
                            <th class="text-center">ITEM</th>
                            <th class="text-center">DOC QTY</th>
                            <th class="text-center">TTL WT.</th>
                            <th class="text-center">MONTH</th>
                            <th class="text-center">VALUE</th>
                            <th class="text-center">BILL AMOUNT</th>
                            <th class="text-center">DF VAT</th>
                            <th class="text-center">DOC FEE</th>
                            <th class="text-center">SCAN FEE</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th colspan="12" style="text-align:right">Total Amount:</th>
                        <th id="total_amount"></th>
                        <th id="total_ait"></th>
                        <th id="total_doc"></th>
                        <th id="total_scan"></th>
                        <th></th>
                    </tr>
                    </tfoot>
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
        $(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            const table = $('#importBillTable').DataTable({
                "dom":
                    "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l>" +
                    "<'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
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
                ajax: "{{ route('import-bills.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'lc_no', name: 'lc_no' },
                    { data: 'lc_date', name: 'lc_date' },
                    { data: 'be_no', name: 'be_no' },
                    { data: 'be_date', name: 'be_date' },
                    { data: 'bill_no', name: 'bill_no' },
                    { data: 'bill_date', name: 'bill_date' },
                    { data: 'item', name: 'item' },
                    { data: 'qty', name: 'qty' },
                    { data: 'weight', name: 'weight' },
                    { data: 'month_name', name: 'month_name' },
                    { data: 'value', name: 'value' },
                    { data: 'amount', name: 'amount' },
                    { data: 'ait_amount', name: 'ait_amount' },
                    { data: 'doc_fee', name: 'doc_fee' },
                    { data: 'scan_fee', name: 'scan_fee' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[15, 'desc']],
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

                    // Compute totals for current page
                    var totalAmount = api.column(12, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalAIT = api.column(13, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalDoc = api.column(14, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalScan = api.column(15, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);

                    // Update footer cells
                    $('#total_amount').html(totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_ait').html(totalAIT.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_doc').html(totalDoc.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_scan').html(totalScan.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                }
            });


            // Delete via SweetAlert + reload table
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
                            data: {_token: $('meta[name="csrf-token"]').attr('content')},
                            success: function (res) {
                                $('#importBillTable').DataTable().ajax.reload(null, false);
                                Swal.fire("Deleted!", res.message, "success");
                            },
                            error: function (xhr) {
                                Swal.fire("Error!", xhr.responseJSON?.message || "Something went wrong.", "error");
                            }
                        });
                    }
                });
            });

        });
    </script>
@endsection
