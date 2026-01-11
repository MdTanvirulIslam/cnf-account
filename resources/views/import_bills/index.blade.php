@extends('layouts.layout')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/table/datatable/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/custom_dt_custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">
    <style>
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        .filter-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .filter-btn {
            height: 38px;
        }
        .reset-btn {
            height: 38px;
            background: #6c757d;
            border-color: #6c757d;
        }
        .reset-btn:hover {
            background: #5a6268;
            border-color: #545b62;
        }
        .filter-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        .filter-button-group {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="row mt-2 w-100">
            <div class="col-md-6"><h5 class="card-title">View Import Bills</h5></div>
            <div class="col-md-6 d-flex justify-content-end">
                <a href="{{ route('import-bills.create') }}" class="btn btn-info btn-rounded mb-2 me-4">
                    <i class="fas fa-plus me-2"></i>New Import Bill
                </a>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="col-xl-12 mb-4">
            <div class="filter-section">
                <h6 class="filter-title"><i class="fas fa-filter me-2"></i>Filter Options</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="filter-label">Company Name</label>
                        <select id="companyFilter" class="form-control form-control-sm">
                            <option value="">All Companies</option>
                            <option value="MULTI FABS LTD">MULTI FABS LTD</option>
                            <option value="EMS APPARELS LTD">EMS APPARELS LTD</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="filter-label">Month</label>
                        <select id="monthFilter" class="form-control form-control-sm">
                            <option value="">All Months</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="filter-label">Year</label>
                        <select id="yearFilter" class="form-control form-control-sm">
                            <option value="">All Years</option>
                            @php
                                $currentYear = date('Y');
                                for($year = $currentYear; $year >= 2020; $year--) {
                                    echo "<option value='{$year}'>{$year}</option>";
                                }
                            @endphp
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="filter-button-group">
                            <button id="applyFilter" class="btn btn-primary btn-sm filter-btn">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <button id="resetFilter" class="btn btn-secondary btn-sm reset-btn">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 layout-top-spacing">
            <div class="widget-content widget-content-area br-8">
                <table id="importBillTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Company</th>
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
                        <th class="text-center">PORT BILL</th>
                        <th class="text-center">AIT AMOUNT</th>
                        <th class="text-center">DOC FEE</th>
                        <th class="text-center">SCAN FEE</th>
                        <th class="text-center">ITC</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th colspan="13" style="text-align:right">Total Amount:</th>
                        <th id="total_amount"></th>
                        <th id="total_port_bill"></th>
                        <th id="total_ait"></th>
                        <th id="total_doc"></th>
                        <th id="total_scan"></th>
                        <th id="total_itc"></th>
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
                "language": {
                    "paginate": {
                        "previous": "<i class='fas fa-chevron-left'></i> Prev",
                        "next": "Next <i class='fas fa-chevron-right'></i>"
                    },
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "search": "Search:",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "emptyTable": "No data available in table"
                },
                "lengthMenu": [5, 10, 20, 50, 100],
                "pageLength": 10,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('import-bills.data') }}",
                    data: function (d) {
                        // Add filter parameters to the DataTables request
                        d.company_name = $('#companyFilter').val();
                        d.month = $('#monthFilter').val();
                        d.year = $('#yearFilter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'company_name', name: 'company_name' },
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
                    { data: 'port_bill_amount', name: 'port_bill_amount' },
                    { data: 'ait_amount', name: 'ait_amount' },
                    { data: 'doc_fee', name: 'doc_fee' },
                    { data: 'scan_fee', name: 'scan_fee' },
                    { data: 'itc', name: 'itc' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
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
                            ? i.replace(/[^\d.-]/g, '') * 1
                            : typeof i === 'number'
                                ? i
                                : 0;
                    };

                    // Compute totals for current page
                    var totalAmount = api.column(13, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalPortBill = api.column(14, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalAIT = api.column(15, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalDoc = api.column(16, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalScan = api.column(17, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    var totalItc = api.column(18, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);

                    // Update footer cells
                    $('#total_amount').html(totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_port_bill').html(totalPortBill.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_ait').html(totalAIT.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_doc').html(totalDoc.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_scan').html(totalScan.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    $('#total_itc').html(totalItc.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                }
            });

            // Apply Filter
            $('#applyFilter').click(function() {
                table.ajax.reload();
            });

            // Reset Filter
            $('#resetFilter').click(function() {
                $('#companyFilter').val('');
                $('#monthFilter').val('');
                $('#yearFilter').val('');
                table.ajax.reload();
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
