@extends('layouts.app')

@section('title', 'Record Donation')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 font-weight-bold">
                <i class="fas fa-plus-circle text-success"></i> Record Donation
            </h1>
            <p class="text-muted">{{ $donor->full_name }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Donor Info Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Donor Summary</h6>
                    <div class="mb-3">
                        <small class="text-muted">Blood Group</small>
                        <p class="mb-2">
                            <span class="badge badge-danger badge-lg">
                                {{ $donor->blood_group }}+
                            </span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Age / Weight</small>
                        <p class="mb-2">
                            <strong>{{ $donor->age }}</strong> yrs / 
                            <strong>{{ $donor->weight ?? 'N/A' }}</strong> kg
                        </p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Hemoglobin</small>
                        <p class="mb-0">
                            <strong>{{ $donor->hemoglobin_level ?? 'N/A' }}</strong> g/dL
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="col-md-9 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Donation Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.donors.record-donation', $donor->id) }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Donation Date -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="donation_date">
                                    <i class="fas fa-calendar"></i> Donation Date
                                </label>
                                <input type="datetime-local" class="form-control @error('donation_date') is-invalid @enderror" 
                                       id="donation_date" name="donation_date" value="{{ old('donation_date', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('donation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Donation Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="type">
                                    <i class="fas fa-vial"></i> Donation Type
                                </label>
                                <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select type</option>
                                    <option value="whole_blood" {{ old('type') === 'whole_blood' ? 'selected' : '' }}>
                                        Whole Blood
                                    </option>
                                    <option value="plasma" {{ old('type') === 'plasma' ? 'selected' : '' }}>
                                        Plasma
                                    </option>
                                    <option value="platelets" {{ old('type') === 'platelets' ? 'selected' : '' }}>
                                        Platelets
                                    </option>
                                    <option value="red_cells" {{ old('type') === 'red_cells' ? 'selected' : '' }}>
                                        Red Cells
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Blood Volume -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="blood_volume">
                                    <i class="fas fa-droplet"></i> Blood Volume (ml)
                                </label>
                                <input type="number" class="form-control @error('blood_volume') is-invalid @enderror" 
                                       id="blood_volume" name="blood_volume" value="{{ old('blood_volume', 450) }}" 
                                       min="100" max="500" step="10">
                                <small class="text-muted">Standard: 450 ml</small>
                                @error('blood_volume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="status">
                                    <i class="fas fa-check"></i> Donation Status
                                </label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required onchange="toggleRejectionReason()">
                                    <option value="">Select status</option>
                                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>
                                    <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>
                                        Rejected
                                    </option>
                                    <option value="deferred" {{ old('status') === 'deferred' ? 'selected' : '' }}>
                                        Deferred
                                    </option>
                                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Rejection Reason (Hidden by default) -->
                        <div class="row" id="rejectionReasonDiv" style="display: none;">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="rejection_reason">
                                    <i class="fas fa-times-circle"></i> Rejection Reason
                                </label>
                                <textarea class="form-control @error('rejection_reason') is-invalid @enderror" 
                                          id="rejection_reason" name="rejection_reason" rows="3" 
                                          placeholder="Explain why the donation was rejected"></textarea>
                                @error('rejection_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Hemoglobin Before -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="hemoglobin_before">
                                    <i class="fas fa-heartbeat"></i> Hemoglobin Before (g/dL)
                                </label>
                                <input type="number" class="form-control @error('hemoglobin_before') is-invalid @enderror" 
                                       id="hemoglobin_before" name="hemoglobin_before" value="{{ old('hemoglobin_before', $donor->hemoglobin_level) }}" 
                                       step="0.1" min="8" max="20">
                                @error('hemoglobin_before')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="notes">
                                    <i class="fas fa-sticky-note"></i> Additional Notes
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" placeholder="Any additional information"></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                        <i class="fas fa-save"></i> Record Donation
                                    </button>
                                    <a href="{{ route('admin.donors.eligibility', $donor->id) }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRejectionReason() {
        const status = document.getElementById('status').value;
        const rejectionDiv = document.getElementById('rejectionReasonDiv');
        
        if (status === 'rejected') {
            rejectionDiv.style.display = 'flex';
        } else {
            rejectionDiv.style.display = 'none';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', toggleRejectionReason);
</script>

<style>
    .sticky-top {
        z-index: 100;
    }

    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .gap-2 {
        gap: 0.5rem;
    }
</style>
@endsection
