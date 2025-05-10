@extends('layouts.app')

@section('title', $licence->domain->name)

@section('content')
    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.personal_and_company_info') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update', ['lang' => app()->getLocale()]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="licence_id" value="{{ $licence->id }}">

                            <!-- Personal Information -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="col-md-12 mb-3">
                                        <label for="first_name" class="form-label">{{ __('messages.first_name') }} <span
                                                class="text-danger"></span></label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('first_name') is-invalid @enderror"
                                            id="first_name" name="first_name"
                                            value="{{ old('first_name', $licence->user->first_name ?? '') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="last_name" class="form-label">{{ __('messages.last_name') }} <span
                                                class="text-danger"></span></label>
                                        <input type="text"
                                            class="form-control form-control-sm  @error('last_name') is-invalid @enderror"
                                            id="last_name" name="last_name"
                                            value="{{ old('last_name', $licence->user->last_name ?? '') }}">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="email" class="form-label">{{ __('messages.email') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="email"
                                            class="form-control form-control-sm @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $licence->user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Company Information -->
                                <div class="col-md-12">
                                    <div class="col-md-12 mb-3">
                                        <label for="company_name"
                                            class="form-label">{{ __('messages.company_name') }}</label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('company_name') is-invalid @enderror"
                                            id="company_name" name="company_name"
                                            value="{{ old('company_name', $licence->user->company_name ?? '') }}">
                                        @error('company_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="company_address"
                                            class="form-label">{{ __('messages.company_address') }}</label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('company_address') is-invalid @enderror"
                                            id="company_address" name="company_address"
                                            value="{{ old('company_address', $licence->user->company_address ?? '') }}">
                                        @error('company_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="town" class="form-label">{{ __('messages.town') }}</label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('town') is-invalid @enderror"
                                            id="town" name="town" value="{{ old('town', $licence->user->town ?? '') }}">
                                        @error('town')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3 col-md-12">
                                        <label for="country" class="form-label">{{ __('messages.country') }}</label>
                                        <select class="form-control form-control-sm" name="country" id="country">
                                            <option value="">{{ __('messages.select_country') }}</option>
                                            @php
                                                $countries = [
                                                    'HR' => 'Hrvatska',
                                                    'SI' => 'Slovenija',
                                                    'BA' => 'Bosna i Hercegovina',
                                                    'RS' => 'Srbija',
                                                    'ME' => 'Crna Gora',
                                                    'MK' => 'Sjeverna Makedonija',
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

                                    <div class="col-md-12 mb-3">
                                        <label for="vat_number" class="form-label">{{ __('messages.vat_number') }}</label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('vat_number') is-invalid @enderror"
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
                        <h5 class="mb-0">{{ __('messages.licence_details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">{{ __('messages.licence_uid') }}</h6>
                            <p class="mb-0">{{ $licence->licence_uid }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">{{ __('messages.domain_name') }}</h6>
                            <p class="mb-0">{{ $licence->domain->name }}</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted mb-2">{{ __('messages.valid_until') }}</h6>
                            <p class="mb-0">{{ $valid_until }}</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted mb-2">{{ __('messages.payment_cost') }}</h6>
                            <p class="mb-0">{{ number_format($licence->price, 2) }} €</p>
                        </div>

                        <div class="d-grid">
                            <button type="button" class="btn btn-primary btn-block" id="payment-button">
                                {{ __('messages.proceed_to_payment') }}
                            </button>
                            <button type="button" class="btn btn-secondary btn-block" id="offer-button">
                                {{ __('messages.ponuda') }}
                            </button>
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
                const response = await fetch('{{ route("payment.create-session", ["lang" => app()->getLocale(), "licence_uid" => $licence->licence_uid]) }}', {
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
                
                const stripe = Stripe('{{ config("services.stripe.key") }}');
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
