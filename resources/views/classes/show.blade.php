@extends('layouts.admin')

@section('title', 'Class Details')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('classes.index') }}" class="btn btn-link text-decoration-none text-muted p-0 mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Classes
                </a>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-primary-fancy">
                    <i class="fa fa-pen me-2"></i> Edit Class
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Info Card (Full Width Now) -->
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">Class Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Class Designation</label>
                                <div class="p-3 bg-light rounded-3">
                                    <span class="fw-bold text-dark fs-5">{{ $class->name }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Current Academic Year</label>
                                <div class="p-3 bg-light rounded-3">
                                    <span
                                        class="fw-bold text-primary fs-5">{{ $class->academicYear ? $class->academicYear->name : 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="col-12 mt-5">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Assigned Management</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted">Class Teacher / Supervisor</label>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-3 me-3">
                                        <i class="fas fa-user-tie text-primary fa-lg"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">
                                            {{ $class->teacher ? $class->teacher->name : 'No teacher assigned' }}</div>
                                        <div class="text-muted small">Official Class In-charge</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted">Operation Status</label>
                                <div>
                                    @if ($class->status == 1)
                                        <span class="badge-soft-success">Active Enrolment</span>
                                    @else
                                        <span class="badge-soft-danger">Inactive / Suspended</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                            <div class="text-muted small">Record initialized on {{ $class->created_at->format('M d, Y') }}
                            </div>
                            <form action="{{ route('classes.destroy', $class->id) }}" method="POST"
                                onsubmit="return confirm('Permanently remove this class record?')">
                                @csrf

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
