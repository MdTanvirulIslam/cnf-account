@extends('layouts.layout')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">
    <style>
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #007bff;
        }
        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .form-card {
            background: white;
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .form-card .card-body {
            padding: 25px;
        }
        .expense-row {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 10px 0;
        }
        .expense-row:hover {
            background-color: #f8f9ff;
            transform: translateX(5px);
        }
        .account-balance {
            font-size: 0.85em;
            color: #28a745;
            font-weight: 500;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .input-highlight {
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .input-highlight:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
            transform: translateY(-2px);
        }
        .expense-badge {
            background: #007bff;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75em;
        }
        .total-display {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        .readonly-field {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            color: #6c757d;
        }
        .update-btn {
            background: #28a745;
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
            background: #218838;
        }
        .cancel-btn {
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
        }
        .expense-number {
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .edit-indicator {
            background: #ffc107;
            color: #212529;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .prefix-hint {
            font-size: 0.75em;
            color: #6c757d;
            margin-top: 2px;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 layout-top-spacing">
            <div class="card form-card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4 class="card-title mb-1">✏️ Edit Import Bill</h4>
                            <p class="text-muted mb-0">Update import bill #{{ $bill->bill_no }}</p>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end align-items-center">
                            <span class="edit-indicator me-3">
                                <i class="fas fa-edit me-1"></i>Editing Mode
                            </span>
                            <a href="{{ route('import-bills.index') }}" class="btn btn-info btn-rounded">
                                <i class="fas fa-list me-2"></i>View All Bills
                            </a>
                        </div>
                    </div>
                    <hr class="mb-4">

                    <form id="importBillForm" class="row g-3">
                        @csrf
                        <input type="hidden" name="form_token" value="{{ Str::random(40) }}">

                        {{-- Basic Information Section --}}
                        <div class="form-section">
                            <h5 class="section-title">📋 Basic Information</h5>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="required-field">L/C No</label>
                                    <input class="form-control form-control-sm input-highlight" type="text" name="lc_no" value="{{ $bill->lc_no }}" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>L/C Date</label>
                                    <input class="form-control form-control-sm input-highlight" type="date" name="lc_date" value="{{ optional($bill->lc_date)->format('Y-m-d') }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Bill No</label>
                                    <input class="form-control form-control-sm input-highlight bill-no-field" type="text" name="bill_no" value="{{ $bill->bill_no }}" required>
                                    <div class="prefix-hint">Prefix: MFL/IMP/</div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Bill Date</label>
                                    <input class="form-control form-control-sm input-highlight" type="date" name="bill_date" value="{{ optional($bill->bill_date)->format('Y-m-d') }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Item Description</label>
                                    <input class="form-control form-control-sm input-highlight" type="text" name="item" value="{{ $bill->item }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Value</label>
                                    <input class="form-control form-control-sm input-highlight" type="number" name="value" value="{{ $bill->value }}" min="0.01" step="0.01" required>
                                    <small class="text-muted">Total import value</small>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Quantity</label>
                                    <input class="form-control form-control-sm input-highlight" type="number" name="qty" value="{{ $bill->qty }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Weight (KG)</label>
                                    <input class="form-control form-control-sm input-highlight" type="number" name="weight" value="{{ $bill->weight }}" step="0.01">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>B/E No</label>
                                    <input class="form-control form-control-sm input-highlight be-no-field" type="text" name="be_no" value="{{ $bill->be_no }}">
                                    <div class="prefix-hint">Prefix: C-</div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>B/E Date</label>
                                    <input class="form-control form-control-sm input-highlight" type="date" name="be_date" value="{{ optional($bill->be_date)->format('Y-m-d') }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Scan Fee</label>
                                    <input class="form-control form-control-sm input-highlight" type="number" name="scan_fee" id="scanFee" value="{{ $bill->scan_fee }}" step="0.01">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Document Fee</label>
                                    <input class="form-control form-control-sm input-highlight" type="number" name="doc_fee" id="docFee" value="{{ $bill->doc_fee }}" step="0.01">
                                </div>
                            </div>
                        </div>

                        {{-- Bank Accounts Section --}}
                        <div class="form-section">
                            <h5 class="section-title">🏦 Bank Accounts</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="required-field">Main Account (Dhaka Bank)</label>
                                    <select class="form-control form-control-sm readonly-field" name="account_id" required readonly>
                                        <option value="">-- Select Main Account --</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ ($bill->account_id == $account->id) || (str_contains(strtolower($account->name), 'dhaka') && !$bill->account_id) ? 'selected' : '' }}>
                                                {{ $account->name }}
                                                <span class="account-balance">(Balance: {{ number_format($account->balance,2) }})</span>
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        For document fee, scan fee & other expenses
                                    </small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>AIT Account (Sonali Bank)</label>
                                    <select class="form-control form-control-sm readonly-field" name="ait_account_id" readonly>
                                        <option value="">-- Select AIT Account --</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ ($bill->ait_account_id == $account->id) || (str_contains(strtolower($account->name), 'sonali') && !$bill->ait_account_id) ? 'selected' : '' }}>
                                                {{ $account->name }}
                                                <span class="account-balance">(Balance: {{ number_format($account->balance,2) }})</span>
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        For AIT expenses only
                                    </small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Port Bill Account (Janata Bank)</label>
                                    <select class="form-control form-control-sm readonly-field" name="port_account_id" readonly>
                                        <option value="">-- Select Port Bill Account --</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ ($bill->port_account_id == $account->id) || (str_contains(strtolower($account->name), 'janata') && !$bill->port_account_id) ? 'selected' : '' }}>
                                                {{ $account->name }}
                                                <span class="account-balance">(Balance: {{ number_format($account->balance,2) }})</span>
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        For Port Bill expenses only
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Expenses Section --}}
                        <div class="form-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="section-title mb-0">💰 Expenses</h5>
                                <span class="expense-badge">{{ count($expenseTypes) }} Expense Types</span>
                            </div>

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
                                <div class="row mb-2 expense-row align-items-center" data-expense-type="{{ $exp }}">
                                    <div class="col-md-1 d-flex justify-content-center">
                                        <div class="expense-number">{{ $i+1 }}</div>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="mb-1">{{ $exp }}</label>
                                        @if(isset($specialExpenses[$exp]))
                                            <small class="text-info d-block">
                                                <i class="fas fa-university me-1"></i>
                                                Deducts from {{ str_replace('Account', '', $specialExpenses[$exp]) }} account
                                            </small>
                                        @else
                                            <small class="text-success d-block">
                                                <i class="fas fa-university me-1"></i>
                                                Deducts from main account
                                            </small>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" class="form-control form-control-sm expense-input input-highlight"
                                                   name="expenses[{{ $exp }}]" value="{{ $val }}" min="0" step="0.01"
                                                   onkeydown="return event.key !== 'Enter';">
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Total Calculation Display --}}
                            <div class="total-display">
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
                                            $totalExpenses = $bill->expenses->whereNotIn('expense_type', ['AIT (As Per Receipt)', 'Port Bill (As Per Receipt)'])->sum('amount');
                                        @endphp
                                        <strong>Other Amount:</strong>
                                        <span id="otherTotal">{{ number_format($totalExpenses, 2) }}</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        @php
                                            $grandTotal = ($bill->expenses->where('expense_type', 'AIT (As Per Receipt)')->first()->amount ?? 0)
                                                        + ($bill->expenses->where('expense_type', 'Port Bill (As Per Receipt)')->first()->amount ?? 0)
                                                        + $totalExpenses;
                                        @endphp
                                        <strong>Grand Total:</strong>
                                        <span id="grandTotal">{{ number_format($grandTotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-info btn-rounded _effect--ripple waves-effect waves-light">
                                    <i class="fas fa-save me-2"></i>Update Import Bill
                                </button>
                                <a href="{{ route('import-bills.index') }}" class="btn btn-light cancel-btn">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                    </form>

                    <div id="formAlert" class="mt-4"></div>
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
            // Prefix configuration
            const prefixes = {
                bill_no: 'MFL/IMP/',
                be_no: 'C-'
            };

            // Function to add prefix to input value
            function addPrefix(fieldName, value) {
                const prefix = prefixes[fieldName];
                // Remove prefix if already exists to avoid duplication
                let cleanValue = value.replace(prefix, '');
                return prefix + cleanValue;
            }

            // Function to remove prefix for display (if needed)
            function removePrefix(fieldName, value) {
                const prefix = prefixes[fieldName];
                return value.replace(prefix, '');
            }

            // Initialize prefixes on page load for edit form
            function initializePrefixes() {
                Object.keys(prefixes).forEach(fieldName => {
                    const $input = $(`[name="${fieldName}"]`);
                    const currentValue = $input.val();

                    // For edit form, we want to show the value without prefix for easier editing
                    if (currentValue && currentValue.startsWith(prefixes[fieldName])) {
                        $input.data('original-value', currentValue);
                        $input.val(removePrefix(fieldName, currentValue));
                    }
                });
            }

            // Handle input events to maintain prefixes
            Object.keys(prefixes).forEach(fieldName => {
                const $input = $(`[name="${fieldName}"]`);
                const prefix = prefixes[fieldName];

                $input.on('focus', function() {
                    let value = $(this).val();
                    // Store the value without prefix for easier editing
                    if (value && !value.startsWith(prefix)) {
                        $(this).data('original-value', value);
                    }
                });

                $input.on('blur', function() {
                    let value = $(this).val();
                    // Add prefix when focus is lost if value exists
                    if (value && !value.startsWith(prefix)) {
                        $(this).val(addPrefix(fieldName, value));
                    }
                });
            });

            // Initialize prefixes when page loads
            initializePrefixes();

            // Prevent Enter key submission
            $('#importBillForm').on('keydown', 'input, select, textarea', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });

            // Calculate totals when expense values change
            function calculateTotals() {
                let aitTotal = 0;
                let portTotal = 0;
                let otherTotal = 0;

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
                    // Ensure all prefixed fields have their prefixes before submission
                    Object.keys(prefixes).forEach(fieldName => {
                        const $input = $(`[name="${fieldName}"]`);
                        let value = $input.val();
                        if (value && !value.startsWith(prefixes[fieldName])) {
                            $input.val(addPrefix(fieldName, value));
                        }
                    });

                    let formData = new FormData(form);
                    formData.append('_method', 'PUT');
                    let submitBtn = $(form).find('button[type="submit"]');
                    let originalText = submitBtn.html();

                    // Disable submit button and show loading
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

                    $.ajax({
                        url: "{{ route('import-bills.update', $bill->id) }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(res){
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.message || 'Import Bill updated successfully!',
                                showConfirmButton: false,
                                timer: 1500,
                                background: '#f8f9fa',
                                iconColor: '#28a745'
                            }).then(() => {
                                window.location.href = "{{ route('import-bills.index') }}";
                            });
                        },
                        error: function(xhr){
                            let errors = xhr.responseJSON?.errors;
                            let msg = "Something went wrong. Please try again.";

                            if (errors) {
                                msg = Object.values(errors).map(error => Array.isArray(error) ? error[0] : error).join("<br>");
                            } else if (xhr.responseJSON?.message) {
                                msg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                html: msg,
                                background: '#f8f9fa',
                                iconColor: '#dc3545'
                            });
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                    return false;
                }
            });
        });
    </script>
@endsection
