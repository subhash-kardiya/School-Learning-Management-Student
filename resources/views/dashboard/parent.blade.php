@extends('layouts.admin')

@section('title', 'Parent Dashboard')

@section('content')
    <div id="dashboard-content">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Child's Attendance</h6>
                            <h3 class="fw-bold mb-0">88%</h3>
                        </div>
                        <div class="icon-box bg-info bg-opacity-10 text-info">
                            <i class="fas fa-calendar-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Fee Status</h6>
                            <h3 class="fw-bold mb-0 text-success">Paid</h3>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-money-bill-wave fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Exams Grade</h6>
                            <h3 class="fw-bold mb-0">B+</h3>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-graduation-cap fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Child Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <i class="fas fa-user-circle fs-1 me-3 text-secondary"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Master Aarav Patel</h6>
                                <small class="text-muted">Class: 8 - Section: B</small>
                            </div>
                            <div class="ms-auto text-end">
                                <span class="badge bg-success-subtle text-success">Active</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center mt-3">
                            <div class="col-4">
                                <h6 class="text-muted mb-1 small">Total Tasks</h6>
                                <p class="fw-bold mb-0">15</p>
                            </div>
                            <div class="col-4">
                                <h6 class="text-muted mb-1 small">Submitted</h6>
                                <p class="fw-bold mb-0">12</p>
                            </div>
                            <div class="col-4">
                                <h6 class="text-muted mb-1 small">Pending</h6>
                                <p class="fw-bold mb-0 text-danger">3</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">School Notices</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 mb-3" role="alert">
                            <div class="d-flex">
                                <i class="fas fa-envelope-open-text mt-1 me-2"></i>
                                <div><strong>PTM:</strong><br>Parents Teacher Meeting on Saturday.</div>
                            </div>
                        </div>
                        <div class="alert alert-light border mb-0" role="alert">
                            <div class="d-flex">
                                <i class="fas fa-clock mt-1 me-2 text-warning"></i>
                                <div><strong>Fee Reminder:</strong><br>Next quarter fee due by 15th Feb.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
