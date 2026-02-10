@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
    <div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Announcements (Demo)</h5>
                        <button class="btn btn-primary-fancy">
                            <i class="fa fa-plus me-2"></i> New
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-primary border-0 mb-3">
                            <strong>Holiday:</strong> School closed on Friday.
                        </div>
                        <div class="alert alert-warning border-0 mb-3">
                            <strong>Exam:</strong> Mid-term schedule published.
                        </div>
                        <div class="alert alert-success border-0 mb-0">
                            <strong>Event:</strong> Sports day on Saturday.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Notices</span>
                            <span class="fw-bold">12</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Active</span>
                            <span class="fw-bold text-success">9</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Archived</span>
                            <span class="fw-bold text-muted">3</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
