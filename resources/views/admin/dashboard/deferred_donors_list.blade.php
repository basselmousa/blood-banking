@extends('layouts.app')

@section('title', 'Deferred Donors')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 font-weight-bold">
                        <i class="fas fa-ban text-warning"></i> Deferred Donors
                    </h1>
                    <p class="text-muted">Manage donor deferrals</p>
                </div>
                <a href="{{ route('admin.eligibility.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-warning">{{ $deferrals->count() }}</h4>
                    <small class="text-muted">Active Deferrals</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-danger">{{ $deferrals->where('deferral_type', 'permanent')->count() }}</h4>
                    <small class="text-muted">Permanent</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-info">{{ $deferrals->where('deferral_type', 'temporary')->count() }}</h4>
                    <small class="text-muted">Temporary</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-secondary">{{ $deferrals->where('deferral_type', 'conditional')->count() }}</h4>
                    <small class="text-muted">Conditional</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Deferrals Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Donor Name</th>
                                <th>Blood Group</th>
                                <th>Deferral Reason</th>
                                <th>Type</th>
                                <th>Deferred Date</th>
                                <th>Eligible After</th>
                                <th>Days Remaining</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deferrals as $deferral)
                                <tr>
                                    <td>
                                        <strong>{{ $deferral->donor->full_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $deferral->donor->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ $deferral->donor->blood_group }}+
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $deferral->reason }}</strong>
                                        <br>
                                        <small>{{ $deferral->description }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $typeClass = match($deferral->deferral_type) {
                                                'temporary' => 'badge-info',
                                                'permanent' => 'badge-danger',
                                                'conditional' => 'badge-warning',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeClass }}">
                                            {{ ucfirst($deferral->deferral_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $deferral->deferral_date->format('M d, Y') }}</td>
                                    <td>
                                        @if ($deferral->eligible_after)
                                            @if (now()->isAfter($deferral->eligible_after))
                                                <span class="text-success">
                                                    <strong>Eligible Now!</strong>
                                                </span>
                                            @else
                                                {{ $deferral->eligible_after->format('M d, Y') }}
                                            @endif
                                        @else
                                            <span class="text-muted">No expiration</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($deferral->eligible_after)
                                            @php $daysRemaining = now()->diffInDays($deferral->eligible_after, false) @endphp
                                            @if ($daysRemaining > 0)
                                                <span class="badge badge-warning">{{ $daysRemaining }} days</span>
                                            @else
                                                <span class="badge badge-success">Ready</span>
                                            @endif
                                        @else
                                            <span class="badge badge-danger">Permanent</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.donors.eligibility', $deferral->donor->id) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($deferral->deferral_type === 'temporary' || $deferral->eligible_after)
                                            <form action="{{ route('admin.donors.clear-deferral', $deferral->donor->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Clear deferral?')" title="Clear Deferral">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                        <p class="mt-2 text-muted">No deferred donors</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($deferrals->hasPages())
                <div class="mt-3">
                    {{ $deferrals->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .badge {
        padding: 0.4rem 0.6rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endsection
