@extends('layouts.admin')

@section('title', 'Exam Marks Entry')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/timetable.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4 tt-skin-classic">
    <div class="tt-header">
        <div class="tt-title">Marks Entry</div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Select Examination Details</h5>
            <form id="marks-filter-form" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">Select Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">Class 1</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select">
                        <option value="">Section A</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" data-class="{{ $section->class_id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" data-class="{{ $subject->class_id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Exam</label>
                    <select name="exam_id" class="form-select">
                        <option value="">Unit Test 1</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}"
                                data-year="{{ $exam->academic_year_id }}"
                                data-class="{{ $exam->class_id }}"
                                data-section="{{ $exam->section_id }}"
                                data-result-declared="{{ (int) ($exam->result_declared ?? 0) }}"
                                data-subject-name="{{ strtolower(trim($exam->subject_name ?? '')) }}">
                                {{ $exam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card d-none" id="marks-entry-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="marks-entry-title">Student Marks</h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary-fancy d-none" id="save-marks-btn"><i class="fa fa-save me-1"></i> Save Marks</button>
                <button type="button" class="btn btn-sm btn-success d-none" id="declare-result-btn"><i class="fa fa-bullhorn me-1"></i> Declare Result</button>
                <button type="button" class="btn btn-sm btn-warning d-none" id="unlock-result-btn"><i class="fa fa-unlock me-1"></i> Unlock Result</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="tt-grid-wrap">
        <table class="tt-grid" id="marks-entry-table">
            <thead>
                <tr>
                    <th class="ps-3" style="width: 100px;">Roll No</th>
                    <th>Student Name</th>
                    <th style="width: 150px;">Total Marks</th>
                    <th style="width: 150px;">Passing Marks</th>
                    <th style="width: 150px;">Obtain Marks</th>
                    <th style="width: 120px;">Percentage</th>
                    <th style="width: 150px;">Grade</th>
                    <th style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody id="students-table-body">
                <!-- Rows will be populated here -->
            </tbody>
        </table>
            </div>
    </div>
    </div>

    <div class="alert alert-info d-none text-center mt-4" id="no-students-alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>No Students Found</strong>
        <p class="mb-0 mt-1">No students are available for selected class, section, and academic year.</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function() {
        const filterForm = document.getElementById('marks-filter-form');
        
        // Main Filters
        const yearSelect = filterForm.querySelector('[name="academic_year_id"]');
        const classSelect = filterForm.querySelector('[name="class_id"]');
        const subjectSelect = filterForm.querySelector('[name="subject_id"]');
        const examSelect = filterForm.querySelector('[name="exam_id"]');
        const sectionSubjects = @json($sectionSubjects ?? []);
        const gradeRules = (@json($grades ?? []))
            .map(rule => ({
                name: String(rule.name ?? '').trim(),
                start: parseFloat(rule.start_mark),
                end: parseFloat(rule.end_mark),
            }))
            .filter(rule => rule.name !== '' && !Number.isNaN(rule.start) && !Number.isNaN(rule.end))
            .sort((a, b) => b.start - a.start);
        let currentExamTotalMarks = 100;
        let currentExamPassingMarks = 33;

        // Existing Marks Logic (using variables already declared above)
        const entryCard = document.getElementById('marks-entry-card');
        const tableBody = document.getElementById('students-table-body');
        const saveBtn = document.getElementById('save-marks-btn');
        const declareBtn = document.getElementById('declare-result-btn');
        const unlockBtn = document.getElementById('unlock-result-btn');
        const sectionSelect = filterForm.querySelector('[name="section_id"]');
        const noStudentsAlert = document.getElementById('no-students-alert');
        const marksEntryTitle = document.getElementById('marks-entry-title');


        function selectFirstVisibleOption(selectElement, preferUndeclaredExam = false) {
            const visibleOptions = Array.from(selectElement.options).filter(opt => opt.value !== '' && !opt.hidden);
            if (!visibleOptions.length) {
                selectElement.value = '';
                return;
            }

            if (preferUndeclaredExam) {
                const firstUndeclared = visibleOptions.find(opt => String(opt.getAttribute('data-result-declared') || '0') !== '1');
                selectElement.value = firstUndeclared ? firstUndeclared.value : visibleOptions[0].value;
                return;
            }

            selectElement.value = visibleOptions[0].value;
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

        function applyFilters(autoSelectDependent = false) {
            const yearId = yearSelect.value;
            const classId = classSelect.value;

            Array.from(sectionSelect.options).forEach(opt => {
                if (opt.value === "") return;
                const sectionClassId = opt.getAttribute('data-class');
                opt.hidden = !!(classId && sectionClassId && sectionClassId !== classId);
            });
            if (sectionSelect.selectedOptions[0] && sectionSelect.selectedOptions[0].hidden) {
                sectionSelect.value = '';
            }
            if (autoSelectDependent && classId && !sectionSelect.value) {
                selectFirstVisibleOption(sectionSelect);
            }

            const selectedSectionId = sectionSelect.value;
            const mappedSubjectIds = sectionSubjects
                .filter(item =>
                    String(item.class_id) === String(classId) &&
                    String(item.section_id) === String(selectedSectionId)
                )
                .map(item => String(item.subject_id));

            Array.from(subjectSelect.options).forEach(opt => {
                if (opt.value === "") return;
                const subjectClassId = opt.getAttribute('data-class');
                const classMismatch = !!(classId && subjectClassId && subjectClassId !== classId);
                // Strict mapping: only show subjects mapped to selected class + section.
                const notMappedForSection = !!(
                    selectedSectionId && 
                    !mappedSubjectIds.includes(String(opt.value))
                );
                opt.hidden = classMismatch || notMappedForSection;
            });
            if (subjectSelect.selectedOptions[0] && subjectSelect.selectedOptions[0].hidden) {
                subjectSelect.value = '';
            }
            if (classId && sectionSelect.value && !subjectSelect.value) {
                selectFirstVisibleOption(subjectSelect);
            }

            const subjectId = subjectSelect.value;
            const selectedSubjectName = subjectSelect.selectedOptions[0]
                ? subjectSelect.selectedOptions[0].text.trim().toLowerCase()
                : '';

            Array.from(examSelect.options).forEach(opt => {
                if (opt.value === "") return;
                
                const eYear = opt.getAttribute('data-year');
                const eClass = opt.getAttribute('data-class');
                const eSection = opt.getAttribute('data-section');
                const eSubjectName = (opt.getAttribute('data-subject-name') || '').trim().toLowerCase();

                let show = true;
                
                // Only filter if a value is selected in the parent dropdown
                if (yearId && eYear && eYear != yearId) show = false;
                if (classId && eClass && eClass != classId) show = false;
                if (selectedSectionId && eSection && eSection !== selectedSectionId) show = false;
                if (subjectId && eSubjectName && selectedSubjectName && eSubjectName !== selectedSubjectName) show = false;
                if (subjectId && !eSubjectName) show = false;
                
                opt.hidden = !show;
            });
            if (examSelect.selectedOptions[0] && examSelect.selectedOptions[0].hidden) {
                examSelect.value = '';
            }
            if (classId && sectionSelect.value && subjectId && !examSelect.value) {
                selectFirstVisibleOption(examSelect, true);
            }
        }

        function hasAllRequiredFilters() {
            return !!(
                yearSelect.value &&
                classSelect.value &&
                sectionSelect.value &&
                subjectSelect.value &&
                examSelect.value
            );
        }

        function loadStudents() {
            applyFilters(false);

            if (!hasAllRequiredFilters()) {
                noStudentsAlert.classList.add('d-none');
                entryCard.classList.add('d-none');
                saveBtn.classList.add('d-none');
                declareBtn.classList.add('d-none');
                unlockBtn.classList.add('d-none');
                return;
            }

            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();

            fetch(`{{ route('exams.marks.data') }}?${params}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(async (response) => {
                    let payload = null;
                    try {
                        payload = await response.json();
                    } catch (e) {
                        payload = null;
                    }

                    if (!response.ok) {
                        const msg = payload?.message
                            || payload?.error
                            || 'Failed to load students.';
                        throw new Error(msg);
                    }

                    return payload ?? {};
                })
                .then(data => {
                    if (!data.students || data.students.length === 0) {
                        entryCard.classList.add('d-none');
                        noStudentsAlert.classList.remove('d-none');
                        saveBtn.classList.add('d-none');
                        declareBtn.classList.add('d-none');
                        unlockBtn.classList.add('d-none');
                    } else {
                        noStudentsAlert.classList.add('d-none');
                        entryCard.classList.remove('d-none');

                        const selectedExamOption = examSelect.options[examSelect.selectedIndex];
                        const exam = @json($exams).find(e => e.id == selectedExamOption.value);
                        currentExamTotalMarks = exam ? exam.total_mark : 100;
                        currentExamPassingMarks = exam ? exam.passing_mark : 33;

                        // Update title
                        const classText = classSelect.options[classSelect.selectedIndex].text;
                        const sectionText = sectionSelect.options[sectionSelect.selectedIndex].text;
                        const examText = examSelect.options[examSelect.selectedIndex].text;
                        if (marksEntryTitle) {
                            marksEntryTitle.textContent = `Marks for ${examText} | ${classText} - ${sectionText} | Total: ${currentExamTotalMarks}`;
                        }


                        renderTable(data.students, data.is_result_declared);
                        
                        if (data.is_result_declared) {
                            saveBtn.classList.add('d-none');
                            declareBtn.classList.add('d-none');
                            unlockBtn.classList.remove('d-none');
                        } else {
                            saveBtn.classList.remove('d-none');
                            declareBtn.classList.remove('d-none');
                            unlockBtn.classList.add('d-none');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || 'Failed to load students.');
                    noStudentsAlert.classList.add('d-none');
                    entryCard.classList.add('d-none');
                    saveBtn.classList.add('d-none');
                    declareBtn.classList.add('d-none');
                    unlockBtn.classList.add('d-none');
                });
        }

        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loadStudents.call(filterForm);
        });

        yearSelect.addEventListener('change', function () {
            applyFilters(true);
            loadStudents.call(filterForm);
        });

        classSelect.addEventListener('change', function () {
            applyFilters(true);
            loadStudents.call(filterForm);
        });

        sectionSelect.addEventListener('change', function () {
            applyFilters(true);
            loadStudents.call(filterForm);
        });

        subjectSelect.addEventListener('change', function () {
            applyFilters(false);
            loadStudents.call(filterForm);
        });

        examSelect.addEventListener('change', function () {
            loadStudents.call(filterForm);
        });

        function selectFirstIfEmpty(selectElement) {
            if (!selectElement || selectElement.value) return;
            selectFirstVisibleOption(selectElement);
        }

        function initializeAutoLoad() {
            selectFirstIfEmpty(yearSelect);
            if (!classSelect.value) {
                const classPicked = selectPreferredOptionByText(classSelect, ['Class 1']);
                if (!classPicked) {
                    selectFirstVisibleOption(classSelect);
                }
            }

            applyFilters(true);
            if (!sectionSelect.value) {
                const sectionPicked = selectPreferredOptionByText(sectionSelect, ['Section A', 'A']);
                if (!sectionPicked) {
                    selectFirstVisibleOption(sectionSelect);
                }
            }

            applyFilters(true);
            selectFirstIfEmpty(subjectSelect);

            applyFilters(false);
            if (!examSelect.value) {
                selectFirstVisibleOption(examSelect, true);
            }

            loadStudents.call(filterForm);
        }

        function renderTable(students, isDeclared = false) {
            tableBody.innerHTML = '';

            const readonlyClass = isDeclared ? 'form-control-plaintext' : 'form-control';
            // Keep row inputs locked by default; Edit button unlocks marks input only.
            const defaultRowDisabled = 'disabled';


            students.forEach(student => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-3">${student.roll_no || '-'}</td>
                    <td>${student.student_name}</td>
                    <td class="text-center">${currentExamTotalMarks}</td>
                    <td class="text-center">${currentExamPassingMarks}</td>
                    <td>
                        <input type="number" class="${readonlyClass}" name="marks_obtained" value="${student.marks_obtained || ''}" step="0.01" min="0" max="${currentExamTotalMarks}" ${defaultRowDisabled}>
                        <input type="hidden" name="student_id" value="${student.student_id}">
                        <input type="hidden" name="mark_id" value="${student.mark_id || ''}">
                    </td>
                    <td class="text-center"><span class="percentage-display"></span></td>
                    <td>
                        <input type="text" class="${readonlyClass} form-control-sm" name="grade" value="${student.grade || ''}" readonly disabled>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-primary edit-mark-btn" title="Edit" ${isDeclared ? 'disabled' : ''}>
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-mark-btn" title="Delete" ${isDeclared ? 'disabled' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            // Add event listeners for percentage calculation
            tableBody.querySelectorAll('input[name="marks_obtained"]').forEach(input => {
                // Calculate on initial render
                calculatePercentageAndGrade(input);

                // Calculate on input change
                input.addEventListener('input', () => calculatePercentageAndGrade(input));
            });

            tableBody.querySelectorAll('.edit-mark-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = btn.closest('tr');
                    if (!row) return;
                    const marksInput = row.querySelector('[name="marks_obtained"]');
                    const isEditing = btn.getAttribute('data-editing') === '1';

                    if (!isEditing) {
                        if (marksInput) {
                            marksInput.removeAttribute('disabled');
                        }
                        btn.setAttribute('data-editing', '1');
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-secondary');
                        btn.innerHTML = '<i class="fas fa-check"></i>';
                        btn.setAttribute('title', 'Lock Row');
                        if (marksInput) {
                            marksInput.focus();
                        }
                    } else {
                        if (marksInput) {
                            marksInput.setAttribute('disabled', 'disabled');
                        }
                        btn.setAttribute('data-editing', '0');
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-primary');
                        btn.innerHTML = '<i class="fas fa-pen"></i>';
                        btn.setAttribute('title', 'Edit');
                    }
                });
            });

            tableBody.querySelectorAll('.delete-mark-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = btn.closest('tr');
                    if (!row) return;
                    const markId = row.querySelector('[name="mark_id"]')?.value || '';

                    if (!markId) {
                        row.remove();
                        return;
                    }

                    if (!confirm('Delete this mark entry?')) {
                        return;
                    }

                    fetch(`{{ url('admin/exams/marks') }}/${markId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(async response => {
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload?.message || 'Failed to delete mark.');
                        }
                        return payload;
                    })
                    .then(result => {
                        alert(result.message || 'Mark deleted successfully.');
                        loadStudents.call(filterForm);
                    })
                    .catch(error => {
                        alert(error.message || 'Failed to delete mark.');
                    });
                });
            });
        }

        function resolveGrade(percentage) {
            if (Number.isNaN(percentage) || percentage < 0) {
                return '';
            }
            if (percentage < 50) {
                return 'F';
            }

            // First try exact decimal match, then whole-number fallback
            // so ranges like 80-89 still match 89.50.
            let matched = gradeRules.find(rule => percentage >= rule.start && percentage <= rule.end);
            if (!matched) {
                const rounded = Math.round(percentage);
                matched = gradeRules.find(rule => rounded >= rule.start && rounded <= rule.end);
            }
            if (!matched) {
                return '';
            }
            return String(matched.name || '').trim().toUpperCase() === 'E' ? 'F' : matched.name;
        }

        function calculatePercentageAndGrade(marksInput) {
            const marks = parseFloat(marksInput.value);
            const row = marksInput.closest('tr');
            const percentageSpan = row.querySelector('.percentage-display');
            const gradeInput = row.querySelector('[name="grade"]');
            if (!percentageSpan) return;

            // Step 1: calculate percentage from entered marks.
            if (!isNaN(marks) && currentExamTotalMarks > 0) {
                const percentage = Number(((marks / currentExamTotalMarks) * 100).toFixed(2));
                percentageSpan.textContent = `${percentage.toFixed(2)}%`;
                // Step 2: derive grade from calculated percentage.
                if (gradeInput) {
                    gradeInput.value = resolveGrade(percentage);
                }
            } else {
                percentageSpan.textContent = '-';
                if (gradeInput) {
                    gradeInput.value = '';
                }
            }
        }

        saveBtn.addEventListener('click', function() {
            const rows = tableBody.querySelectorAll('tr');
            const data = {
                academic_year_id: yearSelect.value,
                class_id: classSelect.value,
                section_id: sectionSelect.value,
                subject_id: subjectSelect.value,
                exam_id: examSelect.value,
                marks: []
            };

            rows.forEach(row => {
                const studentId = row.querySelector('[name="student_id"]').value;
                const marksObtained = row.querySelector('[name="marks_obtained"]').value;
                const grade = row.querySelector('[name="grade"]').value;
               

                if (marksObtained !== '') {
                    data.marks.push({
                        student_id: studentId,
                        marks_obtained: marksObtained,
                        grade: grade,
                       
                    });
                }
            });

            if (data.marks.length === 0) {
                 alert('No marks entered to save.');
                 return;
            }

            fetch(`{{ route('exams.marks.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    loadStudents.call(filterForm);
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                let msg = 'An error occurred while saving marks.';
                if (error.message) msg += '\n' + error.message;
                if (error.errors) {
                    msg += '\n' + Object.values(error.errors).flat().join('\n');
                }
                alert(msg);
            });
        });

        declareBtn.addEventListener('click', function() {
            const examId = examSelect.value;
            if (!examId) {
                alert('Please select an exam first.');
                return;
            }

            if (!confirm('Are you sure you want to declare the result for this exam? This action cannot be undone and will lock marks entry.')) {
                return;
            }

            fetch(`{{ url('admin/exams/declare-result') }}/${examId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const payload = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(payload?.message || 'Failed to declare result.');
                }
                return payload;
            })
            .then(result => {
                alert(result.message || 'Result declared successfully.');
                if (result.success) filterForm.dispatchEvent(new Event('submit'));
            })
            .catch(err => alert(err.message || 'An error occurred.'));
        });

        unlockBtn.addEventListener('click', function() {
            const examId = examSelect.value;
            if (!examId) {
                alert('Please select an exam first.');
                return;
            }

            if (!confirm('Unlock this result? Marks entry will become editable again.')) {
                return;
            }

            fetch(`{{ url('admin/exams/undeclare-result') }}/${examId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const payload = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(payload?.message || 'Failed to unlock result.');
                }
                return payload;
            })
            .then(result => {
                alert(result.message || 'Result unlocked successfully.');
                if (result.success) {
                    loadStudents.call(filterForm);
                }
            })
            .catch(err => alert(err.message || 'An error occurred.'));
        });

        initializeAutoLoad();
    })();
</script>
@endpush
