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
                        <div class="col-md-6"><h5 class="card-title">Export Bill Entry Form</h5></div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <a href="{{ route('export-bills.index') }}" class="btn btn-info btn-rounded mb-2 me-4">View Export Bill Summary</a>
                        </div>
                    </div>
                    <hr>

                    <form id="exportBillForm">
                        @csrf
                        {{-- Hidden form_token to avoid duplicate submission --}}
                        <input type="hidden" name="form_token" value="{{ uniqid('', true) }}">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Buyer Name *</label>
                                <select name="buyer_id" class="form-control form-control-sm" required>
                                    <option value="">-- Select Buyer --</option>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Bill No *</label>
                                <input type="text" name="bill_no" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Bill Date *</label>
                                <input type="date" name="bill_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Invoice No *</label>
                                <input type="text" name="invoice_no" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Invoice Date *</label>
                                <input type="date" name="invoice_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>USD *</label>
                                <input type="number" step="0.01" name="usd" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Total CTN *</label>
                                <input type="number" name="total_qty" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>CTN No</label>
                                <input type="text" name="ctn_no" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>B/E No</label>
                                <input type="text" name="be_no" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>B/E Date</label>
                                <input type="date" name="be_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Qty PCS *</label>
                                <input type="number" name="qty_pcs" class="form-control form-control-sm" required>
                            </div>

                            {{-- Bank Account --}}

                            <div class="col-md-3 mb-3">
                                <label>Vat Account *</label>
                                <select name="from_account_id" id="from_account_id" class="form-control form-control-sm" readonly>

                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}"
                                            {{ str_contains(strtolower($account->name), 'sonali') ? 'selected' : '' }}>
                                            {{ $account->name }} (Balance: {{ number_format($account->balance,2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>From Account *</label>
                                <select name="account_id" id="account_id" class="form-control form-control-sm" readonly="">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}"
                                            {{ str_contains(strtolower($account->name), 'dhaka') ? 'selected' : '' }}>
                                            {{ $account->name }} (Balance: {{ number_format($account->balance,2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h6 class="mt-4">Expenses</h6>
                        @foreach($expenseTypes as $i => $exp)
                            <div class="row mb-2">
                                <div class="col-md-1">{{ $i+1 }}</div>
                                <div class="col-md-7">
                                    <label>{{ $exp }}</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" step="0.01" min="0"
                                           name="expenses[{{ $exp }}]"
                                           class="form-control form-control-sm expense-input"
                                           value="0" onkeydown="return event.key !== 'Enter';">
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary btn-sm mt-3">Submit</button>
                        <a href="{{ route('export-bills.index') }}" class="btn btn-secondary btn-sm mt-3">Back</a>
                    </form>

                    <div id="formAlert" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script>
        $(function () {
            // Completely disable Enter key for the entire document when focused on form elements
            // Approach 1: Event delegation on form
            $('#exportBillForm').on('keydown', 'input, select, textarea', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });

            // Approach 2: Also bind directly to expense inputs after page load
            $('.expense-input').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });

            // Approach 3: Prevent form submit on Enter at form level
            $('#exportBillForm').submit(function(e) {
                if (e.originalEvent && e.originalEvent.submitter === undefined) {
                    // Form was submitted by Enter key
                    e.preventDefault();
                    return false;
                }
            });

            $('#exportBillForm').submit(function(e) {

                e.preventDefault();

                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');

                // Disable button to prevent double-click
                $submitBtn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('export-bills.store') }}",
                    type: "POST",
                    data: $form.serialize(),
                    success: function(res) {
                        Swal.fire("Success", res.message ?? "Bill saved successfully!", "success").then(() => {
                            // Optionally redirect
                            window.location.href = "{{ route('export-bills.index') }}";
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let msg = "Something went wrong.";
                        if (errors) {
                            msg = Object.values(errors).join("<br>");
                        }
                        Swal.fire("Error", msg, "error");
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        });

    </script>
@endsection
