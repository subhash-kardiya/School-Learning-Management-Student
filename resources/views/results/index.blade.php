@extends('layouts.admin')

@section('title', 'Results')

@section('content')
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Results ({{ $globalAcademicYears->firstWhere('id', $selectedAcademicYearId)->name ?? '-' }})</h5>
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <label for="exam_id" class="mb-0 small text-muted">Exam</label>
                    <select name="exam_id" id="exam_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @forelse($exams as $exam)
                            <option value="{{ $exam->id }}" {{ optional($selectedExam)->id === $exam->id ? 'selected' : '' }}>
                                {{ $exam->name }} - {{ optional($exam->class)->name }} {{ optional($exam->section)->name ? '(' . $exam->section->name . ')' : '' }}
                            </option>
                        @empty
                            <option value="">No Exams</option>
                        @endforelse
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Term</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resultRows as $row)
                                <tr>
                                    <td>{{ $row['student_name'] }}</td>
                                    <td>{{ optional(optional($selectedExam)->class)->name ?? '-' }}{{ optional(optional($selectedExam)->section)->name ? ' - ' . $selectedExam->section->name : '' }}</td>
                                    <td>{{ optional($selectedExam)->name ?? '-' }}</td>
                                    <td>{{ number_format($row['percentage'], 2) }}%</td>
                                    <td>{{ $row['grade'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No result data found for the selected exam.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
