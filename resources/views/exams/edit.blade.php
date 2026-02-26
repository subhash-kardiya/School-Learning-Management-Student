@extends('layouts.admin')

@section('title', 'Edit Exam')

@section('content')
<div class="container-fluid py-4">
    @php
        $flashError = session('error');
        $formErrors = $errors->all();
        if ($flashError) {
            $formErrors = array_values(array_filter($formErrors, function ($msg) use ($flashError) {
                return $msg !== $flashError;
            }));
        }
    @endphp
    @if ($flashError)
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            {{ $flashError }}
        </div>
    @endif
    @if (!empty($formErrors))
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <ul class="mb-0">
                @foreach ($formErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Exam</h5>
            <a href="{{ route('exams.createexam') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>

        <div class="card-body">
            <form action="{{ route('exams.update', $exam->id) }}" method="POST">
                @csrf
                @method('PUT')  

                <div class="row g-4">

                    {{-- Exam Name --}}
                    <div class="col-md-6">
                        <label class="form-label">Exam Name</label>
                        <select name="name" class="form-select" required>
                            <option value="">Select Exam Type</option>
                            <option value="Unit Test" {{ old('name', $exam->name) === 'Unit Test' ? 'selected' : '' }}>Unit Test</option>
                            <option value="Mid Term" {{ old('name', $exam->name) === 'Mid Term' ? 'selected' : '' }}>Mid Term</option>
                            <option value="Preliminary Exam" {{ old('name', $exam->name) === 'Preliminary Exam' ? 'selected' : '' }}>Preliminary Exam</option>
                            <option value="Final Exam" {{ old('name', $exam->name) === 'Final Exam' ? 'selected' : '' }}>Final Exam</option>
                        </select>
                    </div>

                    {{-- Academic Year --}}
                    <div class="col-md-6">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $exam->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Class --}}
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" data-year="{{ $class->academic_year_id }}" {{ $exam->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div class="col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" data-class="{{ $section->class_id }}" data-year="{{ $section->class->academic_year_id ?? '' }}" {{ $exam->section_id == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                     
                    <div class="col-md-6">
                        <label class="form-label">Subject name</label>
                        <select name="subject_name" class="form-select" required data-current="{{ $exam->subject_name }}">
                            <option value="">Select Subject name</option>
                        </select>
                    </div>
                  


                    {{-- Start Date --}}
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $exam->start_date->format('Y-m-d') }}" required>
                    </div>

                    {{-- End Date --}}
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $exam->end_date->format('Y-m-d') }}" required>
                    </div>

                    {{-- Time --}}
                    <div class="col-md-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('H:i') : '' }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" value="{{ $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('H:i') : '' }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Room No</label>
                        <input type="text" name="room_no" class="form-control" value="{{ $exam->room_no }}" required>
                    </div>

                    {{-- Total Mark --}}
                    <div class="col-md-6">
                        <label class="form-label">Total Marks</label>
                        <input type="number" name="total_mark" class="form-control" value="{{ $exam->total_mark }}" required>
                    </div>

                    {{-- Passing Mark --}}
                    <div class="col-md-6">
                        <label class="form-label">Passing Marks</label>
                        <input type="number" name="passing_mark" class="form-control" value="{{ $exam->passing_mark }}" required>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-12">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="1" {{ $exam->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $exam->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary-fancy px-5">Update Exam</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.querySelector('select[name="academic_year_id"]');
        const classSelect = document.querySelector('select[name="class_id"]');
        const sectionSelect = document.querySelector('select[name="section_id"]');
        const subjectSelect = document.querySelector('select[name="subject_name"]');
        const allSubjects = @json($subjects ?? []);
        const sectionSubjects = @json($sectionSubjects ?? []);
        const currentSubject = subjectSelect ? (subjectSelect.dataset.current || '') : '';

                function setAutoSubject() {
            const selectedClassId = classSelect ? classSelect.value : '';
            const selectedSectionId = sectionSelect ? sectionSelect.value : '';

            subjectSelect.innerHTML = '<option value="">Select Subject name</option>';

            if (!selectedClassId || !selectedSectionId) {
                return;
            }

            const mappedSubjects = sectionSubjects
                .filter(item =>
                    String(item.class_id) === String(selectedClassId) &&
                    String(item.section_id) === String(selectedSectionId) &&
                    item.subject_name
                )
                .map(item => item.subject_name)
                .filter((name, index, arr) => arr.indexOf(name) === index);

            if (!mappedSubjects.length) {
                return;
            }

            const preferredSubject = (typeof currentSubject !== 'undefined' && currentSubject) ? currentSubject : mappedSubjects[0];

            mappedSubjects.forEach(subjectName => {
                const option = document.createElement('option');
                option.value = subjectName;
                option.textContent = subjectName;
                option.selected = (subjectName === preferredSubject);
                subjectSelect.appendChild(option);
            });
        }

        function filterClasses() {
            const selectedYear = yearSelect.value;

            if (classSelect) {
                Array.from(classSelect.options).forEach(option => {
                    if (option.value === "") return;
                    const dataYear = option.getAttribute('data-year');
                    const shouldHide = !selectedYear || (dataYear !== selectedYear);
                    option.hidden = shouldHide;
                    option.style.display = shouldHide ? 'none' : '';
                });

                if (classSelect.value && classSelect.selectedOptions[0].hidden) {
                    classSelect.value = "";
                }

                filterSections();
            }
        }

        function filterSections() {
            const selectedClassId = classSelect ? classSelect.value : '';

            if (sectionSelect) {
                Array.from(sectionSelect.options).forEach(option => {
                    if (option.value === "") return;

                    const dataClass = option.getAttribute('data-class');
                    const shouldHide = !selectedClassId || (dataClass !== selectedClassId);
                    option.hidden = shouldHide;
                    option.style.display = shouldHide ? 'none' : '';
                });

                if (sectionSelect.value && sectionSelect.selectedOptions[0].hidden) {
                    sectionSelect.value = "";
                }

                setAutoSubject();
            }
        }

        if (classSelect) {
            classSelect.addEventListener('change', filterSections);
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', filterClasses);
        }

        if (sectionSelect) {
            sectionSelect.addEventListener('change', setAutoSubject);
        }

        filterClasses();
    });
</script>
@endpush



