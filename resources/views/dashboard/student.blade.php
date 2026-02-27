@extends('layouts.admin')

@section('title', 'Student Dashboard')

@section('content')
    <div id="dashboard-content">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('student.certificate.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-certificate me-1"></i> My Certificates
            </a>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
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
                    <div class="d-flex justify-content-between align-items-center">
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
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending Homework</h6>
                            <h3 class="fw-bold mb-0">{{ $pendingHomework ?? 0 }}</h3>
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
                        <h5 class="fw-bold mb-0">Latest Announcements</h5>
                    </div>
                    <div class="card-body">
                        @forelse($latestAnnouncements ?? [] as $announcement)
                            <div class="alert alert-light border mb-3" role="alert">
                                <strong>{{ $announcement->title }}</strong><br>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($announcement->description, 75) }}</small>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No announcements available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
