@extends('layouts.admin')

@section('title', 'Marks Entry')

@section('content')
    @php
        $examMarksStoreRoute = session('role') === 'teacher' ? 'teacher.exams.marks.store' : 'exams.marks.store';
    @endphp
    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3">Please correct marks input and try again.</div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Step 3: Marks Entry ({{ $globalAcademicYears->firstWhere('id', $yearId)->name ?? 'No Year Selected' }})</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Select Exam</label>
                        <select name="exam_id" id="marks-exam" class="form-select" onchange="this.form.submit()">
                            <option value="">Select Exam</option>
                            @foreach ($exams as $exam)
                                <option value="{{ $exam->id }}" {{ (string) optional($selectedExam)->id === (string) $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }} | {{ $exam->class?->name }}-{{ $exam->section?->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Select Subject</label>
                        <select name="exam_subject_id" id="marks-subject" class="form-select" onchange="this.form.submit()">
                            <option value="">Select Subject</option>
                            @foreach ($examSubjects as $examSubject)
                                <option value="{{ $examSubject->id }}" {{ (string) optional($selectedExamSubject)->id === (string) $examSubject->id ? 'selected' : '' }}>
                                    {{ $examSubject->subject?->name }} ({{ $examSubject->total_marks }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Students</h5>
                @if ($selectedExamSubject)
                    <span class="badge bg-light text-dark">
                        Max: {{ $selectedExamSubject->total_marks }} | Pass: {{ $selectedExamSubject->passing_marks }}
                    </span>
                @endif
            </div>
            <div class="card-body p-0">
                @if ($selectedExam && $selectedExamSubject)
                    <form method="POST" action="{{ route($examMarksStoreRoute) }}">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                        <input type="hidden" name="exam_subject_id" value="{{ $selectedExamSubject->id }}">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Roll No</th>
                                        <th>Theory ({{ $selectedExamSubject->theory_marks }})</th>
                                        <th>Practical ({{ $selectedExamSubject->practical_marks }})</th>
                                        <th>Internal ({{ $selectedExamSubject->internal_marks }})</th>
                                        <th>Absent</th>
                                        <th>Total</th>
                                        <th>Subject Result</th>
                                        <th>Overall %</th>
                                        <th>Overall Result</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $student)
                                        @php
                                            $row = $marksMap[$student->id] ?? null;
                                            $theoryOld = old('theory_marks.' . $student->id, $row['theory_marks'] ?? '');
                                            $practicalOld = old('practical_marks.' . $student->id, $row['practical_marks'] ?? '');
                                            $internalOld = old('internal_marks.' . $student->id, $row['internal_marks'] ?? '');
                                            $absentOld = old('is_absent.' . $student->id, $row['is_absent'] ?? false);
                                            $subjectTotal = is_numeric($theoryOld) || is_numeric($practicalOld) || is_numeric($internalOld)
                                                ? (float) ($theoryOld ?: 0) + (float) ($practicalOld ?: 0) + (float) ($internalOld ?: 0)
                                                : null;
                                            $subjectResult = $absentOld
                                                ? 'Absent'
                                                : (!is_null($subjectTotal) && $subjectTotal >= (float) $selectedExamSubject->passing_marks ? 'Pass' : 'Fail');
                                            $summary = $studentSummaries[$student->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $student->student_name }}</td>
                                            <td>{{ $student->roll_no }}</td>
                                            <td style="max-width: 150px;">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="{{ $selectedExamSubject->theory_marks }}"
                                                    class="form-control @error('theory_marks.' . $student->id) is-invalid @enderror"
                                                    name="theory_marks[{{ $student->id }}]"
                                                    value="{{ $theoryOld }}">
                                                @error('theory_marks.' . $student->id)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td style="max-width: 150px;">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="{{ $selectedExamSubject->practical_marks }}"
                                                    class="form-control @error('practical_marks.' . $student->id) is-invalid @enderror"
                                                    name="practical_marks[{{ $student->id }}]"
                                                    value="{{ $practicalOld }}">
                                                @error('practical_marks.' . $student->id)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td style="max-width: 150px;">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="{{ $selectedExamSubject->internal_marks }}"
                                                    class="form-control @error('internal_marks.' . $student->id) is-invalid @enderror"
                                                    name="internal_marks[{{ $student->id }}]"
                                                    value="{{ $internalOld }}">
                                                @error('internal_marks.' . $student->id)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_absent[{{ $student->id }}]" value="1" {{ $absentOld ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>{{ $subjectTotal ?? '-' }}</td>
                                            <td>
                                                <span class="badge {{ $subjectResult === 'Pass' ? 'bg-success' : ($subjectResult === 'Absent' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                    {{ $subjectResult }}
                                                </span>
                                            </td>
                                            <td>{{ $summary['percentage'] ?? '-' }}</td>
                                            <td>
                                                @if ($summary)
                                                    <span class="badge {{ $summary['result'] === 'Pass' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $summary['result'] }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $summary['grade'] ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted py-4">No students found for this exam class-section.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 text-end border-top">
                            <button class="btn btn-primary-fancy px-4">Save Marks</button>
                        </div>
                    </form>
                @else
                    <div class="text-center text-muted py-5">
                        Select exam and subject to load students.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
