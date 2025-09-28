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
                        <div class="col-md-6">
                            <h5 class="card-title">Edit Export Bill</h5>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <a href="{{ route('export-bills.index') }}" class="btn btn-info btn-rounded mb-2 me-4">View Export Bill Summary</a>
                        </div>
                    </div>
                    <hr>

                    <form id="importBillForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_token" value="{{ Str::uuid() }}">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Buyer Name *</label>
                                <select id="select-beast" name="buyer_id" class="form-control form-control-sm" required>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}" {{ $bill->buyer_id == $buyer->id ? 'selected' : '' }}>
                                            {{ $buyer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Bill No *</label>
                                <input type="text" name="bill_no" class="form-control form-control-sm" value="{{ $bill->bill_no }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Bill Date *</label>
                                <input type="date" name="bill_date" class="form-control form-control-sm"
                                       value="{{ $bill->bill_date ? $bill->bill_date->format('Y-m-d') : '' }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Invoice No *</label>
                                <input type="text" name="invoice_no" class="form-control form-control-sm" value="{{ $bill->invoice_no }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Invoice Date *</label>
                                <input type="date" name="invoice_date" class="form-control form-control-sm"
                                       value="{{ $bill->invoice_date ? $bill->invoice_date->format('Y-m-d') : '' }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>USD *</label>
                                <input type="number" step="0.01" name="usd" class="form-control form-control-sm" value="{{ $bill->usd }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Total Qty</label>
                                <input type="number" name="total_qty" class="form-control form-control-sm" value="{{ $bill->total_qty }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>CTN No</label>
                                <input type="text" name="ctn_no" class="form-control form-control-sm" value="{{ $bill->ctn_no }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>B/E No</label>
                                <input type="text" name="be_no" class="form-control form-control-sm" value="{{ $bill->be_no }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>B/E Date</label>
                                <input type="date" name="be_date" class="form-control form-control-sm"
                                       value="{{ $bill->be_date ? $bill->be_date->format('Y-m-d') : '' }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Qty PCS</label>
                                <input type="number" name="qty_pcs" class="form-control form-control-sm" value="{{ $bill->qty_pcs }}">
                            </div>

                            {{-- Bank Account --}}
                            <div class="col-md-3 mb-3">
                                <label>Vat Account *</label>
                                <select name="from_account_id" class="form-control form-control-sm" required>
                                    <option value="">-- Select Account --</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ $bill->from_account_id == $account->id ? 'selected' : '' }} readonly="">
                                            {{ $account->name }} (Balance: {{ number_format($account->balance, 2) }})
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
                                <div class="col-md-7"><label>{{ $exp }}</label></div>
                                <div class="col-md-4">
                                    <input type="number" step="0.01" min="0"
                                           name="expenses[{{ $exp }}]"
                                           class="form-control form-control-sm expense-input"
                                           value="{{ $expenses[$exp] ?? 0 }}">
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary btn-sm mt-3">Update</button>
                        <a href="{{ route('export-bills.index') }}" class="btn btn-secondary btn-sm mt-3">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script>
        $(function(){
            $('#importBillForm').submit(function(e){
                e.preventDefault();
                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');
                $submitBtn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('export-bills.update', $bill->id) }}",
                    type: "POST",
                    data: $form.serialize(),
                    success: function(res){
                        Swal.fire("Success", res.message ?? "Bill updated successfully!", "success")
                            .then(() => window.location.href = "{{ route('export-bills.index') }}");
                    },
                    error: function(xhr){
                        let errors = xhr.responseJSON?.errors;
                        let msg = errors ? Object.values(errors).join("<br>") : (xhr.responseJSON?.message ?? "Something went wrong.");
                        Swal.fire("Error", msg, "error");
                    },
                    complete: function(){
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
