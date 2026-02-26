@extends('layouts.admin')

@section('title', 'Exam Schedule')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Exam Schedule</h5>
            
        </div>

        <div class="card-body">
            @if(!in_array(session('role'), ['student', 'parent']))
                <form id="scheduleFilterForm" action="{{ route('exams.schedule') }}" method="GET" class="row g-3 mb-4 align-items-end justify-content-end">
                    <div class="col-md-2 col-lg-2">
                        <label for="academic_year_id" class="form-label">Academic Year</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (isset($selectedAcademicYear) && $selectedAcademicYear == $year->id) ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-lg-2">
                        <label for="class_id" class="form-label">Class</label>
                        <select name="class_id" id="class_id" class="form-select">
                            <option value="">Class 1</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ (isset($selectedClass) && (string)$selectedClass === (string)$class->id) ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-lg-2">
                        <label for="section_id" class="form-label">Section</label>
                        <select name="section_id" id="section_id" class="form-select">
                            <option value="">Section A</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" data-class="{{ $section->class_id }}" {{ (isset($selectedSection) && (string)$selectedSection === (string)$section->id) ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif
            <h5 class="mb-3">Exam List</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Exam Name</th>
                            <th>Class & Section</th>
                            <th>Subject Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Time</th>
                            <th>Room No</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>{{ $exam->name }}</td>
                                <td>{{ $exam->class->name ?? '-' }}{{ $exam->section ? ' - ' . $exam->section->name : '' }}</td>
                                <td>{{ $exam->subject_name }}</td>
                                <td>{{ $exam->start_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $exam->end_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>
                                    @if($exam->start_time)
                                        {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} - {{ $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('h:i A') : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $exam->room_no ?? '-' }}</td>
                               
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No exams found.</td>
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
    document.addEventListener('DOMContentLoaded', function () {
        if (['student', 'parent'].includes("{{ session('role') }}")) return;

        const form = document.getElementById('scheduleFilterForm');
        if (!form) return;

        const classField = document.getElementById('class_id');
        const sectionField = document.getElementById('section_id');
        const yearField = document.getElementById('academic_year_id');

        function selectFirstVisibleOption(selectElement) {
            if (!selectElement) return;
            const firstVisible = Array.from(selectElement.options).find(opt => opt.value !== '' && !opt.hidden);
            selectElement.value = firstVisible ? firstVisible.value : '';
        }

        function selectPreferredOptionByText(selectElement, preferredTexts = []) {
            if (!selectElement) return false;
            const normalizedPreferred = preferredTexts.map(t => String(t).trim().toLowerCase());
            const candidate = Array.from(selectElement.options).find(opt => {
                if (opt.value === '' || opt.hidden) return false;
                const label = String(opt.textContent || '').trim().toLowerCase();
                return normalizedPreferred.includes(label);
            });
            if (!candidate) return false;
            selectElement.value = candidate.value;
            return true;
        }

        function filterSectionsByClass() {
            if (!classField || !sectionField) return;
            const selectedClass = classField.value;
            Array.from(sectionField.options).forEach(function (option) {
                if (option.value === '') return;
                const optionClass = option.getAttribute('data-class');
                const hidden = selectedClass && optionClass !== selectedClass;
                option.hidden = hidden;
                option.style.display = hidden ? 'none' : '';
            });

            const selectedOption = sectionField.selectedOptions[0];
            if (sectionField.value && selectedOption && selectedOption.hidden) {
                sectionField.value = '';
            }
        }

        function initializeDefaultFilters() {
            let changed = false;

            if (classField && !classField.value) {
                const classPicked = selectPreferredOptionByText(classField, ['Class 1']);
                if (!classPicked) {
                    selectFirstVisibleOption(classField);
                }
                changed = !!classField.value;
            }

            filterSectionsByClass();

            if (sectionField && !sectionField.value) {
                const sectionPicked = selectPreferredOptionByText(sectionField, ['Section A', 'A']);
                if (!sectionPicked) {
                    selectFirstVisibleOption(sectionField);
                }
                changed = changed || !!sectionField.value;
            }

            if (changed) {
                form.submit();
            }
        }

        if (yearField) {
            yearField.addEventListener('change', function () {
                form.submit();
            });
        }

        if (classField) {
            classField.addEventListener('change', function () {
                filterSectionsByClass();
                if (sectionField && !sectionField.value) {
                    const sectionPicked = selectPreferredOptionByText(sectionField, ['Section A', 'A']);
                    if (!sectionPicked) {
                        selectFirstVisibleOption(sectionField);
                    }
                }
                form.submit();
            });
        }

        if (sectionField) {
            sectionField.addEventListener('change', function () {
                form.submit();
            });
        }

        filterSectionsByClass();
        initializeDefaultFilters();
    });
</script>
@endpush
