@extends('layouts.layout')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-6"><h5 class="card-title">Edit Import Bill</h5></div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <a href="{{ route('import-bills.index') }}" class="btn btn-info btn-rounded mb-2 me-4">View Import Bill Summary</a>
                        </div>
                    </div>
                    <hr>

                    <form id="importBillForm" class="row g-3">
                        @csrf
                        <input type="hidden" name="form_token" value="{{ Str::random(40) }}">

                        {{-- Basic Information --}}
                        <div class="col-md-3 form-group">
                            <label>L/C No</label>
                            <input class="form-control form-control-sm" type="text" name="lc_no" value="{{ $bill->lc_no }}" required>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>L/C Date</label>
                            <input class="form-control form-control-sm" type="date" name="lc_date" value="{{ optional($bill->lc_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Bill No</label>
                            <input class="form-control form-control-sm" type="text" name="bill_no" value="{{ $bill->bill_no }}" required>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Bill Date</label>
                            <input class="form-control form-control-sm" type="date" name="bill_date" value="{{ optional($bill->bill_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Item</label>
                            <input class="form-control form-control-sm" type="text" name="item" value="{{ $bill->item }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Value</label>
                            <input class="form-control form-control-sm" type="number" name="value" value="{{ $bill->value }}" min="0.01" step="0.01" required>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>QTY</label>
                            <input class="form-control form-control-sm" type="number" name="qty" value="{{ $bill->qty }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Weight</label>
                            <input class="form-control form-control-sm" type="number" name="weight" value="{{ $bill->weight }}" step="0.01">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>B/E No</label>
                            <input class="form-control form-control-sm" type="text" name="be_no" value="{{ $bill->be_no }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>B/E Date</label>
                            <input class="form-control form-control-sm" type="date" name="be_date" value="{{ optional($bill->be_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Scan Fee</label>
                            <input class="form-control form-control-sm" type="number" name="scan_fee" id="scanFee" value="{{ $bill->scan_fee }}" step="0.01">
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Doc Fee</label>
                            <input class="form-control form-control-sm" type="number" name="doc_fee" id="docFee" value="{{ $bill->doc_fee }}" step="0.01">
                        </div>

                        {{-- 🔹 Bank Accounts --}}
                        <hr class="mt-3 mb-3">
                        <h5 class="mb-3">Bank Accounts</h5>

                        <div class="col-md-4 form-group">
                            <label for="mainAccount">Main Account (Dhaka Bank)</label>
                            <select class="form-control form-control-sm" name="account_id" id="mainAccount" required>
                                <option value="">-- Select Main Account --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}"
                                        {{ ($bill->account_id == $account->id) || (str_contains(strtolower($account->name), 'dhaka') && !$bill->account_id) ? 'selected' : '' }}>
                                        {{ $account->name }} (Balance: {{ number_format($account->balance,2) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">For doc fee, scan fee & other expenses</small>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="aitAccount">AIT Account (Sonali Bank)</label>
                            <select class="form-control form-control-sm" name="ait_account_id" id="aitAccount">
                                <option value="">-- Select AIT Account --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}"
                                        {{ ($bill->ait_account_id == $account->id) || (str_contains(strtolower($account->name), 'sonali') && !$bill->ait_account_id) ? 'selected' : '' }}>
                                        {{ $account->name }} (Balance: {{ number_format($account->balance,2) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">For AIT expenses only</small>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="portAccount">Port Bill Account (Janata Bank)</label>
                            <select class="form-control form-control-sm" name="port_account_id" id="portAccount">
                                <option value="">-- Select Port Bill Account --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}"
                                        {{ ($bill->port_account_id == $account->id) || (str_contains(strtolower($account->name), 'janata') && !$bill->port_account_id) ? 'selected' : '' }}>
                                        {{ $account->name }} (Balance: {{ number_format($account->balance,2) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">For Port Bill expenses only</small>
                        </div>

                        {{-- Expenses Section --}}
                        <hr class="mt-3 mb-3">
                        <h5 class="mb-3">Expenses</h5>

                        @php
                            $specialExpenses = [
                                'AIT (As Per Receipt)' => 'aitAccount',
                                'Port Bill (As Per Receipt)' => 'portAccount'
                            ];
                        @endphp

                        @foreach($expenseTypes as $i => $exp)
                            @php
                                $existing = $bill->expenses->firstWhere('expense_type', $exp);
                                $val = $existing ? $existing->amount : 0;
                            @endphp
                            <div class="row mb-2 expense-row" data-expense-type="{{ $exp }}">
                                <div class="col-md-1">{{ $i+1 }}</div>
                                <div class="col-md-7">
                                    <label>{{ $exp }}</label>
                                    @if(isset($specialExpenses[$exp]))
                                        <small class="text-info d-block">
                                            <i class="fas fa-info-circle"></i>
                                            Deducts from {{ str_replace('Account', '', $specialExpenses[$exp]) }} account
                                        </small>
                                    @else
                                        <small class="text-success d-block">
                                            <i class="fas fa-info-circle"></i>
                                            Deducts from main account
                                        </small>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control form-control-sm expense-input"
                                           name="expenses[{{ $exp }}]" value="{{ $val }}" min="0" step="0.01" onkeydown="return event.key !== 'Enter';">
                                </div>
                            </div>
                        @endforeach

                        {{-- Total Calculation Display --}}
                        <div class="col-md-12 mt-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Amount Breakdown</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>AIT Amount:</strong>
                                            <span id="aitTotal">{{ number_format($bill->expenses->where('expense_type', 'AIT (As Per Receipt)')->first()->amount ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Port Bill Amount:</strong>
                                            <span id="portTotal">{{ number_format($bill->expenses->where('expense_type', 'Port Bill (As Per Receipt)')->first()->amount ?? 0, 2) }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            @php
                                                $otherExpenses = $bill->expenses->whereNotIn('expense_type', ['AIT (As Per Receipt)', 'Port Bill (As Per Receipt)'])->sum('amount');
                                                $otherTotal = $otherExpenses + $bill->doc_fee + $bill->scan_fee;
                                            @endphp
                                            <strong>Other Amount:</strong>
                                            <span id="otherTotal">{{ number_format($otherTotal, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            @php
                                                $grandTotal = ($bill->expenses->where('expense_type', 'AIT (As Per Receipt)')->first()->amount ?? 0)
                                                            + ($bill->expenses->where('expense_type', 'Port Bill (As Per Receipt)')->first()->amount ?? 0)
                                                            + $otherTotal;
                                            @endphp
                                            <strong>Grand Total:</strong>
                                            <span id="grandTotal">{{ number_format($grandTotal, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary">Update Import Bill</button>
                            <a href="{{ route('import-bills.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <div id="formAlert" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>

    <script>
        $(function () {
            // Prevent form submission on Enter key for ALL input fields
            $(document).on('keydown', function(e) {
                if ($(e.target).closest('#importBillForm').length &&
                    (e.key === 'Enter' || e.keyCode === 13)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            });

            // Calculate totals when expense values change
            function calculateTotals() {
                let aitTotal = 0;
                let portTotal = 0;
                let otherTotal = 0;
                let docFee = parseFloat($('#docFee').val()) || 0;
                let scanFee = parseFloat($('#scanFee').val()) || 0;

                $('.expense-input').each(function() {
                    let value = parseFloat($(this).val()) || 0;
                    let expenseType = $(this).closest('.expense-row').data('expense-type');

                    if (expenseType === 'AIT (As Per Receipt)') {
                        aitTotal += value;
                    } else if (expenseType === 'Port Bill (As Per Receipt)') {
                        portTotal += value;
                    } else {
                        otherTotal += value;
                    }
                });

                // Add doc fee and scan fee to other total
                otherTotal += docFee + scanFee;

                $('#aitTotal').text(aitTotal.toFixed(2));
                $('#portTotal').text(portTotal.toFixed(2));
                $('#otherTotal').text(otherTotal.toFixed(2));
                $('#grandTotal').text((aitTotal + portTotal + otherTotal).toFixed(2));
            }

            // Bind calculation to expense inputs, doc fee, and scan fee
            $('.expense-input, #docFee, #scanFee').on('input', calculateTotals);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $("#importBillForm").validate({
                errorClass: 'text-danger',
                rules: {
                    lc_no: { required: true },
                    bill_no: { required: true },
                    value: { required: true, number: true, min: 0.01 },
                    account_id: { required: true }
                },
                messages: {
                    account_id: { required: "Please select main account for expenses" }
                },
                submitHandler: function(form) {
                    let formData = new FormData(form);
                    formData.append('_method', 'PUT');
                    let submitBtn = $(form).find('button[type="submit"]');

                    // Disable submit button to prevent double submission
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                    $.ajax({
                        url: "{{ route('import-bills.update', $bill->id) }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(res){
                            Swal.fire({
                                icon:'success',
                                title: 'Success!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(function(){
                                window.location.href = "{{ route('import-bills.index') }}";
                            }, 800);
                        },
                        error: function(xhr){
                            let errors = xhr.responseJSON?.errors;
                            if (errors) {
                                let html = "<ul>";
                                $.each(errors, function(k,v){ html += `<li>${v[0]}</li>`; });
                                html += "</ul>";
                                $("#formAlert").html(`<div class="alert alert-danger">${html}</div>`);
                            } else {
                                let message = xhr.responseJSON?.message || 'Something went wrong';
                                $("#formAlert").html(`<div class="alert alert-danger">${message}</div>`);
                            }

                            // Re-enable submit button
                            submitBtn.prop('disabled', false).html('Update Import Bill');

                            // Scroll to alert
                            $('html, body').animate({
                                scrollTop: $("#formAlert").offset().top - 100
                            }, 500);
                        }
                    });
                    return false;
                }
            });
        });
    </script>
@endsection
