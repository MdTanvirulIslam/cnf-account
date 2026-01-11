@extends('layouts.layout')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/table/datatable/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/css/light/table/datatable/custom_dt_custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
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
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #007bff !important;
            background: white !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e9ecef !important;
            border-color: #dee2e6;
            color: #0056b3 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #007bff !important;
            color: white !important;
            border-color: #007bff;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            background: #f8f9fa !important;
            font-weight: 600;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #6c757d !important;
            background: #f8f9fa !important;
            cursor: not-allowed;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background: #f8f9fa !important;
            color: #6c757d !important;
        }
        .dt-button {
            background: #007bff !important;
            color: white !important;
            border: 1px solid #007bff !important;
            border-radius: 4px !important;
            padding: 5px 15px !important;
            margin: 0 2px !important;
        }
        .dt-button:hover {
            background: #0056b3 !important;
            border-color: #0056b3 !important;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="row mt-2 w-100">
            <div class="col-md-6">
                <h5 class="card-title">View Export Bills</h5>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                <a href="{{ route('export-bills.create') }}" class="btn btn-info btn-rounded mb-2 me-4">
                    <i class="fas fa-plus me-2"></i>New Export Bill
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
                    <div class="col-md-3 mb-1 form-group d-flex align-items-end">
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
                <table id="exportBillTable" class="table style-3 dt-table-hover">
                    <thead>
                    <tr>
                        <th class="text-center">SL</th>
                        <th>Company</th>
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
                        <th>ITC</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th colspan="12" style="text-align:right">Total Amount:</th>
                        <th></th> <!-- Amount Total -->
                        <th></th> <!-- Bank Vat Total -->
                        <th></th> <!-- ITC Total -->
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            // Initialize DataTable with proper pagination icons
            const table = $('#exportBillTable').DataTable({
                "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
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
                    url: "{{ route('export-bills.data') }}",
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
                    { data: 'itc', name: 'itc', className: "text-right" },
                    { data: 'action', name: 'action', orderable:false, searchable:false, className:'text-center' }
                ],
                buttons: [
                    {
                        extend: 'copy',
                        className: 'dt-button',
                        text: '<i class="fas fa-copy me-1"></i>Copy'
                    },
                    {
                        extend: 'excel',
                        className: 'dt-button',
                        text: '<i class="fas fa-file-excel me-1"></i>Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'dt-button',
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF'
                    },
                    {
                        extend: 'print',
                        className: 'dt-button',
                        text: '<i class="fas fa-print me-1"></i>Print'
                    }
                ],
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

                    // ====== Amount Total (column index 12) ======
                    var totalAmount = api.column(12, { page: 'current' }).data()
                        .reduce((a, b) => intVal(a) + intVal(b), 0);

                    // ====== Bank VAT Total (column index 13) ======
                    var totalBankVat = api.column(13, { page: 'current' }).data()
                        .reduce((a, b) => intVal(a) + intVal(b), 0);

                    // ====== ITC Total (column index 14) ======
                    var totalItc = api.column(14, { page: 'current' }).data()
                        .reduce((a, b) => intVal(a) + intVal(b), 0);

                    // Update footer cells
                    $(api.column(12).footer()).html(
                        totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    );
                    $(api.column(13).footer()).html(
                        totalBankVat.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    );
                    $(api.column(14).footer()).html(
                        totalItc.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    );
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

            // Apply filter on Enter key press in filter fields
            $('.form-control-sm').keypress(function(e) {
                if (e.which == 13) { // Enter key
                    table.ajax.reload();
                }
            });

            // Apply filter on change (optional - uncomment if you want instant filtering)
            // $('#companyFilter, #monthFilter, #yearFilter').change(function() {
            //     table.ajax.reload();
            // });

            // Delete function
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
