@extends('layouts.admin')

@section('title', 'Student Dashboard')

@section('content')
    <div id="dashboard-content">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">My Attendance</h6>
                            <h3 class="fw-bold mb-0">92%</h3>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-user-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">My Grade</h6>
                            <h3 class="fw-bold mb-0">A+</h3>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-star fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Pending Homework</h6>
                            <h3 class="fw-bold mb-0">3</h3>
                        </div>
                        <div class="icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-book fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Today's Timetable</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0">Mathematics</h6>
                                    <small class="text-muted">09:00 AM - 10:00 AM | Prof. Sharma</small>
                                </div>
                                <span class="badge bg-light text-dark">Room 101</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0">Science</h6>
                                    <small class="text-muted">10:00 AM - 11:00 AM | Dr. Gupta</small>
                                </div>
                                <span class="badge bg-light text-dark">Lab 2</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Student Profile</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset('assets/student-avatar.svg') }}" class="rounded-pill mb-3"
                            style="width: 80px; height: 80px;" alt="Student">
                        <h6 class="fw-bold mb-1">{{ session('auth_name') ?? 'Student Name' }}</h6>
                        <p class="text-muted small">ID: ST-2024-001 | Class: 10th-A</p>
                        <hr>
                        <div class="text-start">
                            <small class="text-muted d-block">Academic Year</small>
                            <p class="mb-2">2024-2025</p>
                            <small class="text-muted d-block">Class Teacher</small>
                            <p class="mb-0">Prof. Vikram Rathore</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
