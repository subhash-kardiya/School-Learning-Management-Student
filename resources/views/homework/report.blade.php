@extends('layouts.admin')

@section('title', 'Homework Submission Report')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/homework-compact.css') }}">
@endpush

@section('content')
    @php
        $isTeacher = auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('teacher');
        $homeworkListRoute = $isTeacher ? 'teacher.homework.list' : 'homework.list';
    @endphp

    <div class="container-fluid py-4 hw-page">
        <div class="report-shell">
            <div class="hero-card p-3 p-md-4 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Homework Submission Report</h4>
                        <p class="mb-0 opacity-75">Track class-wise student submission status.</p>
                    </div>
                    <a href="{{ route($homeworkListRoute) }}" class="btn btn-light btn-sm fw-semibold">Back to Homework</a>
                </div>
                <div class="row g-2">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="hero-meta">
                            <div class="label">Homework</div>
                            <div class="value">{{ $homework->title }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="hero-meta">
                            <div class="label">Class - Section</div>
                            <div class="value">{{ $homework->class?->name ?? '-' }} -
                                {{ $homework->section?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="hero-meta">
                            <div class="label">Subject</div>
                            <div class="value">{{ $homework->subject?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="hero-meta">
                            <div class="label">Due Date</div>
                            <div class="value">
                                {{ \Illuminate\Support\Carbon::parse($homework->due_date)->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Total Students</div>
                        <div class="stat-value text-dark">{{ $summary['total'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Submitted</div>
                        <div class="stat-value text-success">{{ $summary['submitted'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Not Submitted</div>
                        <div class="stat-value text-danger">{{ $summary['not_submitted'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-label">Submission Rate</div>
                        <div class="stat-value text-primary">{{ $summary['percent'] }}%</div>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Student Submission Details</h6>
                    <small class="text-muted">{{ $rows->count() }} students</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle report-table">
                        <thead>
                            <tr>
                                <th class="ps-3">Student Name</th>
                                <th>Roll No</th>
                                <th>Submitted At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $row['student']->student_name }}</td>
                                    <td>{{ $row['roll_no'] ?? '-' }}</td>
                                    <td>
                                        {{ $row['submitted_at'] ? \Illuminate\Support\Carbon::parse($row['submitted_at'])->format('d M Y, h:i A') : '-' }}
                                    </td>
                                    <td>
                                        @if ($row['submitted'])
                                            <span class="status-chip text-success bg-success-subtle border-success-subtle">
                                                <span class="status-dot bg-success"></span>Submitted
                                            </span>
                                        @else
                                            <span class="status-chip text-danger bg-danger-subtle border-danger-subtle">
                                                <span class="status-dot bg-danger"></span>Not Submitted
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
