@extends('layouts.app')

@section('title', 'Eligible Donors')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 font-weight-bold">
                        <i class="fas fa-check-circle text-success"></i> Eligible Donors
                    </h1>
                    <p class="text-muted">Donors available for blood donation</p>
                </div>
                <a href="{{ route('admin.eligibility.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.donors.eligible-list') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-control" required>
                                <option value="">Select Blood Group</option>
                                <option value="O+" {{ request('blood_group') === 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ request('blood_group') === 'O-' ? 'selected' : '' }}>O-</option>
                                <option value="A+" {{ request('blood_group') === 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ request('blood_group') === 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ request('blood_group') === 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ request('blood_group') === 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ request('blood_group') === 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ request('blood_group') === 'AB-' ? 'selected' : '' }}>AB-</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <select name="city" class="form-control">
                                <option value="">All Cities</option>
                                @foreach ($cities ?? [] as $city)
                                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>
                                        {{ $city }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Summary -->
    @if (!empty($eligibleDonors))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> Found <strong>{{ count($eligibleDonors) }}</strong> eligible donors
                </div>
            </div>
        </div>

        <!-- Donors Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Blood Group</th>
                                    <th>Age</th>
                                    <th>City</th>
                                    <th>Last Donation</th>
                                    <th>Risk Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($eligibleDonors as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item['donor']->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $item['donor']->email }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger badge-lg">
                                                {{ $item['donor']->blood_group }}+
                                            </span>
                                        </td>
                                        <td>{{ $item['donor']->age }} yrs</td>
                                        <td>{{ $item['donor']->city }}</td>
                                        <td>
                                            @if ($item['donor']->last_donation_date)
                                                {{ $item['donor']->last_donation_date->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $riskClass = match($item['risk_level']) {
                                                    'low' => 'badge-success',
                                                    'medium' => 'badge-warning',
                                                    'high' => 'badge-danger',
                                                    default => 'badge-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $riskClass }}">
                                                {{ strtoupper($item['risk_level']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> Eligible
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.donors.eligibility', $item['donor']->id) }}" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="tel:{{ $item['donor']->phone_number }}" 
                                               class="btn btn-sm btn-success" title="Call">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> No eligible donors found. Please adjust your filters.
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
    }

    .table-responsive {
        border-radius: 0.25rem;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endsection
