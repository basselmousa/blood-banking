@extends('layouts.app')

@section('title', 'Check Donor Eligibility')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 font-weight-bold">
                        <i class="fas fa-heartbeat text-danger"></i> Donor Eligibility Check
                    </h1>
                    <p class="text-muted">{{ $donor->full_name }}</p>
                </div>
                <a href="{{ route('admin.donors') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Donor Info Card -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Donor Information</h6>
                    <div class="mb-3">
                        <small class="text-muted">Full Name</small>
                        <p class="mb-2"><strong>{{ $donor->full_name }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Blood Group</small>
                        <p class="mb-2">
                            <span class="badge badge-danger badge-lg">
                                {{ $donor->blood_group }}+
                            </span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Age</small>
                        <p class="mb-2"><strong>{{ $donor->age }} years</strong></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Email</small>
                        <p class="mb-2"><small>{{ $donor->email }}</small></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Phone</small>
                        <p class="mb-0"><small>{{ $donor->phone_number }}</small></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Eligibility Status -->
        <div class="col-md-9 mb-4">
            <!-- Status Badge -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-2">Eligibility Status</h5>
                            @if ($eligibility['eligible'])
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle"></i> <strong>ELIGIBLE</strong> for blood donation
                                </div>
                            @else
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-times-circle"></i> <strong>NOT ELIGIBLE</strong> - See issues below
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="mb-1">Risk Level</p>
                            @php
                                $riskClass = match($eligibility['risk_level']) {
                                    'low' => 'badge-success',
                                    'medium' => 'badge-warning',
                                    'high' => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                            @endphp
                            <span class="badge {{ $riskClass }} badge-lg">
                                {{ strtoupper($eligibility['risk_level']) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issues -->
            @if (!$eligibility['eligible'])
                <div class="card border-0 shadow-sm mb-4 border-left border-danger">
                    <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle text-danger"></i> Issues Found
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach ($eligibility['issues'] as $issue)
                            <div class="alert alert-warning mb-2">
                                <strong>{{ $issue['reason'] }}:</strong> {{ $issue['message'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Next Eligible Date -->
            @if ($eligibility['next_eligible_date'])
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Next Eligible Date</h6>
                        <h4 class="text-primary">
                            {{ $eligibility['next_eligible_date']->format('F d, Y') }}
                        </h4>
                        @if (!$eligibility['eligible'])
                            <small class="text-muted">
                                Donor can donate again after this date
                            </small>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Health Metrics -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-heartbeat text-danger"></i> Health Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <small class="text-muted">Weight</small>
                            <p class="mb-0">
                                <strong>{{ $donor->weight ?? 'Not recorded' }}</strong> kg
                            </p>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Height</small>
                            <p class="mb-0">
                                <strong>{{ $donor->height ?? 'Not recorded' }}</strong> cm
                            </p>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">BMI</small>
                            <p class="mb-0">
                                @if ($donor->bmi)
                                    <strong>{{ $donor->bmi }}</strong>
                                    @if ($donor->bmi >= 18.5 && $donor->bmi <= 29.9)
                                        <span class="badge badge-success">Normal</span>
                                    @else
                                        <span class="badge badge-warning">Alert</span>
                                    @endif
                                @else
                                    <span class="text-muted">Not calculated</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Hemoglobin</small>
                            <p class="mb-0">
                                @if ($donor->hemoglobin_level)
                                    <strong>{{ $donor->hemoglobin_level }}</strong> g/dL
                                    @if ($donor->hemoglobin_level >= ($donor->gender === 'female' ? 12.5 : 13.5))
                                        <span class="badge badge-success">OK</span>
                                    @else
                                        <span class="badge badge-danger">Low</span>
                                    @endif
                                @else
                                    <span class="text-muted">Not recorded</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Blood Pressure</small>
                            <p class="mb-0">
                                <strong>{{ $donor->blood_pressure ?? 'Not recorded' }}</strong>
                            </p>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">Last Health Check</small>
                            <p class="mb-0">
                                <strong>
                                    @if ($donor->last_health_checkup)
                                        {{ $donor->last_health_checkup->diffForHumans() }}
                                    @else
                                        Not recorded
                                    @endif
                                </strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Assessment -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line text-info"></i> Risk Assessment
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-3">Risk Score: <strong class="text-info">{{ $riskDetails['score'] }}/100</strong></h5>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $riskDetails['score'] }}%; background-color: {{ $riskDetails['score'] < 25 ? '#28a745' : ($riskDetails['score'] < 50 ? '#ffc107' : ($riskDetails['score'] < 75 ? '#fd7e14' : '#dc3545')) }};"
                             aria-valuenow="{{ $riskDetails['score'] }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $riskDetails['score'] }}%
                        </div>
                    </div>

                    <h6 class="mb-2">Identified Risks:</h6>
                    @if (empty($riskDetails['risks']))
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check"></i> No significant risks identified
                        </div>
                    @else
                        @foreach ($riskDetails['risks'] as $risk)
                            <div class="alert alert-warning mb-2">
                                <i class="fas fa-exclamation-circle"></i> {{ $risk }}
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Donation History -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-history text-info"></i> Recent Donation History
                    </h6>
                </div>
                <div class="card-body">
                    @if ($donationHistory->isEmpty())
                        <p class="text-muted text-center py-4">No donation history recorded</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Volume</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Hemoglobin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($donationHistory as $donation)
                                        <tr>
                                            <td>{{ $donation->donation_date->format('M d, Y') }}</td>
                                            <td>{{ $donation->blood_volume }} ml</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ str_replace('_', ' ', ucfirst($donation->type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($donation->status) {
                                                        'completed' => 'badge-success',
                                                        'rejected' => 'badge-danger',
                                                        'deferred' => 'badge-warning',
                                                        'cancelled' => 'badge-secondary',
                                                        default => 'badge-light'
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">
                                                    {{ ucfirst($donation->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $donation->hemoglobin_before ?? 'N/A' }} g/dL</td>
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

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-cog text-secondary"></i> Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if ($eligibility['eligible'])
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.donors.record-donation', $donor->id) }}" class="btn btn-success btn-block">
                                    <i class="fas fa-plus-circle"></i> Record Donation
                                </a>
                            </div>
                        @endif

                        @if (!$donor->is_deferred)
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-warning btn-block" data-toggle="modal" data-target="#deferModal">
                                    <i class="fas fa-ban"></i> Defer Donor
                                </button>
                            </div>
                        @else
                            <div class="col-md-3 mb-2">
                                <form action="{{ route('admin.donors.clear-deferral', $donor->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-block" onclick="return confirm('Clear deferral for this donor?')">
                                        <i class="fas fa-undo"></i> Clear Deferral
                                    </button>
                                </form>
                            </div>
                        @endif

                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.donors') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to Donors
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Defer Modal -->
<div class="modal fade" id="deferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">
                    <i class="fas fa-ban text-warning"></i> Defer Donor
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.donors.defer', $donor->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Deferral Reason</label>
                        <input type="text" class="form-control @error('reason') is-invalid @enderror" 
                               id="reason" name="reason" required placeholder="e.g., Recent tattoo">
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" required></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deferral_type">Deferral Type</label>
                        <select class="form-control @error('deferral_type') is-invalid @enderror" 
                                id="deferral_type" name="deferral_type" required>
                            <option value="">Select type</option>
                            <option value="temporary">Temporary (6 months)</option>
                            <option value="permanent">Permanent</option>
                            <option value="conditional">Conditional</option>
                        </select>
                        @error('deferral_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="eligible_after">Eligible After (Optional)</label>
                        <input type="date" class="form-control @error('eligible_after') is-invalid @enderror" 
                               id="eligible_after" name="eligible_after">
                        @error('eligible_after')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban"></i> Defer Donor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
    }

    .border-left {
        border-left: 4px solid;
    }

    .border-danger {
        border-left-color: #dc3545 !important;
    }
</style>
@endsection
