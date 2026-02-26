@extends('layouts.admin')

@section('title', 'Exam Subject Setup')

@section('content')
    @php
        $isTeacherRole = session('role') === 'teacher';
        $examMarksRoute = $isTeacherRole ? 'teacher.exams.marks' : 'exams.marks';
        $examScheduleStoreRoute = $isTeacherRole ? 'teacher.exams.schedule.store' : 'exams.schedule.store';
    @endphp
    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3">Please correct the highlighted fields.</div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Step 2: Subject-wise Exam Entry ({{ $globalAcademicYears->firstWhere('id', $yearId)->name ?? 'No Year Selected' }})</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Select Exam</label>
                        <select name="exam_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Select Exam</option>
                            @foreach ($exams as $exam)
                                <option value="{{ $exam->id }}" {{ (string) optional($selectedExam)->id === (string) $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }} | {{ $exam->class?->name }}-{{ $exam->section?->name }} | {{ $exam->start_date }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route($examMarksRoute, ['exam_id' => optional($selectedExam)->id]) }}" class="btn btn-outline-primary">Go To Step 3</a>
                    </div>
                </form>

                @if ($selectedExam)
                    <hr>
                    <form method="POST" action="{{ route($examScheduleStoreRoute) }}" class="row g-3 align-items-end">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Subject</label>
                            <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Select Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ (string) old('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Theory</label>
                            <input type="number" step="0.01" name="theory_marks" class="form-control @error('theory_marks') is-invalid @enderror" min="0" value="{{ old('theory_marks', 0) }}">
                            @error('theory_marks')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Practical</label>
                            <input type="number" step="0.01" name="practical_marks" class="form-control @error('practical_marks') is-invalid @enderror" min="0" value="{{ old('practical_marks', 0) }}">
                            @error('practical_marks')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Internal</label>
                            <input type="number" step="0.01" name="internal_marks" class="form-control @error('internal_marks') is-invalid @enderror" min="0" value="{{ old('internal_marks', 0) }}">
                            @error('internal_marks')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Passing</label>
                            <input type="number" step="0.01" name="passing_marks" class="form-control @error('passing_marks') is-invalid @enderror" min="0" value="{{ old('passing_marks', 33) }}" required>
                            @error('passing_marks')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary-fancy w-100">Save</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Subjects Mapped To Exam</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Theory</th>
                                <th>Practical</th>
                                <th>Internal</th>
                                <th>Passing</th>
                                <th>Total Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($examSubjects as $row)
                                <tr>
                                    <td>{{ $selectedExam?->name }}</td>
                                    <td>{{ $row->subject?->name }}</td>
                                    <td>{{ $row->theory_marks }}</td>
                                    <td>{{ $row->practical_marks }}</td>
                                    <td>{{ $row->internal_marks }}</td>
                                    <td>{{ $row->passing_marks }}</td>
                                    <td>{{ $row->total_marks }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No subjects added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
