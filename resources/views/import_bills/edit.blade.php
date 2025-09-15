@extends('layouts.layout')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endsection

@section('content')
    <div class="row layout-spacing ">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card"><div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6"><h5 class="card-title">Edit Import Bill</h5></div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <a href="{{ route('import-bills.index') }}" class="btn btn-info btn-rounded mb-2 me-4">View Import Bill Summary</a>
                        </div>
                    </div>
                    <hr>

                    <form id="importBillForm" class="row g-3">
                        @csrf
                        {{-- We'll append _method=PUT in ajax request --}}

                        <div class="col-md-3 form-group">
                            <label>L/C No</label>
                            <input class="form-control form-control-sm" type="text" name="lc_no" id="lcNoText" value="{{ $bill->lc_no }}" required>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>L/C Date</label>
                            <input class="form-control form-control-sm" type="date" name="lc_date" id="lcDate" value="{{ optional($bill->lc_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Bill No</label>
                            <input class="form-control form-control-sm" type="text" name="bill_no" id="billNo" value="{{ $bill->bill_no }}" required>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Bill Date</label>
                            <input class="form-control form-control-sm" type="date" name="bill_date" id="billDate" value="{{ optional($bill->bill_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Item</label>
                            <input class="form-control form-control-sm" type="text" name="item" id="item" value="{{ $bill->item }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Value</label>
                            <input class="form-control form-control-sm" type="number" name="value" id="value" value="{{ $bill->value }}" min="0.01" step="0.01" required>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>QTY</label>
                            <input class="form-control form-control-sm" type="number" name="qty" id="qty" value="{{ $bill->qty }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Weight</label>
                            <input class="form-control form-control-sm" type="number" name="weight" id="weight" value="{{ $bill->weight }}" step="0.01">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>B/E No</label>
                            <input class="form-control form-control-sm" type="text" name="be_no" id="beNo" value="{{ $bill->be_no }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>B/E Date</label>
                            <input class="form-control form-control-sm" type="date" name="be_date" id="beDate" value="{{ optional($bill->be_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Scan Fee</label>
                            <input class="form-control form-control-sm" type="number" name="scan_fee" id="scanFee" value="{{ $bill->scan_fee }}" step="0.01">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Doc Fee</label>
                            <input class="form-control form-control-sm" type="number" name="doc_fee" id="docFee" value="{{ $bill->doc_fee }}" step="0.01">
                        </div>

                        <hr class="mt-3 mb-3">
                        <h5 class="mb-3">Expenses</h5>

                        @foreach($expenseTypes as $i => $exp)
                            @php
                                $existing = $bill->expenses->firstWhere('expense_type', $exp);
                                $val = $existing ? $existing->amount : 0;
                            @endphp
                            <div class="row mb-2">
                                <div class="col-md-1">{{ $i+1 }}</div>
                                <div class="col-md-7"><label>{{ $exp }}</label></div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control form-control-sm expense-input"
                                           name="expenses[{{ $exp }}]" value="{{ $val }}" min="0" step="0.01">
                                </div>
                            </div>
                        @endforeach

                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>

                    <div id="formAlert" class="mt-3"></div>
                </div></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>

    <script>
        $(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $("#importBillForm").validate({
                errorClass: 'text-danger',
                rules: {
                    company_name: { required: true },
                    lc_no: { required: true },
                    bill_no: { required: true },
                    value: { required: true, number: true, min: 0.01 }
                },
                submitHandler: function(form) {
                    let formData = new FormData(form);
                    formData.append('_method','PUT');

                    $.ajax({
                        url: "{{ route('import-bills.update', $bill->id) }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(res){
                            Swal.fire({ icon:'success', title: res.message, timer:1500, showConfirmButton:false });
                            setTimeout(function(){ window.location.href = "{{ route('import-bills.index') }}"; }, 800);
                        },
                        error: function(xhr){
                            let errors = xhr.responseJSON?.errors;
                            if (errors) {
                                let html = "<ul>";
                                $.each(errors, function(k,v){ html += `<li>${v[0]}</li>`; });
                                html += "</ul>";
                                $("#formAlert").html(`<div class="alert alert-danger">${html}</div>`);
                            } else {
                                $("#formAlert").html(`<div class="alert alert-danger">Something went wrong</div>`);
                            }
                        }
                    });
                    return false;
                }
            });
        });
    </script>
@endsection
