@extends('layouts.app')

@section('title', 'Donation Eligibility Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 font-weight-bold text-gradient">
                        <i class="fas fa-heartbeat text-danger"></i> Donation Eligibility Dashboard
                    </h1>
                    <p class="text-muted">Manage and monitor donor eligibility and deferrals</p>
                </div>
                <a href="{{ route('admin.donors.eligible-list') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-users"></i> View Eligible Donors
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Donors -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm hover-shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Total Donors</h6>
                            <h2 class="font-weight-bold text-dark">{{ $totalDonors }}</h2>
                        </div>
                        <div class="badge badge-primary rounded-circle p-3">
                            <i class="fas fa-user-friends fa-lg"></i>
                        </div>
                    </div>
                    <small class="text-muted">All registered donors</small>
                </div>
            </div>
        </div>

        <!-- Eligible Donors -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm hover-shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Eligible Donors</h6>
                            <h2 class="font-weight-bold text-success">{{ $eligibleDonors }}</h2>
                            <small class="text-success">
                                {{ $totalDonors > 0 ? round(($eligibleDonors / $totalDonors) * 100, 1) : 0 }}%
                            </small>
                        </div>
                        <div class="badge badge-success rounded-circle p-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deferred Donors -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm hover-shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Deferred Donors</h6>
                            <h2 class="font-weight-bold text-warning">{{ $deferredDonors }}</h2>
                            <small class="text-warning">
                                {{ $totalDonors > 0 ? round(($deferredDonors / $totalDonors) * 100, 1) : 0 }}%
                            </small>
                        </div>
                        <div class="badge badge-warning rounded-circle p-3">
                            <i class="fas fa-pause-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Donations -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm hover-shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Recent Donations (30d)</h6>
                            <h2 class="font-weight-bold text-info">{{ $recentDonations }}</h2>
                        </div>
                        <div class="badge badge-info rounded-circle p-3">
                            <i class="fas fa-droplet fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blood Group Distribution -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-danger"></i> Blood Group Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="bloodGroupChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Rejection Trends -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Rejections (Last 3 Months)
                    </h5>
                </div>
                <div class="card-body">
                    <h3 class="text-warning mb-3">{{ $rejectedDonations }}</h3>
                    <div class="alert alert-light mb-0">
                        <small class="text-muted">Track donation rejections to improve screening</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-lightning-bolt text-info"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.donors.eligible-list') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search"></i> Search Eligible
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.donors.deferred-list') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-ban"></i> View Deferred
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.donors') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-list"></i> All Donors
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.add') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-plus"></i> Add Donor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('bloodGroupChart').getContext('2d');
        const bloodGroups = {!! json_encode($bloodGroupStats->pluck('blood_group')) !!};
        const counts = {!! json_encode($bloodGroupStats->pluck('total')) !!};

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: bloodGroups,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A',
                        '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>

<style>
    .text-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hover-shadow {
        transition: box-shadow 0.3s ease;
    }

    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .badge {
        background-color: rgba(0, 0, 0, 0.1);
        color: #333;
    }

    .badge.badge-primary {
        background-color: rgba(0, 123, 255, 0.2) !important;
        color: #0056b3 !important;
    }

    .badge.badge-success {
        background-color: rgba(40, 167, 69, 0.2) !important;
        color: #155724 !important;
    }

    .badge.badge-warning {
        background-color: rgba(255, 193, 7, 0.2) !important;
        color: #856404 !important;
    }

    .badge.badge-info {
        background-color: rgba(17, 182, 214, 0.2) !important;
        color: #004366 !important;
    }
</style>
@endsection
