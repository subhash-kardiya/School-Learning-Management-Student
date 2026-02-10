@extends('layouts.admin')

@section('title', 'Subject Details')

@section('content')
    <div class="container-fluid py-4 subject-ui">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">{{ $subject->name }}</h3>
                <div class="d-flex gap-2 align-items-center">
                    <span class="chip">Code: {{ $subject->subject_code }}</span>
                    <span class="chip">Class: {{ $subject->class->name ?? 'N/A' }}</span>

                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-primary">
                    <i class="fas fa-pen me-1"></i> Edit
                </a>
                <a href="{{ route('subjects.index') }}" class="btn btn-light border">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <!-- KPI STRIP -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="kpi-card gradient-primary">
                    <i class="fas fa-book"></i>
                    <div>
                        <small>Subject</small>
                        <h6 class="mb-0">{{ $subject->name }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card gradient-info">
                    <i class="fas fa-school"></i>
                    <div>
                        <small>Class</small>
                        <h6 class="mb-0">{{ $subject->class->name ?? 'N/A' }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card gradient-success">
                    <i class="fas fa-user-tie"></i>
                    <div>
                        <small>Teacher</small>
                        <h6 class="mb-0">{{ $subject->teacher->name ?? 'Not Assigned' }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENT GRID -->
        <div class="row g-4">

            <!-- LEFT: DETAILS -->
            <div class="col-lg-7">
                <div class="glass-card">
                    <h6 class="section-title">Academic Details</h6>
                    <div class="detail-row">
                        <span>Subject Name</span><strong>{{ $subject->name }}</strong>
                    </div>
                    <div class="detail-row">
                        <span>Subject Code</span><strong>{{ $subject->subject_code }}</strong>
                    </div>
                    <div class="detail-row">
                        <span>Class</span><strong>{{ $subject->class->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="detail-row">
                        <span>Status</span>
                        @if ($subject->status)
                            <strong class="text-success">Active</strong>
                        @else
                            <strong class="text-danger">Inactive</strong>
                        @endif
                    </div>
                </div>
            </div>

            <!-- RIGHT: TEACHER + META -->
            <div class="col-lg-5">
                <div class="glass-card mb-4">
                    <h6 class="section-title">Assigned Teacher</h6>
                    <div class="teacher-box">
                        <div class="avatar"><i class="fas fa-user"></i></div>
                        <div>
                            <strong>{{ $subject->teacher->name ?? 'Not Assigned' }}</strong>
                            <small class="text-muted d-block">Teacher ID: {{ $subject->teacher->id ?? '-' }}</small>
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <h6 class="section-title">System Timeline</h6>
                    <div class="timeline">
                        <div class="t-item">
                            <span class="dot"></span>
                            <div>
                                <strong>Created</strong>
                                <small
                                    class="text-muted d-block">{{ $subject->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        <div class="t-item">
                            <span class="dot"></span>
                            <div>
                                <strong>Last Updated</strong>
                                <small
                                    class="text-muted d-block">{{ $subject->updated_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
