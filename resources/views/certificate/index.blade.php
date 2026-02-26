@extends('layouts.admin')

@section('title', 'Certificate')

@section('content')
    <div class="container-fluid py-4 certificate-modern">
        <div class="hero-panel mb-4">
            <div>
                <h3 class="mb-1">Certificates</h3>
                <p class="mb-0 text-muted">Manage and print certificates</p>
            </div>
            <div class="hero-actions">
                <button class="btn btn-modern" data-bs-toggle="collapse" data-bs-target="#certificateCreateForm">
                    <i class="fas fa-plus me-2"></i>Create Certificate
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif

        <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="certificateCreateForm">
            <div class="card glass-card mb-4">
                <div class="card-header">
                    <strong>Create Certificate</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('certificate.store') }}" method="POST" class="row g-3">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Select Student</option>
                                @foreach ($students as $s)
                                    <option value="{{ $s->id }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->student_name }} ({{ $s->roll_no ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Certificate Type</label>
                            <select name="certificate_type" class="form-select" required>
                                <option value="bonafide" {{ old('certificate_type') == 'bonafide' ? 'selected' : '' }}>Bonafide</option>
                                <option value="leaving" {{ old('certificate_type') == 'leaving' ? 'selected' : '' }}>Leaving</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control"
                                value="{{ old('issue_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select">
                                <option value="">Select Year</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Reason (Leaving only)</label>
                            <input type="text" name="reason" class="form-control" value="{{ old('reason') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Conduct</label>
                            <input type="text" name="conduct" class="form-control" value="{{ old('conduct') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="draft">Draft</option>
                                <option value="issued">Issued</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-modern px-4">Generate Certificate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-header">
                <strong>Generated Certificates</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Certificate No</th>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Issue Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($certificates as $c)
                                <tr>
                                    <td>{{ $c->certificate_no }}</td>
                                    <td>{{ $c->student?->student_name }}</td>
                                    <td>{{ ucfirst($c->certificate_type) }}</td>
                                    <td>
                                        <span class="badge {{ $c->status === 'issued' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($c->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $c->issue_date }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('certificate.show', $c->id) }}" class="btn btn-sm btn-soft">View</a>
                                        <a href="{{ route('certificate.show', $c->id) }}?print=1" class="btn btn-sm btn-modern" target="_blank">Print</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No certificates yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/certificate.css') }}">
@endpush
