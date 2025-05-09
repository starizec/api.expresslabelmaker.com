@extends('layouts.app')

@section('title', __('messages.profile') . ' - ' . config('app.name'))

@section('content')
    <div class="container py-5 mb-5">
        <div class="row">
            <!-- Profile Information Form -->
            <div class="col-lg-12">
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
                                <div class="col-md-6">
                                    <div class="col-md-12 mb-3">
                                        <label for="first_name" class="form-label">{{ __('messages.first_name') }} <span
                                                class="text-danger"></span></label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('first_name') is-invalid @enderror"
                                            id="first_name" name="first_name"
                                            value="{{ old('first_name', $user->first_name ?? '') }}" required>
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
                                            value="{{ old('last_name', $user->last_name ?? '') }}">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="email" class="form-label">{{ __('messages.email') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="email"
                                            class="form-control form-control-sm @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Company Information -->
                                <div class="col-md-6">
                                    <div class="col-md-12 mb-3">
                                        <label for="company_name"
                                            class="form-label">{{ __('messages.company_name') }}</label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('company_name') is-invalid @enderror"
                                            id="company_name" name="company_name"
                                            value="{{ old('company_name', $user->company_name ?? '') }}">
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
                                            value="{{ old('company_address', $user->company_address ?? '') }}">
                                        @error('company_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="town" class="form-label">{{ __('messages.town') }}</label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('town') is-invalid @enderror"
                                            id="town" name="town" value="{{ old('town', $user->town ?? '') }}">
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
                                                    {{ old('country', $user->country ?? '') == $code ? 'selected' : '' }}>
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
                                            value="{{ old('vat_number', $user->vat_number ?? '') }}">
                                        @error('vat_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="text-end float-right mt-5">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('messages.save_changes') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('messages.my_licenses') }}</h5>
                    </div>
                    <div class="card-body">
                        @if ($licences->isEmpty())
                            <div class="text-center py-4">
                                <i class="bi bi-key text-muted" style="font-size: 2rem;"></i>
                                <p class="mt-2">{{ __('messages.no_licenses') }}</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.domain') }}</th>
                                            <th>{{ __('messages.license_key') }}</th>
                                            <th>{{ __('messages.status') }}</th>
                                            <th>{{ __('messages.usage') }}</th>
                                            <th>{{ __('messages.valid_from') }}</th>
                                            <th>{{ __('messages.valid_until') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($licences as $licence)
                                            <tr>
                                                <td>
                                                    {{ $licence->domain->name }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-monospace">{{ $licence->licence_uid }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $today = \Carbon\Carbon::now();
                                                        $validFromDate = is_string($licence->valid_from)
                                                            ? \Carbon\Carbon::parse($licence->valid_from)
                                                            : $licence->valid_from;
                                                        $validUntilDate = is_string($licence->valid_until)
                                                            ? \Carbon\Carbon::parse($licence->valid_until)
                                                            : $licence->valid_until;

                                                        if ($today->between($validFromDate, $validUntilDate)) {
                                                            $status = 'active';
                                                            $badgeClass = 'bg-success';
                                                        } elseif ($today->gt($validUntilDate)) {
                                                            $status = 'inactive';
                                                            $badgeClass = 'bg-danger';
                                                        } else {
                                                            $status = 'pending';
                                                            $badgeClass = 'bg-warning';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="badge {{ $badgeClass }}">{{ __('messages.' . $status) }}</span>
                                                </td>
                                                <td>
                                                    {{ $licence->usage }} / {{ $licence->usage_limit ?? '∞' }}
                                                </td>
                                                <td>
                                                    @php
                                                        $validFromDate = is_string($licence->valid_from)
                                                            ? \Carbon\Carbon::parse($licence->valid_from)
                                                            : $licence->valid_from;
                                                    @endphp
                                                    {{ $validFromDate->format('d.m.Y') }}
                                                </td>
                                                <td>
                                                    @php
                                                        $validUntilDate = is_string($licence->valid_until)
                                                            ? \Carbon\Carbon::parse($licence->valid_until)
                                                            : $licence->valid_until;
                                                    @endphp
                                                    {{ $validUntilDate->format('d.m.Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
