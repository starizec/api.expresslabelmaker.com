@extends('layouts.app')

@section('title', $licence->domain->name)

@section('content')
    @if (session('success'))
        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">{{ __('messages.request_offer_success') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('messages.request_offer_success') }}</p>
                        <p>{{ __('messages.request_offer_success_message', ['email' => $licence->user->email]) }}</p>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('profile', ['lang' => app()->getLocale()]) }}" class="btn btn-secondary">{{ __('messages.go_to_profile') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div style="background: linear-gradient(to right, #045cb8, #047adb)" class="pt-5 pb-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('payment.personal_and_company_info') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('payment.submit-offer', ['lang' => app()->getLocale()]) }}"
                                method="POST">
                                @csrf
                                @method('POST')
                                <input type="hidden" name="licence_id" value="{{ $licence->id }}">

                                <!-- Personal Information -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="first_name">{{ __('messages.first_name') }}</label>
                                            <input type="text"
                                                class="form-control @error('first_name') is-invalid @enderror"
                                                id="first_name" name="first_name"
                                                value="{{ old('first_name', $licence->user->first_name ?? '') }}" required>
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="last_name">{{ __('messages.last_name') }}</label>
                                            <input type="text"
                                                class="form-control @error('last_name') is-invalid @enderror" id="last_name"
                                                name="last_name"
                                                value="{{ old('last_name', $licence->user->last_name ?? '') }}" required>
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="email">{{ __('messages.email') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                id="email" name="email"
                                                value="{{ old('email', $licence->user->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Company Information -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="company_name">{{ __('messages.company_name') }}</label>
                                            <input type="text"
                                                class="form-control @error('company_name') is-invalid @enderror"
                                                id="company_name" name="company_name"
                                                value="{{ old('company_name', $licence->user->company_name ?? '') }}">
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="company_address">{{ __('messages.company_address') }}</label>
                                            <input type="text"
                                                class="form-control @error('company_address') is-invalid @enderror"
                                                id="company_address" name="company_address"
                                                value="{{ old('company_address', $licence->user->company_address ?? '') }}">
                                            @error('company_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="town">{{ __('messages.town') }}</label>
                                            <input type="text" class="form-control @error('town') is-invalid @enderror"
                                                id="town" name="town"
                                                value="{{ old('town', $licence->user->town ?? '') }}">
                                            @error('town')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="country">{{ __('messages.country') }}</label>
                                            <select class="form-control @error('country') is-invalid @enderror"
                                                name="country" id="country">
                                                <option value="">{{ __('messages.select_country') }}</option>
                                                @php
                                                    $countries = [
                                                        'HR' => 'Hrvatska',
                                                    ];
                                                @endphp
                                                @foreach ($countries as $code => $name)
                                                    <option value="{{ $code }}"
                                                        {{ old('country', $licence->user->country ?? '') == $code ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="vat_number">{{ __('messages.vat_number') }}</label>
                                            <input type="text"
                                                class="form-control @error('vat_number') is-invalid @enderror"
                                                id="vat_number" name="vat_number"
                                                value="{{ old('vat_number', $licence->user->vat_number ?? '') }}">
                                            @error('vat_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('payment.order_review') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="mb-2">{{ __('payment.domain_name') }}</h6>
                                <p class="font-weight-bold">{{ $licence->domain->name }}</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="mb-2">{{ __('payment.licence_uid') }}</h6>
                                <p class="font-weight-bold">{{ $licence->licence_uid }}</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="mb-2">{{ __('payment.valid_until') }}</h6>
                                <p class="font-weight-bold">{{ \Carbon\Carbon::parse($valid_until)->format('d.m.Y') }}</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="mb-2">{{ __('payment.payment_cost') }}</h6>
                                <p class="font-weight-bold">{{ number_format($price, 2) }} €</p>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-lg btn-success btn-block shadow"
                                    style="
                                    box-shadow: 1px 1px 21px -1px rgba(0,0,0,0.39);
                                    -webkit-box-shadow: 1px 1px 21px -1px rgba(0,0,0,0.39);
                                    -moz-box-shadow: 1px 1px 21px -1px rgba(0,0,0,0.39);
                                    ">
                                    {{ __('payment.request_offer') }} <i class="bi bi-forward"></i>
                                </button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            });
        </script>
    @endif
