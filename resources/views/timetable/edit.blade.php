@extends('layouts.admin')

@section('title', 'Edit Timetable')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Edit Timetable Entry</h5>
                <p class="text-muted small mb-0">Update class timetable details</p>
            </div>
            <div class="d-flex gap-2">
                @can('timetable_delete')
                    <form action="{{ route('timetable.destroy', $timetable->id) }}" method="POST"
                        onsubmit="return confirm('Delete this entry?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-light text-danger">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </form>
                @endcan
                <a href="{{ route('timetable.class') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('timetable.update', $timetable->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label">Entry Type</label>
                            <select name="type" class="form-select" id="tt_entry_type" required>
                                <option value="lecture" {{ ($timetable->type ?? ($timetable->is_break ? 'break' : 'lecture')) === 'lecture' ? 'selected' : '' }}>Lecture</option>
                                <option value="break" {{ ($timetable->type ?? ($timetable->is_break ? 'break' : 'lecture')) === 'break' ? 'selected' : '' }}>Break</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select" id="tt_class_id" required>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" {{ $timetable->class_id == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select name="section_id" class="form-select" id="tt_section_id" required>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" data-class="{{ $section->class_id }}"
                                        {{ $timetable->section_id == $section->id ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3" data-tt-field="lecture">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" class="form-select" id="tt_subject_id">
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="col-md-3" data-tt-field="lecture">
                            <label class="form-label">Teacher (Auto)</label>
                            <input type="text" class="form-control" id="tt_teacher_name" placeholder="Auto assigned" disabled>
                            <input type="hidden" name="teacher_id" id="tt_teacher_id" value="{{ $timetable->teacher_id }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Day</label>
                            <select name="day_of_week" class="form-select" required>
                                @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                    <option value="{{ $day }}" {{ $timetable->day_of_week == $day ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ $timetable->start_time }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="{{ $timetable->end_time }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Room</label>
                            <input type="text" name="room" class="form-control" value="{{ $timetable->room }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select">
                                <option value="">Select Year</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $timetable->academic_year_id == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="0" {{ !$timetable->status ? 'selected' : '' }}>Draft</option>
                                <option value="1" {{ $timetable->status ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary-fancy px-5">Update Timetable</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const typeSelect = document.getElementById('tt_entry_type');
            if (!typeSelect) return;
            const lectureFields = document.querySelectorAll('[data-tt-field="lecture"]');

            function toggleFields() {
                const isLecture = typeSelect.value === 'lecture';
                lectureFields.forEach(field => {
                    field.style.display = isLecture ? '' : 'none';
                });
            }

            typeSelect.addEventListener('change', toggleFields);
            toggleFields();
        })();
    </script>
    <script>
        (function () {
            const classSelect = document.getElementById('tt_class_id');
            const sectionSelect = document.getElementById('tt_section_id');
            const subjectSelect = document.getElementById('tt_subject_id');
            const teacherName = document.getElementById('tt_teacher_name');
            const teacherId = document.getElementById('tt_teacher_id');
            if (!classSelect || !sectionSelect) return;

            const options = Array.from(sectionSelect.options).map(opt => ({
                value: opt.value,
                text: opt.textContent,
                classId: opt.getAttribute('data-class') || '',
            }));

            const mappings = @json($subjectMappings ?? []);
            const subjectsAll = @json($subjects ?? []);
            const currentSubjectId = "{{ $timetable->subject_id }}";
            const currentTeacherName = "{{ $timetable->teacher?->name ?? '' }}";

            function filterSections() {
                const classId = classSelect.value;
                const previousValue = sectionSelect.value;
                sectionSelect.innerHTML = '';

                options
                    .filter(opt => !opt.value || (!classId || opt.classId === classId))
                    .forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.value;
                        option.textContent = opt.text;
                        if (opt.classId) option.setAttribute('data-class', opt.classId);
                        sectionSelect.appendChild(option);
                    });

                const hasPrevious = Array.from(sectionSelect.options).some(o => o.value === previousValue);
                if (!classId) {
                    sectionSelect.value = '';
                } else if (hasPrevious) {
                    sectionSelect.value = previousValue;
                } else {
                    const firstSelectable = Array.from(sectionSelect.options).find(o => o.value);
                    sectionSelect.value = firstSelectable ? firstSelectable.value : '';
                }

                updateSubjects();
            }

            function updateSubjects() {
                if (!subjectSelect) return;
                const classId = classSelect.value;
                const sectionId = sectionSelect.value;
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';

                let filtered = [];
                if (classId && sectionId) {
                    filtered = mappings.filter(m => String(m.class_id) === String(classId)
                        && String(m.section_id) === String(sectionId));
                } else if (classId) {
                    const seen = new Set();
                    mappings.forEach(m => {
                        if (String(m.class_id) === String(classId) && !seen.has(m.subject_id)) {
                            seen.add(m.subject_id);
                            filtered.push(m);
                        }
                    });
                }

                if (!filtered.length && classId) {
                    const fallback = subjectsAll.filter(s => String(s.class_id) === String(classId));
                    filtered = fallback.map(s => ({
                        subject_id: s.id,
                        subject_name: s.name,
                        teacher_id: '',
                        teacher_name: '',
                    }));
                }

                filtered.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.subject_id;
                    opt.textContent = m.subject_name || 'Subject';
                    opt.dataset.teacherId = m.teacher_id || '';
                    opt.dataset.teacherName = m.teacher_name || '';
                    subjectSelect.appendChild(opt);
                });

                if (currentSubjectId && Array.from(subjectSelect.options).some(o => o.value === currentSubjectId)) {
                    subjectSelect.value = currentSubjectId;
                }

                updateTeacher();
            }

            function updateTeacher() {
                if (!teacherName || !teacherId || !subjectSelect) return;
                const selected = subjectSelect.options[subjectSelect.selectedIndex];
                const tId = selected?.dataset?.teacherId || teacherId.value || '';
                const tName = selected?.dataset?.teacherName || currentTeacherName || (sectionSelect.value ? 'Auto assigned' : 'Select section');
                teacherId.value = tId;
                teacherName.value = tName;
            }

            classSelect.addEventListener('change', filterSections);
            sectionSelect.addEventListener('change', updateSubjects);
            subjectSelect?.addEventListener('change', updateTeacher);
            filterSections();
        })();
    </script>
@endpush
