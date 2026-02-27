@extends('layouts.admin')

@section('title', 'Request Certificate')

@section('content')
    <div class="container-fluid py-4 certificate-modern certificate-module-compact">
        <div class="mb-3 no-print">
            <a href="{{ route('student.certificate.index') }}" class="text-muted text-decoration-none fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Back to My Certificates
            </a>
        </div>

        <div class="request-hero mb-4">
            <div>
                <h4 class="mb-1">Certificate Request</h4>
                <p class="text-muted mb-0">Submit request with complete details for quick approval workflow.</p>
            </div>

        </div>

        <div class="card glass-card">
            <div class="card-header modern-card-header">
                <strong>Request Certificate</strong>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('student.certificate.store') }}" method="POST" class="row g-3">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm">
                            Please fix the highlighted fields and try again.
                        </div>
                    @endif

                    <div class="col-md-12">
                        <div class="alert alert-info border-0 mb-0 modern-info">
                            <strong>Active Academic Year:</strong> {{ $activeYear->name }}
                            ({{ \Illuminate\Support\Carbon::parse($activeYear->start_date)->format('d-m-Y') }} to
                            {{ \Illuminate\Support\Carbon::parse($activeYear->end_date)->format('d-m-Y') }})
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label modern-label">Student</label>
                        <input type="text" class="form-control modern-control"
                            value="{{ $student->student_name }} ({{ $student->roll_no ?? 'N/A' }})" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label modern-label">Certificate Type</label>
                        <select name="certificate_type"
                            class="form-select modern-control @error('certificate_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="bonafide" {{ old('certificate_type') === 'bonafide' ? 'selected' : '' }}>Bonafide
                            </option>
                            <option value="leaving" {{ old('certificate_type') === 'leaving' ? 'selected' : '' }}>Leaving
                            </option>
                        </select>
                        @error('certificate_type')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label modern-label">Reason</label>
                        <input type="text" name="reason"
                            class="form-control modern-control @error('reason') is-invalid @enderror"
                            placeholder="Write your certificate request reason" value="{{ old('reason') }}">
                        @error('reason')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-modern px-4 py-2">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/certificate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resize/certificate-compact.css') }}">
@endpush
