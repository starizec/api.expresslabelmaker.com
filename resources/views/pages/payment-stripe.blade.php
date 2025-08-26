@extends('layouts.app')

@section('title', $licence->domain->name)

@section('content')
    <div style="background: linear-gradient(to right, #045cb8, #047adb)" class="pt-5 pb-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('payment.personal_and_company_info') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.update', ['lang' => app()->getLocale()]) }}" method="POST">
                                @csrf
                                @method('PUT')
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
                                                value="{{ old('last_name', $licence->user->last_name ?? '') }}">
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
                            </form>
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
                                <p class="font-weight-bold">{{ number_format($licence->price, 2) }} €</p>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="button" class="btn btn-lg btn-success btn-block shadow"
                                    id="payment-button"
                                    style="
                                    box-shadow: 1px 1px 21px -1px rgba(0,0,0,0.39);
                                    -webkit-box-shadow: 1px 1px 21px -1px rgba(0,0,0,0.39);
                                    -moz-box-shadow: 1px 1px 21px -1px rgba(0,0,0,0.39);
                                    ">
                                    <i class="bi bi-credit-card-fill"></i> {{ __('payment.proceed_to_payment') }}
                                </button>
                            </div>
                            <div>
                                <img src="{{ asset('assets/images/stripe-badge-transparent.png') }}" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script src="https://js.stripe.com/v3/"></script>
            <script>
                document.getElementById('payment-button').addEventListener('click', async function() {
                    try {
                        const response = await fetch(
                            '{{ route('payment.create-session', ['lang' => app()->getLocale(), 'licence_uid' => $licence->licence_uid]) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
                        }

                        const session = await response.json();

                        if (!session.id) {
                            throw new Error('No session ID received from server');
                        }

                        const stripe = Stripe('{{ config('services.stripe.key') }}');
                        const result = await stripe.redirectToCheckout({
                            sessionId: session.id
                        });

                        if (result.error) {
                            alert(result.error.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while processing your payment: ' + error.message);
                    }
                });
            </script>
        @endpush
    @endsection
