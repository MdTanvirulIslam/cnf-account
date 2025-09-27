@extends('layouts.layout')

@section('styles')
    <link href="{{ asset('assets/src/assets/css/light/users/account-setting.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/src/assets/css/dark/users/account-setting.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
@endsection

@section('content')
    <div class="row layout-spacing">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
            <div class="card">
                <div class="card-body">
                    <div class="account-content">
                        <div class="row mb-3">
                            <ul class="nav nav-pills" id="animateLine" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="animated-underline-home-tab"
                                            data-bs-toggle="tab" href="#animated-underline-home" role="tab"
                                            aria-controls="animated-underline-home" aria-selected="true">
                                        <!-- Profile icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" role="img" aria-label="Profile settings icon"
                                             fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <title>Profile settings</title>
                                            <circle cx="11" cy="8" r="3.2"/>
                                            <path d="M4.5 19c.9-3 3.6-5 6.5-5s5.6 2 6.5 5"/>
                                            <g transform="translate(17,7) scale(0.9)">
                                                <circle cx="5" cy="5" r="1.6"/>
                                                <path d="M5 0v1.6M5 8.4V10M0 5h1.6M8.4 5H10M1.2 1.2l1.1 1.1M7.7 7.7l1.1 1.1M7.7 1.2l1.1 1.1M1.2 7.7l1.1 1.1"
                                                      stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
                                            </g>
                                        </svg>
                                        Profile Settings
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="animated-underline-profile-tab"
                                            data-bs-toggle="tab" href="#animated-underline-profile" role="tab"
                                            aria-controls="animated-underline-profile" aria-selected="false"
                                            tabindex="-1">
                                        <!-- Change Password icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                             role="img" aria-label="Change password">
                                            <title>Change Password</title>
                                            <rect x="4" y="10" width="16" height="10" rx="2" ry="2"/>
                                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                            <path d="M12 14a3 3 0 1 0 3 3h-1.5l2 2 2-2H16"/>
                                        </svg>
                                        Change Password
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-content" id="animateLineContent-4">
                        <!-- ===== PROFILE TAB ===== -->
                        <div class="tab-pane fade show active" id="animated-underline-home" role="tabpanel"
                             aria-labelledby="animated-underline-home-tab">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12 layout-spacing">
                                    <!-- IMPORTANT: add id, action and enctype -->
                                    <form id="profileForm" class="section general-info" method="post"
                                          action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
                                        @csrf
                                        @method('patch')

                                        <div class="info">
                                            <h6 class="">General Information</h6>
                                            <div class="row">
                                                <div class="col-lg-11 mx-auto">
                                                    <div class="row">

                                                        <div class="col-xl-10 col-lg-12 col-md-8 mt-md-0 mt-4">
                                                            <div class="form">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="fullName">Full Name</label>
                                                                            <input type="text"
                                                                                   name="name"
                                                                                   class="form-control mb-3"
                                                                                   id="fullName"
                                                                                   placeholder="Full Name"
                                                                                   value="{{ old('name', auth()->user()->name ?? 'Jimmy Turner') }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="address">Address</label>
                                                                            <input type="text"
                                                                                   name="address"
                                                                                   class="form-control mb-3"
                                                                                   id="address"
                                                                                   placeholder="Address"
                                                                                   value="{{ old('address', auth()->user()->address ?? 'New York') }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="phone">Phone</label>
                                                                            <input type="text"
                                                                                   name="phone"
                                                                                   class="form-control mb-3"
                                                                                   id="phone"
                                                                                   placeholder="Write your phone number here"
                                                                                   value="{{ old('phone', auth()->user()->phone ?? '+1 (530) 555-12121') }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="email">Email</label>
                                                                            <input type="email"
                                                                                   name="email"
                                                                                   class="form-control mb-3"
                                                                                   id="email"
                                                                                   placeholder="Write your email here"
                                                                                   value="{{ old('email', auth()->user()->email ?? 'Jimmy@gmail.com') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="formFile">Profile Picture</label>

                                                                            {{-- File input --}}
                                                                            <input class="form-control file-upload-input" type="file" id="formFile" name="profile_picture">

                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="formFile">Profile Picture</label>

                                                                            {{-- Show current picture if exists --}}
                                                                            @if(auth()->user()->profile_picture)
                                                                                <div class="mt-2">
                                                                                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                                                                                         alt="Profile Picture"
                                                                                         class="img-thumbnail"
                                                                                         style="max-width: 220px; max-height: 220px;">
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-12 mt-1">
                                                                        <div class="form-group text-end">
                                                                            <button type="submit" class="btn btn-secondary" id="profileSubmitBtn">Save</button>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form> <!-- /#profileForm -->
                                </div>
                            </div>
                        </div>

                        <!-- ===== PASSWORD TAB ===== -->
                        <div class="tab-pane fade" id="animated-underline-profile" role="tabpanel"
                             aria-labelledby="animated-underline-profile-tab">
                            <div class="row">
                                <div class="col-xl-8 col-lg-8 col-md-8 layout-spacing">
                                    <!-- Password form keeps your blade components; ID added -->
                                    <form id="passwordForm" class="section general-info" method="post"
                                          action="{{ route('password.update') }}" novalidate>
                                        @csrf
                                        @method('put')

                                        <div class="info">
                                            <h6 class="">Change Current Password</h6>
                                            <div class="row">
                                                <div class="mb-3">
                                                    <x-input-label class="form-label"
                                                                   for="update_password_current_password"
                                                                   :value="__('Current Password')"/>
                                                    <x-text-input id="update_password_current_password"
                                                                  name="current_password" type="password"
                                                                  class="form-control add-billing-address-input"
                                                                  autocomplete="current-password"/>
                                                    <x-input-error
                                                        :messages="$errors->updatePassword->get('current_password')"
                                                        class="mt-2"/>
                                                </div>

                                                <div class="mb-3">
                                                    <x-input-label class="form-label" for="update_password_password"
                                                                   :value="__('New Password')"/>
                                                    <x-text-input id="update_password_password" name="password"
                                                                  type="password"
                                                                  class="form-control add-billing-address-input"
                                                                  autocomplete="new-password"/>
                                                    <x-input-error
                                                        :messages="$errors->updatePassword->get('password')"
                                                        class="mt-2"/>
                                                </div>

                                                <div class="mb-3">
                                                    <x-input-label class="form-label"
                                                                   for="update_password_password_confirmation"
                                                                   :value="__('Confirm Password')"/>
                                                    <x-text-input id="update_password_password_confirmation"
                                                                  name="password_confirmation" type="password"
                                                                  class="form-control add-billing-address-input"
                                                                  autocomplete="new-password"/>
                                                    <x-input-error
                                                        :messages="$errors->updatePassword->get('password_confirmation')"
                                                        class="mt-2"/>
                                                </div>

                                                <div class="flex items-center mb-3 text-end">
                                                    <button type="submit" id="passwordSubmitBtn" class="btn btn-secondary _effect--ripple waves-effect waves-light">{{ __('Save') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form> <!-- /#passwordForm -->
                                </div>
                            </div>
                        </div>

                    </div> <!-- /.tab-content -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/assets/js/users/account-settings.js') }}"></script>
    <script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper to get CSRF token: from meta tag or hidden input
            function getCsrfToken() {
                let meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) return meta.getAttribute('content');
                let tokenInput = document.querySelector('input[name="_token"]');
                if (tokenInput) return tokenInput.value;
                return '';
            }

            function showSuccess(message = 'Updated successfully') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    showConfirmButton: false,
                    timer: 2000
                });
            }

            function showError(title = 'Error', html = '') {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    html: html
                });
            }

            function handleFormAjax(formId, submitBtnId) {
                const form = document.getElementById(formId);
                if (!form) return;

                form.addEventListener('submit', async function (e) {
                    e.preventDefault(); // <- very important
                    const submitBtn = document.getElementById(submitBtnId) || form.querySelector('button[type="submit"]');

                    try {
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            // optionally show spinner in button
                            submitBtn.dataset.originalText = submitBtn.innerHTML;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                        }

                        const url = form.getAttribute('action') || window.location.href;

                        const formData = new FormData(form);

                        const response = await fetch(url, {
                            method: 'POST', // use POST so Laravel receives _method input if present
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': getCsrfToken()
                                // DON'T set Content-Type when sending FormData
                            },
                            body: formData,
                            credentials: 'same-origin'
                        });

                        // Try parse JSON if server returns application/json
                        const contentType = response.headers.get('content-type') || '';
                        let data = null;
                        if (contentType.indexOf('application/json') !== -1) {
                            data = await response.json();
                        } else {
                            // if not json, try to parse as json string or read text
                            const text = await response.text();
                            try { data = JSON.parse(text); } catch (err) { data = { html: text }; }
                        }

                        if (response.ok) {
                            // If server returned message in JSON
                            if (data && data.message) {
                                showSuccess(data.message);
                            } else {
                                showSuccess();
                            }

                            // Optional: reset password fields after success
                            if (formId === 'passwordForm') {
                                form.reset();
                            }

                        } else {
                            // For validation errors Laravel returns 422 with errors object
                            if (response.status === 422 && data && data.errors) {
                                const errors = [];
                                Object.keys(data.errors).forEach(key => {
                                    errors.push(data.errors[key].join('<br>'));
                                });
                                showError('Validation error', errors.join('<br>'));
                            } else if (data && data.message) {
                                showError('Error', data.message);
                            } else if (data && data.html) {
                                // fallback: server returned HTML (maybe redirect); show generic message
                                showError('Error', 'Server returned an unexpected response.');
                            } else {
                                showError('Error', 'Something went wrong.');
                            }
                        }
                    } catch (err) {
                        showError('Request failed', err.message || 'Network error');
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            if (submitBtn.dataset.originalText) submitBtn.innerHTML = submitBtn.dataset.originalText;
                        }
                    }
                });
            }

            // Attach to forms
            handleFormAjax('profileForm', 'profileSubmitBtn');
            handleFormAjax('passwordForm', 'passwordSubmitBtn');

        });
    </script>
@endsection
