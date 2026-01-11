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
        .prefix-display {
            font-size: 0.75em;
            color: #007bff;
            font-weight: 500;
            background: #e7f1ff;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
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
                            <h4 class="card-title mb-1">✏️ Edit Export Bill</h4>
                            <p class="text-muted mb-0">Update export bill #{{ $bill->bill_no }}</p>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end align-items-center">
                            <span class="edit-indicator me-3">
                                <i class="fas fa-edit me-1"></i>Editing Mode
                            </span>
                            <a href="{{ route('export-bills.index') }}" class="btn btn-info btn-rounded">
                                <i class="fas fa-list me-2"></i>View All Bills
                            </a>
                        </div>
                    </div>
                    <hr class="mb-4">

                    <form id="exportBillForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_token" value="{{ Str::uuid() }}">

                        {{-- Basic Information Section --}}
                        <div class="form-section">
                            <h5 class="section-title">📋 Basic Information</h5>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Company Name</label>
                                    <select name="company_name" class="form-control form-control-sm input-highlight company-select" required>
                                        <option value="">Select Company Name</option>
                                        <option value="MULTI FABS LTD" {{ $bill->company_name == 'MULTI FABS LTD' ? 'selected' : '' }}>MULTI FABS LTD</option>
                                        <option value="EMS APPARELS LTD" {{ $bill->company_name == 'EMS APPARELS LTD' ? 'selected' : '' }}>EMS APPARELS LTD</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Buyer Name</label>
                                    <select name="buyer_id" class="form-control form-control-sm input-highlight" required>
                                        @foreach($buyers as $buyer)
                                            <option value="{{ $buyer->id }}" {{ $bill->buyer_id == $buyer->id ? 'selected' : '' }}>
                                                {{ $buyer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Bill No</label>
                                    <input type="text" name="bill_no" class="form-control form-control-sm input-highlight bill-no-field"
                                           value="{{ $bill->bill_no }}" required>
                                    <div class="prefix-hint" id="billNoPrefixHint">
                                        @php
                                            if($bill->company_name == 'MULTI FABS LTD') {
                                                echo 'Prefix: MFL/EXP/';
                                            } elseif($bill->company_name == 'EMS APPARELS LTD') {
                                                echo 'Prefix: EMS/EXP/';
                                            } else {
                                                echo 'Prefix: MFL/EXP/ (Default)';
                                            }
                                        @endphp
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Bill Date</label>
                                    <input type="date" name="bill_date" class="form-control form-control-sm input-highlight"
                                           value="{{ $bill->bill_date ? $bill->bill_date->format('Y-m-d') : '' }}" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Invoice No</label>
                                    <input type="text" name="invoice_no" class="form-control form-control-sm input-highlight invoice-no-field"
                                           value="{{ $bill->invoice_no }}" required>
                                    <div class="prefix-hint" id="invoiceNoPrefixHint">
                                        @php
                                            if($bill->company_name == 'MULTI FABS LTD') {
                                                echo 'Prefix: MFL/';
                                            } elseif($bill->company_name == 'EMS APPARELS LTD') {
                                                echo 'Prefix: EMS/';
                                            } else {
                                                echo 'Prefix: MFL/ (Default)';
                                            }
                                        @endphp
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Invoice Date</label>
                                    <input type="date" name="invoice_date" class="form-control form-control-sm input-highlight"
                                           value="{{ $bill->invoice_date ? $bill->invoice_date->format('Y-m-d') : '' }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">USD Amount</label>
                                    <input type="number" step="0.01" name="usd" class="form-control form-control-sm input-highlight"
                                           value="{{ $bill->usd }}" required>
                                    <small class="text-muted">Amount in USD</small>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="required-field">Total CTN</label>
                                    <input type="number" name="total_qty" class="form-control form-control-sm input-highlight"
                                           value="{{ $bill->total_qty }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>B/E No</label>
                                    <input type="text" name="be_no" class="form-control form-control-sm input-highlight be-no-field"
                                           value="{{ $bill->be_no }}">
                                    <div class="prefix-hint">Prefix: C- (Always)</div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>B/E Date</label>
                                    <input type="date" name="be_date" class="form-control form-control-sm input-highlight"
                                           value="{{ $bill->be_date ? $bill->be_date->format('Y-m-d') : '' }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Quantity PCS</label>
                                    <input type="number" name="qty_pcs" class="form-control form-control-sm input-highlight"
                                           value="{{ $bill->qty_pcs }}">
                                </div>

                                <div class="col-md-6 mb-6">
                                    <label> ITC (INCOME TAX ON C & F COMMISSION) </label>
                                    <input class="form-control form-control-sm input-highlight" type="number" name="itc" id="itc" value="{{ $bill->itc }}" step="0.01">
                                </div>
                            </div>
                        </div>

                        {{-- Bank Accounts Section --}}
                        <div class="form-section">
                            <h5 class="section-title">🏦 Bank Accounts</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="required-field">VAT Account</label>
                                    <select name="from_account_id" class="form-control form-control-sm readonly-field" readonly>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" {{ $bill->from_account_id == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                                <span class="account-balance">(Balance: {{ number_format($account->balance, 2) }})</span>
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        For VAT & Other expenses (Auto-managed)
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="required-field">Main Account</label>
                                    <select name="account_id" class="form-control form-control-sm readonly-field" readonly>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" {{ $bill->account_id == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                                <span class="account-balance">(Balance: {{ number_format($account->balance,2) }})</span>
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        For other expenses (Auto-managed)
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
                                    'Bank C & F Vat & Others (As Per Receipt)' => 'vatAccount'
                                ];
                            @endphp

                            @foreach($expenseTypes as $i => $exp)
                                <div class="row mb-2 expense-row align-items-center" data-expense-type="{{ $exp }}">
                                    <div class="col-md-1 d-flex justify-content-center">
                                        <div class="expense-number">{{ $i+1 }}</div>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="mb-1">{{ $exp }}</label>
                                        @if(isset($specialExpenses[$exp]))
                                            <small class="text-info d-block">
                                                <i class="fas fa-university me-1"></i>
                                                Deducts from VAT Account
                                            </small>
                                        @else
                                            <small class="text-success d-block">
                                                <i class="fas fa-university me-1"></i>
                                                Deducts from Main Account
                                            </small>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01" min="0"
                                                   name="expenses[{{ $exp }}]"
                                                   class="form-control form-control-sm expense-input input-highlight"
                                                   value="{{ $expenses[$exp] ?? 0 }}"
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
                                        <strong>VAT Amount:</strong>
                                        <span id="vatTotal">{{ number_format($expenses['Bank C & F Vat & Others (As Per Receipt)'] ?? 0, 2) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        @php
                                            $otherTotal = 0;
                                            foreach($expenses as $type => $amount) {
                                                if($type !== 'Bank C & F Vat & Others (As Per Receipt)') {
                                                    $otherTotal += $amount;
                                                }
                                            }
                                        @endphp
                                        <strong>Other Amount:</strong>
                                        <span id="otherTotal">{{ number_format($otherTotal, 2) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        @php
                                            $grandTotal = ($expenses['Bank C & F Vat & Others (As Per Receipt)'] ?? 0) + $otherTotal;
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
                                    <i class="fas fa-save me-2"></i>Update Export Bill
                                </button>
                                <a href="{{ route('export-bills.index') }}" class="btn btn-light cancel-btn">
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
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script>
        $(function () {
            // Dynamic prefix configuration based on company
            const companyPrefixes = {
                'MULTI FABS LTD': 'MFL',
                'EMS APPARELS LTD': 'EMS'
            };

            // Prefix patterns for each field
            const fieldPrefixes = {
                bill_no: '/EXP/',
                invoice_no: '/',
                be_no: 'C-'
            };

            // Function to get current company prefix
            function getCurrentPrefix() {
                const companyName = $('[name="company_name"]').val();
                return companyPrefixes[companyName] || 'MFL'; // Default to MFL
            }

            // Function to get full prefix for a field
            function getFullPrefix(fieldName) {
                const companyPrefix = getCurrentPrefix();
                if (!companyPrefix) return '';

                // Special handling for be_no (always starts with C-)
                if (fieldName === 'be_no') {
                    return fieldPrefixes[fieldName];
                }

                // For bill_no and invoice_no: companyPrefix + field-specific suffix
                return companyPrefix + fieldPrefixes[fieldName];
            }

            // Function to update prefix hints
            function updatePrefixHints() {
                const companyName = $('[name="company_name"]').val();
                let billPrefix = 'MFL/EXP/';
                let invoicePrefix = 'MFL/';

                if (companyName === 'MULTI FABS LTD') {
                    billPrefix = 'MFL/EXP/';
                    invoicePrefix = 'MFL/';
                } else if (companyName === 'EMS APPARELS LTD') {
                    billPrefix = 'EMS/EXP/';
                    invoicePrefix = 'EMS/';
                }

                $('#billNoPrefixHint').text('Prefix: ' + billPrefix);
                $('#invoiceNoPrefixHint').text('Prefix: ' + invoicePrefix);
            }

            // Function to add prefix to input value
            function addPrefix(fieldName, value) {
                const fullPrefix = getFullPrefix(fieldName);
                if (!fullPrefix) return value;

                // Remove any existing prefix to avoid duplication
                let cleanValue = value;
                Object.values(companyPrefixes).forEach(prefix => {
                    // Remove both possible formats
                    cleanValue = cleanValue.replace(prefix + fieldPrefixes[fieldName], '');
                    cleanValue = cleanValue.replace(prefix, '');
                });
                // Also remove field-specific suffix
                cleanValue = cleanValue.replace(fieldPrefixes[fieldName], '');

                return fullPrefix + cleanValue;
            }

            // Function to remove prefix for display
            function removePrefix(fieldName, value) {
                const fullPrefix = getFullPrefix(fieldName);
                if (fullPrefix && value.startsWith(fullPrefix)) {
                    return value.replace(fullPrefix, '');
                }
                return value;
            }

            // Initialize prefixes on page load
            function initializePrefixes() {
                updatePrefixHints();

                // Remove prefixes from bill_no and invoice_no for editing
                const $billNoField = $('.bill-no-field');
                const $invoiceNoField = $('.invoice-no-field');

                const billNoValue = $billNoField.val();
                const invoiceNoValue = $invoiceNoField.val();

                // Store original values
                $billNoField.data('original-value', billNoValue);
                $invoiceNoField.data('original-value', invoiceNoValue);

                // Remove prefix for editing
                if (billNoValue) {
                    $billNoField.val(removePrefix('bill_no', billNoValue));
                }
                if (invoiceNoValue) {
                    $invoiceNoField.val(removePrefix('invoice_no', invoiceNoValue));
                }
            }

            // Handle company change event
            $('[name="company_name"]').change(function() {
                updatePrefixHints();

                const companyName = $(this).val();
                if (!companyName) {
                    // Clear prefixes if no company selected
                    Object.keys(fieldPrefixes).forEach(fieldName => {
                        if (fieldName !== 'be_no') {
                            const $input = $(`[name="${fieldName}"]`);
                            const originalValue = $input.data('original-value') || '';
                            $input.val(originalValue);
                        }
                    });
                    return;
                }

                // Update prefixes for bill_no and invoice_no based on selected company
                Object.keys(fieldPrefixes).forEach(fieldName => {
                    // Skip be_no as it doesn't depend on company
                    if (fieldName === 'be_no') return;

                    const $input = $(`[name="${fieldName}"]`);
                    const currentValue = $input.val();

                    if (currentValue) {
                        // Replace existing prefix with new company prefix
                        const newValue = addPrefix(fieldName, currentValue);
                        $input.val(newValue);
                        $input.data('original-value', newValue);
                    }
                });
            });

            // Handle input events to maintain prefixes
            Object.keys(fieldPrefixes).forEach(fieldName => {
                const $input = $(`[name="${fieldName}"]`);

                $input.on('input', function() {
                    const companyName = $('[name="company_name"]').val();
                    if (!companyName) return;

                    let value = $(this).val();
                    const fullPrefix = getFullPrefix(fieldName);

                    // If user deletes the prefix, add it back
                    if (value && fullPrefix && !value.startsWith(fullPrefix)) {
                        $(this).val(addPrefix(fieldName, value));
                    }
                });

                $input.on('focus', function() {
                    const companyName = $('[name="company_name"]').val();
                    if (!companyName) return;

                    let value = $(this).val();
                    const fullPrefix = getFullPrefix(fieldName);

                    // Store the value without prefix for easier editing
                    if (value && fullPrefix && value.startsWith(fullPrefix)) {
                        $(this).data('original-value', value);
                        $(this).val(removePrefix(fieldName, value));
                    }
                });

                $input.on('blur', function() {
                    const companyName = $('[name="company_name"]').val();
                    if (!companyName) return;

                    let value = $(this).val();
                    const fullPrefix = getFullPrefix(fieldName);

                    // Restore prefix when focus is lost
                    if (value && fullPrefix && !value.startsWith(fullPrefix)) {
                        $(this).val(addPrefix(fieldName, value));
                    }
                });
            });

            // Initialize prefixes when page loads
            initializePrefixes();

            // Prevent Enter key submission
            $('#exportBillForm').on('keydown', 'input, select, textarea', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });

            // Calculate totals when expense values change
            function calculateTotals() {
                let vatTotal = 0;
                let otherTotal = 0;
                const vatType = 'Bank C & F Vat & Others (As Per Receipt)';

                $('.expense-input').each(function() {
                    let value = parseFloat($(this).val()) || 0;
                    let expenseType = $(this).closest('.expense-row').data('expense-type');

                    if (expenseType === vatType) {
                        vatTotal += value;
                    } else {
                        otherTotal += value;
                    }
                });

                $('#vatTotal').text(vatTotal.toFixed(2));
                $('#otherTotal').text(otherTotal.toFixed(2));
                $('#grandTotal').text((vatTotal + otherTotal).toFixed(2));
            }

            // Bind calculation to expense inputs
            $('.expense-input').on('input', calculateTotals);

            // Form submission
            $('#exportBillForm').submit(function(e) {
                e.preventDefault();

                const companyName = $('[name="company_name"]').val();
                if (!companyName) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Please select a company first.",
                        background: '#f8f9fa',
                        iconColor: '#dc3545'
                    });
                    return false;
                }

                // Ensure all prefixed fields have correct prefixes before submission
                Object.keys(fieldPrefixes).forEach(fieldName => {
                    const $input = $(`[name="${fieldName}"]`);

                    let value = $input.val();
                    const fullPrefix = getFullPrefix(fieldName);

                    if (value && fullPrefix && !value.startsWith(fullPrefix)) {
                        $input.val(addPrefix(fieldName, value));
                    }
                });

                let $form = $(this);
                let $submitBtn = $form.find('button[type="submit"]');
                let originalText = $submitBtn.html();

                // Disable button and show loading
                $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

                $.ajax({
                    url: "{{ route('export-bills.update', $bill->id) }}",
                    type: "POST",
                    data: $form.serialize(),
                    success: function(res) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: res.message || "Export Bill updated successfully!",
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#f8f9fa',
                            iconColor: '#28a745'
                        }).then(() => {
                            window.location.href = "{{ route('export-bills.index') }}";
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let msg = "Something went wrong. Please try again.";

                        if (errors) {
                            msg = Object.values(errors).map(error => Array.isArray(error) ? error[0] : error).join("<br>");
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON?.existing_bill) {
                            const existing = xhr.responseJSON.existing_bill;
                            msg = `The B/E No has already been taken by Export Bill #${existing.id} (Invoice: ${existing.invoice_no})`;
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
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@endsection
