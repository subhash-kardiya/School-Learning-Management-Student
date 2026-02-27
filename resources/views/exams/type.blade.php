@extends('layouts.admin')

@section('title', 'Exam Setup')

@section('content')
    @php
        $isTeacherRole = session('role') === 'teacher';
        $examTypeStoreRoute = $isTeacherRole ? 'teacher.exams.type.store' : 'exams.type.store';
        $examScheduleRoute = $isTeacherRole ? 'teacher.exams.schedule' : 'exams.schedule';
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
                <h5 class="mb-0">Step 1: Create Exam ({{ $globalAcademicYears->firstWhere('id', $yearId)->name ?? 'No Year Selected' }})</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route($examTypeStoreRoute) }}" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Exam Type</label>
                        <select name="exam_type_id" class="form-select @error('exam_type_id') is-invalid @enderror" required>
                            <option value="">Select Exam Type</option>
                            @foreach ($examTypes as $type)
                                <option value="{{ $type->id }}" {{ (string) old('exam_type_id') === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('exam_type_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Class</label>
                        <select name="class_id" id="exam-class" class="form-select @error('class_id') is-invalid @enderror" required>
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ (string) old('class_id') === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Section</label>
                        <select name="section_id" id="exam-section" class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option
                                    value="{{ $section->id }}"
                                    data-class-id="{{ $section->class_id }}"
                                    {{ (string) old('section_id') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Exam Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Unit Test 1 / Mid Term / Final" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Start Date</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                        @error('start_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">End Date</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                        @error('end_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Result Publish Date</label>
                        <input type="date" name="result_publish_date" class="form-control @error('result_publish_date') is-invalid @enderror" value="{{ old('result_publish_date') }}">
                        @error('result_publish_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary-fancy px-4">Save Exam</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Created Exams</h5>
                <a href="{{ route($examScheduleRoute) }}" class="btn btn-sm btn-outline-primary">Go To Step 2</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Type</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Date Range</th>
                                <th>Publish Date</th>
                                <th>Status</th>
                                <th>Subjects Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($exams as $exam)
                                <tr>
                                    <td>{{ $exam->name }}</td>
                                    <td>{{ $exam->examType?->name }}</td>
                                    <td>{{ $exam->class?->name }}</td>
                                    <td>{{ $exam->section?->name }}</td>
                                    <td>{{ $exam->start_date }} to {{ $exam->end_date }}</td>
                                    <td>{{ $exam->result_publish_date ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $exam->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($exam->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $exam->examSubjects->count() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No exams created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const classSelect = document.getElementById('exam-class');
            const sectionSelect = document.getElementById('exam-section');
            if (!classSelect || !sectionSelect) return;

            const syncSections = () => {
                const classId = classSelect.value;
                Array.from(sectionSelect.options).forEach(option => {
                    if (!option.value) return;
                    option.hidden = classId && option.getAttribute('data-class-id') !== classId;
                });
                if (sectionSelect.selectedOptions.length && sectionSelect.selectedOptions[0].hidden) {
                    sectionSelect.value = '';
                }
            };

            classSelect.addEventListener('change', syncSections);
            syncSections();
        })();
    </script>
@endpush
