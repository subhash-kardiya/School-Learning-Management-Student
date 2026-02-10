@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid">

        {{-- STATS CARDS --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <a href="{{ route('students.index') }}" class="text-decoration-none">
                    <div class="card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Students</h6>
                            <h3 class="mb-0">{{ $studentCount ?? 0 }}</h3>
                        </div>
                        <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center"
                            style="width:40px; height:40px;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('teachers.index') }}" class="text-decoration-none">
                    <div class="card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Teachers</h6>
                            <h3 class="mb-0">{{ $teacherCount ?? 0 }}</h3>
                        </div>
                        <div class="bg-warning text-white rounded d-flex align-items-center justify-content-center"
                            style="width:40px; height:40px;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('classes.index') }}" class="text-decoration-none">
                    <div class="card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Classes</h6>
                            <h3 class="mb-0">{{ $classCount ?? 0 }}</h3>
                        </div>
                        <div class="bg-success text-white rounded d-flex align-items-center justify-content-center"
                            style="width:40px; height:40px;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="row g-4">

            {{-- Pending Tasks --}}
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pending Tasks</h5>
                        <span class="badge bg-danger">5 Pending</span>
                    </div>
                    <div class="list-group list-group-flush">

                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-signature me-3 text-secondary"></i>
                                <div>
                                    <h6 class="mb-0">Approve Leave Request</h6>
                                    <span class="text-muted">John Doe (Teacher)</span>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm rounded-pill">View</button>
                        </div>

                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-alt me-3 text-secondary"></i>
                                <div>
                                    <h6 class="mb-0">Finalize Exam Schedule</h6>
                                    <span class="text-muted">Class 10 - Mid Term</span>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm rounded-pill">Action</button>
                        </div>

                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-plus me-3 text-secondary"></i>
                                <div>
                                    <h6 class="mb-0">New Admission Review</h6>
                                    <span class="text-muted">3 New Applications</span>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm rounded-pill">Review</button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Notices --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Notices (3)</h5>
                    </div>
                    <div class="card-body">

                        <div class="p-3 mb-3 rounded bg-primary bg-opacity-10 d-flex">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            <div>
                                <strong class="text-dark">Holiday:</strong> <br>
                                <span class="text-dark">School closed on Friday.</span>
                            </div>
                        </div>

                        <div class="p-3 mb-3 rounded bg-warning bg-opacity-10 d-flex">
                            <i class="fas fa-bullhorn me-2 text-warning"></i>
                            <div>
                                <strong class="text-dark">Exam:</strong> <br>
                                <span class="text-dark">Mid-term dates announced.</span>
                            </div>
                        </div>

                        <div class="p-3 rounded bg-success bg-opacity-10 d-flex">
                            <i class="fas fa-running me-2 text-success"></i>
                            <div>
                                <strong class="text-dark">Event:</strong> <br>
                                <span class="text-dark">Sports Day meeting 4 PM.</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
