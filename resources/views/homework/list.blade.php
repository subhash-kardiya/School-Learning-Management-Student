@extends('layouts.admin')

@section('title', isset($student) ? 'My Homework' : 'Homework List')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/homework-compact.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4 hw-page">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @php
            $isTeacher =
                auth()->user() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('teacher');
            $isAdmin =
                auth()->user() &&
                method_exists(auth()->user(), 'hasRole') &&
                (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'));
            $reportRoute = $isTeacher ? 'teacher.homework.submission.report' : 'homework.submission.report';
            $toggleRoute = $isTeacher ? 'teacher.homework.toggle.status' : 'homework.toggle.status';
            $deleteRoute = $isTeacher ? 'teacher.homework.destroy' : 'homework.destroy';
        @endphp

        @if (isset($student))
            <div class="card hw-card">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">My Homework</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 hw-table align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Homework</th>
                                    <th>Subject</th>
                                    <th>Assigned By</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($homeworks as $hw)
                                    @php
                                        $submitted = isset($submissionMap[$hw->id]);
                                        $overdue =
                                            !$submitted &&
                                            $hw->due_date &&
                                            \Illuminate\Support\Carbon::parse($hw->due_date)->isPast();
                                    @endphp
                                    <tr class="{{ $overdue ? 'hw-overdue' : '' }}">
                                        <td class="ps-3 fw-semibold">{{ $hw->title }}</td>
                                        <td>{{ $hw->subject?->name ?? '-' }}</td>
                                        <td>{{ $hw->teacher?->name ?? '-' }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($hw->due_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge {{ $submitted ? 'bg-success' : 'bg-danger' }}">
                                                {{ $submitted ? 'Submitted' : 'Not Submitted' }}
                                            </span>
                                        </td>
                                        <td class="pe-3">
                                            @if ($submitted)
                                                <button class="btn btn-sm btn-secondary" disabled>Submitted</button>
                                            @else
                                                <form action="{{ route('student.homework.submit', $hw->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-primary">Submit</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No homework found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0">Homework Management</h5>
                @if ($isTeacher)
                    <a href="{{ route('teacher.homework.create') }}" class="btn btn-primary">Create Homework</a>
                @endif
            </div>

            <form method="GET" class="hw-toolbar p-3 mb-3">
                <div class="row g-2 justify-content-end">
                    <div class="col-md-3 col-lg-3">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="Search by title">
                    </div>
                    <div class="col-md-2 col-lg-2">
                        <select name="class_id" id="homework-class-filter" class="form-select">
                            <option value="">All Classes</option>
                            @foreach ($classes ?? [] as $class)
                                <option value="{{ $class->id }}"
                                    {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-lg-2">
                        <select name="section_id" id="homework-section-filter" class="form-select">
                            <option value="">Select Class First</option>
                            @foreach ($sections ?? [] as $section)
                                <option value="{{ $section->id }}"
                                    data-class-id="{{ $section->class_id }}"
                                    {{ (string) request('section_id') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </form>

            <div class="card hw-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 hw-table align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Title</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Assigned By</th>
                                    <th>Due Date</th>
                                    <th>Submissions</th>
                                    <th>Status</th>
                                    <th class="pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($homeworks as $hw)
                                    @php
                                        $reportUrl = route($reportRoute, $hw->id);
                                    @endphp
                                    <tr class="hw-row {{ $hw->is_overdue ? 'hw-overdue' : '' }}"
                                        data-url="{{ $reportUrl }}">
                                        <td class="ps-3">
                                            <div class="fw-semibold">{{ $hw->title }}</div>
                                            @if ($hw->is_overdue)
                                                <small class="text-danger">Overdue</small>
                                            @endif
                                        </td>
                                        <td>{{ $hw->class?->name }}-{{ $hw->section?->name }}</td>
                                        <td>{{ $hw->subject?->name ?? '-' }}</td>
                                        <td>{{ $hw->teacher?->name ?? '-' }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($hw->due_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $hw->submitted_count }} /
                                                {{ $hw->total_students }}</span>
                                            <div class="progress mt-1" style="height:6px;">
                                                <div class="progress-bar bg-primary"
                                                    style="width: {{ $hw->submission_percent }}%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($hw->status)
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <span class="hw-status-dot bg-success"></span>Active
                                                </span>
                                            @else
                                                <span
                                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                    <span class="hw-status-dot bg-secondary"></span>Draft/Closed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="pe-3">
                                            <div class="d-flex gap-2">
                                                <a href="{{ $reportUrl }}" class="btn btn-sm btn-info">View</a>
                                                @if (!isset($hw->can_toggle) || $hw->can_toggle)
                                                    <form action="{{ route($toggleRoute, $hw->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-sm btn-warning">{{ $hw->status ? 'Close' : 'Activate' }}</button>
                                                    </form>
                                                @endif
                                                @if (($isAdmin && (!isset($hw->can_delete) || $hw->can_delete)))
                                                    <form action="{{ route($deleteRoute, $hw->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Delete homework?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No homework found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const filterForm = document.querySelector('.hw-toolbar');
            if (filterForm) {
                const classSelect = filterForm.querySelector('select[name="class_id"]');
                const sectionSelect = filterForm.querySelector('select[name="section_id"]');
                const syncSectionsByClass = function() {
                    if (!classSelect || !sectionSelect) return;

                    const selectedClassId = classSelect.value;
                    let selectedVisible = false;

                    sectionSelect.disabled = !selectedClassId;
                    sectionSelect.options[0].text = selectedClassId ? 'All Sections' : 'Select Class First';

                    Array.from(sectionSelect.options).forEach(function(option, index) {
                        if (index === 0) return;
                        const optionClassId = option.getAttribute('data-class-id') || '';
                        const show = selectedClassId && optionClassId === selectedClassId;
                        option.hidden = !show;
                        if (show && option.value === sectionSelect.value) {
                            selectedVisible = true;
                        }
                    });

                    if (sectionSelect.value && !selectedVisible) {
                        sectionSelect.value = '';
                    }
                };

                syncSectionsByClass();

                const selects = filterForm.querySelectorAll('select[name="class_id"], select[name="section_id"]');
                selects.forEach(function(select) {
                    select.addEventListener('change', function() {
                        if (select.name === 'class_id') {
                            syncSectionsByClass();
                        }
                        filterForm.submit();
                    });
                });
            }

            document.querySelectorAll('.hw-row').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    if (e.target.closest('a,button,form')) return;
                    const url = row.getAttribute('data-url');
                    if (url) window.location.href = url;
                });
            });
        })();
    </script>
@endpush
