@extends('layouts.admin')

@section('title', 'Create Exam')

@section('content')
<div class="container-fluid py-4">

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">
            {{ session('success') }}
        </div>
    @endif
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
            <h5 class="mb-0">Create Exam</h5>
            <div>
                @if (!request()->boolean('open_form'))
                    <a href="{{ route('exams.createexam', ['open_form' => 1]) }}" class="btn btn-primary-fancy btn-sm me-2">
                        Create Exam
                    </a>
                @endif
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
            </div>
        </div>

        <div class="collapse {{ request()->boolean('open_form') ? 'show' : '' }}" id="createExamCollapse">
            <div class="card-body">
            <form action="{{ route('exams.store') }}" method="POST">
                @csrf
                <div class="row g-4">

                    {{-- Exam Name --}}
                    <div class="col-md-6">
                        <label class="form-label">Exam Name</label>
                        <select name="name" class="form-select" required>
                            <option value="">Select Exam Type</option>
                            <option value="Unit Test" {{ old('name') === 'Unit Test' ? 'selected' : '' }}>Unit Test</option>
                            <option value="Mid Term" {{ old('name') === 'Mid Term' ? 'selected' : '' }}>Mid Term</option>
                            <option value="Preliminary Exam" {{ old('name') === 'Preliminary Exam' ? 'selected' : '' }}>Preliminary Exam</option>
                            <option value="Final Exam" {{ old('name') === 'Final Exam' ? 'selected' : '' }}>Final Exam</option>
                        </select>
                    </div>

                    {{-- Academic Year --}}
                    <div class="col-md-6">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select" required>
                            <option value="">Select Academic Year</option>
                            @isset($academicYears)
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            @endisset
                        </select>


                    </div>

                    {{-- Class --}}
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            @isset($classes)
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" data-year="{{ $class->academic_year_id }}">{{ $class->name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    {{-- Section --}}
                    <div class="col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select">
                            <option value="">Select Section</option>
                            @isset($sections)
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" data-class="{{ $section->class_id }}" data-year="{{ $section->class->academic_year_id ?? '' }}">{{ $section->name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                 <div class="col-md-6">
                        <label class="form-label">Subject name</label>
                        <select name="subject_name" class="form-select" required>
                            <option value="">Select Subject name</option>
                            {{-- Subjects will be populated via JavaScript --}}
                        </select>
                    </div>

                    {{-- Start Date --}}
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>

                    {{-- End Date --}}
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>

                    {{-- Time --}}
                    <div class="col-md-6">
                        <label class="form-label">Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Room No</label>
                        <input type="text" name="room_no" class="form-control" placeholder="Enter Room Number" required>
                    </div>

                    {{-- Total Mark --}}
                    <div class="col-md-6">
                        <label class="form-label">Total Marks</label>
                        <input type="number" name="total_mark" class="form-control" value="100" required>
                    </div>

                    {{-- Passing Mark --}}
                    <div class="col-md-6">
                        <label class="form-label">Passing Marks</label>
                        <input type="number" name="passing_mark" class="form-control" value="33" required>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-12">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary-fancy px-5">Create Exam</button>
                </div>

            </form>
        </div>
        </div>
    </div>

    @if (!request()->boolean('open_form'))
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <h5 class="mb-0">Exam List</h5>
                <div class="d-flex gap-2">
                    <select id="exam-list-class-filter" class="form-select form-select-sm" style="min-width: 180px;">
                        <option value="">Class 1</option>
                        @isset($classes)
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                    <select id="exam-list-section-filter" class="form-select form-select-sm" style="min-width: 180px;">
                        <option value="">Section A</option>
                        @isset($sections)
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" data-class="{{ $section->class_id }}">{{ $section->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="exam-list-table" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Exam Name</th>
                            <th>Class & Section</th>
                            <th>Subject Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Time</th>
                            <th>Room No</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.querySelector('select[name="academic_year_id"]');
        const classSelect = document.querySelector('select[name="class_id"]');
        const sectionSelect = document.querySelector('select[name="section_id"]');
        const subjectSelect = document.querySelector('select[name="subject_name"]');
        const sectionSubjects = @json($sectionSubjects ?? []);
        const listClassFilter = document.getElementById('exam-list-class-filter');
        const listSectionFilter = document.getElementById('exam-list-section-filter');
        let examTable = null;

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
            const selectedYear = yearSelect ? yearSelect.value : '';

            if (sectionSelect) {
                Array.from(sectionSelect.options).forEach(option => {
                    if (option.value === "") return;

                    const dataClass = option.getAttribute('data-class');
                    const dataYear = option.getAttribute('data-year');
                    const shouldHide = !selectedClassId || (dataClass !== selectedClassId) || (selectedYear && dataYear && dataYear !== selectedYear);
                    option.hidden = shouldHide;
                    option.style.display = shouldHide ? 'none' : '';
                });

                if (sectionSelect.value && sectionSelect.selectedOptions[0].hidden) {
                    sectionSelect.value = "";
                }

                setAutoSubject();
            }
        }

        function filterListSections() {
            if (!listSectionFilter || !listClassFilter) return;

            const selectedClass = listClassFilter.value;
            Array.from(listSectionFilter.options).forEach(option => {
                if (option.value === '') return;
                const optionClass = option.getAttribute('data-class');
                const hidden = selectedClass && optionClass !== selectedClass;
                option.hidden = hidden;
                option.style.display = hidden ? 'none' : '';
            });

            const currentSection = listSectionFilter.value;
            const selectedOption = listSectionFilter.selectedOptions[0];
            if (currentSection && selectedOption && selectedOption.hidden) {
                listSectionFilter.value = '';
            }
        }

        function applyExamListFilter() {
            if (examTable) {
                examTable.draw();
            }
        }

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

        function initializeFormAutoSelection() {
            if (classSelect && !classSelect.value) {
                selectFirstVisibleOption(classSelect);
            }
            filterSections();
            if (sectionSelect && !sectionSelect.value) {
                selectFirstVisibleOption(sectionSelect);
            }
            setAutoSubject();
        }

        function initializeExamListAutoSelection() {
            if (!listClassFilter || !listSectionFilter) return;

            if (!listClassFilter.value) {
                const classPicked = selectPreferredOptionByText(listClassFilter, ['Class 1']);
                if (!classPicked) {
                    selectFirstVisibleOption(listClassFilter);
                }
            }

            filterListSections();

            if (!listSectionFilter.value) {
                const sectionPicked = selectPreferredOptionByText(listSectionFilter, ['Section A', 'A']);
                if (!sectionPicked) {
                    selectFirstVisibleOption(listSectionFilter);
                }
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

        if (listClassFilter) {
            listClassFilter.addEventListener('change', function() {
                filterListSections();
                applyExamListFilter();
            });
        }

        if (listSectionFilter) {
            listSectionFilter.addEventListener('change', applyExamListFilter);
        }

        if (window.jQuery && $.fn.DataTable) {
            examTable = $('#exam-list-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('exams.data') }}",
                    data: function(d) {
                        d.class_id = listClassFilter ? listClassFilter.value : '';
                        d.section_id = listSectionFilter ? listSectionFilter.value : '';
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'class_section', name: 'class_section', orderable: false, searchable: false },
                    { data: 'subject_name', name: 'subject_name' },
                    { data: 'start_date_display', name: 'start_date' },
                    { data: 'end_date_display', name: 'end_date' },
                    { data: 'time_display', name: 'time_display', orderable: false, searchable: false },
                    { data: 'room_no', name: 'room_no' },
                    { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        }

        filterClasses();
        initializeExamListAutoSelection();
        applyExamListFilter();
        initializeFormAutoSelection();

    });
</script>
@endpush
