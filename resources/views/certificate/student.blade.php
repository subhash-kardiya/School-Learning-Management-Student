@extends('layouts.admin')

@section('title', 'My Certificates')

@section('content')
    <div class="container-fluid py-4 certificate-modern certificate-module-compact">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3">{{ session('error') }}</div>
        @endif
        @php
            $roleLabel = ucfirst(session('role') ?? 'student');
            $canView =
                auth()->user() && method_exists(auth()->user(), 'hasPermission')
                    ? (auth()->user()->hasPermission('certificate_view')
                        ? 'Allowed'
                        : 'Denied')
                    : 'N/A';
        @endphp

        <div class="student-hero mb-4">
            <div>
                <h4 class="mb-1">My Certificates</h4>
                <p class="text-muted small mb-0">Only approved certificates are shown here</p>
            </div>
            <div class="hero-meta">

                <a href="{{ route('student.certificate.create') }}" class="btn btn-modern btn-sm ms-2">Request Certificate</a>
            </div>
        </div>

        <div class="student-kpis mb-4">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-certificate"></i></div>
                <div>
                    <div class="kpi-label">Total Issued</div>
                    <div class="kpi-value">{{ $certificates->where('status', 'approved')->count() }}</div>
                </div>
            </div>
            <div class="kpi-card kpi-alt">
                <div class="kpi-icon"><i class="fas fa-print"></i></div>
                <div>
                    <div class="kpi-label">Ready To Print</div>
                    <div class="kpi-value">{{ $certificates->where('status', 'approved')->count() }}</div>
                </div>
            </div>
        </div>

        <div class="card glass-card modern-list-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern certificate-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Certificate No</th>
                                <th>Type</th>
                                <th>Issue Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($certificates as $c)
                                <tr>
                                    <td><span class="cert-no">{{ $c->certificate_no }}</span></td>
                                    <td><span class="chip chip-soft">{{ ucfirst($c->certificate_type) }}</span></td>
                                    <td>{{ $c->issue_date ? \Illuminate\Support\Carbon::parse($c->issue_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>
                                        <span
                                            class="badge
                                            {{ $c->status === 'approved' ? 'bg-success' : '' }}
                                            {{ $c->status === 'pending' ? 'bg-warning text-dark' : '' }}
                                            {{ $c->status === 'rejected' ? 'bg-danger' : '' }}">
                                            {{ ucfirst($c->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('student.certificate.show', $c->id) }}"
                                            class="btn btn-sm btn-soft">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No approved certificates.</td>
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
    <link rel="stylesheet" href="{{ asset('css/resize/certificate-compact.css') }}">
@endpush
