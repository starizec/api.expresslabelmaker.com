@extends('layouts.app')

@section('title', 'My Profile - ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" 
                         alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
                    <h5 class="my-3">{{ $user->name }}</h5>
                    <p class="text-muted mb-1">{{ $user->email }}</p>
                    <p class="text-muted mb-4">Member since {{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="col-lg-9">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Licenses</h5>
                </div>
                <div class="card-body">
                    @if($licenses->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-key text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2">You don't have any licenses yet.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>License Key</th>
                                        <th>Status</th>
                                        <th>Usage</th>
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($licenses as $license)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="text-monospace">{{ Str::limit($license->key, 16) }}</span>
                                                <button class="btn btn-sm btn-link" 
                                                        onclick="navigator.clipboard.writeText('{{ $license->key }}')"
                                                        title="Copy license key">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            @if($license->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $license->used_count }} / {{ $license->max_usage ?? '∞' }}
                                        </td>
                                        <td>
                                            @if($license->expires_at)
                                                {{ $license->expires_at->format('d M Y') }}
                                                @if($license->expires_at->isPast())
                                                    <span class="badge bg-danger">Expired</span>
                                                @elseif($license->expires_at->diffInDays() < 30)
                                                    <span class="badge bg-warning">Expiring soon</span>
                                                @endif
                                            @else
                                                Never
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-primary"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#licenseDetails{{ $license->id }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="{{ route('licenses.renew', $license->id) }}" 
                                                   class="btn btn-outline-success">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </a>
                                            </div>
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
                    <h5 class="mb-0">Usage Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Total Labels Created</h6>
                                <h2 class="text-primary">{{ $licenses->sum('used_count') }}</h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Active Licenses</h6>
                                <h2 class="text-success">{{ $licenses->where('is_active', true)->count() }}</h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6>Expiring Soon</h6>
                                <h2 class="text-warning">
                                    {{ $licenses->where('expires_at', '>', now())
                                              ->where('expires_at', '<', now()->addDays(30))
                                              ->count() }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- License Detail Modals -->
@foreach($licenses as $license)
<div class="modal fade" id="licenseDetails{{ $license->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">License Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">License Key</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $license->key }}" readonly>
                        <button class="btn btn-outline-secondary" 
                                onclick="navigator.clipboard.writeText('{{ $license->key }}')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Created On</label>
                    <p>{{ $license->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Last Used</label>
                    <p>{{ $license->last_used_at ? $license->last_used_at->format('d M Y H:i') : 'Never' }}</p>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Usage History</label>
                    <div class="progress">
                        @php
                            $percentage = $license->max_usage 
                                ? ($license->used_count / $license->max_usage) * 100 
                                : 0;
                        @endphp
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: {{ $percentage }}%"
                             aria-valuenow="{{ $percentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $license->used_count }} / {{ $license->max_usage ?? '∞' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                @if(!$license->is_active)
                    <a href="{{ route('licenses.renew', $license->id) }}" 
                       class="btn btn-primary">Renew License</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection 