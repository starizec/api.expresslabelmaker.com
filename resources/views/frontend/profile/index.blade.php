@extends('layouts.app')

@section('title', __('messages.profile') . ' - ' . config('app.name'))

@section('content')
    <div class="container py-5">
        <div class="row">
            <!-- Profile Sidebar -->
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" alt="{{ __('messages.avatar') }}"
                            class="rounded-circle img-fluid" style="width: 150px;">
                        <h5 class="my-3">{{ $user->name }}</h5>
                        <p class="text-muted mb-1">{{ $user->email }}</p>
                        <p class="text-muted mb-4">{{ __('messages.member_since') }} {{ $user->created_at->format('M Y') }}
                        </p>
                    </div>
                </div>
            </div>

            

            <!-- License Information -->
            <div class="col-lg-9">
                <div class="card mb-4">
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
                                            <th>{{ __('messages.license_key') }}</th>
                                            <th>{{ __('messages.status') }}</th>
                                            <th>{{ __('messages.usage') }}</th>
                                            <th>{{ __('messages.valid_until') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($licences as $licence)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="text-monospace">{{ $licence->licence_uid }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($licence->is_active)
                                                        <span class="badge bg-success">{{ __('messages.active') }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $licence->usage }} / {{ $licence->usage_limit ?? '∞' }}
                                                </td>
                                                <td>
                                                    @if ($licence->valid_until)
                                                        @php
                                                            $validUntilDate = is_string($licence->valid_until) ? \Carbon\Carbon::parse($licence->valid_until) : $licence->valid_until;
                                                            $now = \Carbon\Carbon::now();
                                                            $isExpired = $validUntilDate->isPast();
                                                            $isExpiringSoon = !$isExpired && $validUntilDate->diffInDays($now) < 30;
                                                        @endphp
                                                        {{ $validUntilDate->format('d M Y') }}
                                                        @if ($isExpired)
                                                            <span class="badge bg-danger">{{ __('messages.expired') }}</span>
                                                        @elseif($isExpiringSoon)
                                                            <span class="badge bg-warning">{{ __('messages.expiring_soon') }}</span>
                                                        @endif
                                                    @else
                                                        {{ __('messages.never') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Usage Statistics Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.usage_statistics') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6>{{ __('messages.total_labels_created') }}</h6>
                                    <h2 class="text-primary">{{ $licences->sum('usage') }}</h2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6>{{ __('messages.active_licenses') }}</h6>
                                    <h2 class="text-success">{{ $licences->where('is_active', true)->count() }}</h2>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6>{{ __('messages.expiring_soon') }}</h6>
                                    <h2 class="text-warning">
                                        @php
                                            $expiringCount = 0;
                                            $now = \Carbon\Carbon::now();
                                            foreach($licences as $licence) {
                                                if ($licence->valid_until) {
                                                    $validUntil = is_string($licence->valid_until) ? \Carbon\Carbon::parse($licence->valid_until) : $licence->valid_until;
                                                    if ($validUntil->gt($now) && $validUntil->diffInDays($now) < 30) {
                                                        $expiringCount++;
                                                    }
                                                }
                                            }
                                        @endphp
                                        {{ $expiringCount }}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
