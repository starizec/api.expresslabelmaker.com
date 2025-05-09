@extends('layouts.app')

@section('title', $licence->domain->name)

@section('content')
    <div class="container">
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
        </div>
    </div>
@endsection
