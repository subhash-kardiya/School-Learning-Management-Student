@extends('layouts.admin')

@section('title', 'My Certificates')

@section('content')
    <div class="container-fluid py-4 certificate-modern">
        @php
            $roleLabel = ucfirst(session('role') ?? 'student');
            $canView = auth()->user() && method_exists(auth()->user(), 'hasPermission')
                ? (auth()->user()->hasPermission('certificate_view') ? 'Allowed' : 'Denied')
                : 'N/A';
        @endphp

        <div class="student-hero mb-4">
            <div>
                <h4 class="mb-1">My Certificates</h4>
                <p class="text-muted small mb-0">Issued certificates only</p>
            </div>
            <div class="hero-meta">
                <span class="pill">Role: {{ $roleLabel }}</span>
                <span class="pill pill-secondary">Permission: {{ $canView }}</span>
            </div>
        </div>

        <div class="student-kpis mb-4">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-certificate"></i></div>
                <div>
                    <div class="kpi-label">Total Issued</div>
                    <div class="kpi-value">{{ $certificates->count() }}</div>
                </div>
            </div>
            <div class="kpi-card kpi-alt">
                <div class="kpi-icon"><i class="fas fa-print"></i></div>
                <div>
                    <div class="kpi-label">Ready To Print</div>
                    <div class="kpi-value">{{ $certificates->count() }}</div>
                </div>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
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
                                    <td>{{ $c->certificate_no }}</td>
                                    <td>{{ ucfirst($c->certificate_type) }}</td>
                                    <td>{{ $c->issue_date }}</td>
                                    <td><span class="badge bg-success">Issued</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('student.certificate.show', $c->id) }}" class="btn btn-sm btn-soft">View</a>
                                        <a href="{{ route('student.certificate.show', $c->id) }}?print=1" class="btn btn-sm btn-modern" target="_blank">Print</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No issued certificates.</td>
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
