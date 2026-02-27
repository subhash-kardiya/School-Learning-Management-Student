@extends('layouts.admin')

@section('title', 'Edit Timetable')

@section('content')
    <div class="container-fluid py-4 timetable-module-compact">
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
                <form action="{{ route('timetable.update', $timetable->id) }}" method="POST" id="tt-edit-form">
                    @csrf
                    @method('PUT')


                    @error('schedule')
                        <div class="alert alert-danger border-0 shadow-sm mb-3">{{ $message }}</div>
                    @enderror

                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label">Entry Type</label>
                            <select name="type" class="form-select" id="tt_entry_type">
                                <option value="lecture"
                                    {{ old('type', $timetable->type ?? ($timetable->is_break ? 'break' : 'lecture')) === 'lecture' ? 'selected' : '' }}>
                                    Lecture</option>
                                <option value="break"
                                    {{ old('type', $timetable->type ?? ($timetable->is_break ? 'break' : 'lecture')) === 'break' ? 'selected' : '' }}>
                                    Break</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select @error('class_id') is-invalid @enderror"
                                id="tt_class_id">
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ (int) old('class_id', $timetable->class_id) === (int) $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select name="section_id" class="form-select @error('section_id') is-invalid @enderror"
                                id="tt_section_id">
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" data-class="{{ $section->class_id }}"
                                        {{ (int) old('section_id', $timetable->section_id) === (int) $section->id ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('section_id')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3" data-tt-field="lecture">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror"
                                id="tt_subject_id">
                                <option value="">Select Subject</option>
                            </select>
                            @error('subject_id')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3" data-tt-field="lecture">
                            <label class="form-label">Teacher (Auto)</label>
                            <input type="text" class="form-control" id="tt_teacher_name" placeholder="Auto assigned"
                                disabled>
                            <input type="hidden" name="teacher_id" id="tt_teacher_id"
                                value="{{ $timetable->teacher_id }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Day</label>
                            <select name="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror">
                                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                    <option value="{{ $day }}"
                                        {{ old('day_of_week', $timetable->day_of_week) == $day ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                            @error('day_of_week')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time"
                                class="form-control @error('start_time') is-invalid @enderror"
                                value="{{ old('start_time', $timetable->start_time) }}">
                            @error('start_time')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time"
                                class="form-control @error('end_time') is-invalid @enderror"
                                value="{{ old('end_time', $timetable->end_time) }}">
                            @error('end_time')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Room</label>
                            <input type="text" name="room" id="tt_room"
                                class="form-control @error('room') is-invalid @enderror"
                                value="{{ old('room', $timetable->room) }}" readonly>
                            <small class="text-muted">Auto fetched from Class Mapping (Class + Section).</small>
                            @error('room')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id"
                                class="form-select @error('academic_year_id') is-invalid @enderror">
                                <option value="">Select Year</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ (int) old('academic_year_id', $timetable->academic_year_id) === (int) $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="0"
                                    {{ (string) old('status', (string) $timetable->status) === '0' ? 'selected' : '' }}>
                                    Draft</option>
                                <option value="1"
                                    {{ (string) old('status', (string) $timetable->status) === '1' ? 'selected' : '' }}>
                                    Published</option>
                            </select>
                            @error('status')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
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
        (function() {
            const form = document.getElementById('tt-edit-form');
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

            form?.addEventListener('submit', function(e) {
                $('.client-error').remove();
                let hasError = false;
                const fields = ['class_id', 'section_id', 'subject_id', 'day_of_week', 'start_time', 'end_time',
                    'status'
                ];
                fields.forEach(function(name) {
                    const $field = $('[name="' + name + '"]');
                    if (!$field.length) return;
                    if (String($field.val() || '').trim() === '') {
                        hasError = true;
                        $field.addClass('is-invalid');
                        $field.after(
                            '<span class="text-danger d-block client-error">This field is required.</span>'
                            );
                    } else {
                        $field.removeClass('is-invalid');
                    }
                });
                if (hasError) e.preventDefault();
            });
        })();
    </script>
    <script>
        (function() {
            const classSelect = document.getElementById('tt_class_id');
            const sectionSelect = document.getElementById('tt_section_id');
            const subjectSelect = document.getElementById('tt_subject_id');
            const teacherName = document.getElementById('tt_teacher_name');
            const teacherId = document.getElementById('tt_teacher_id');
            const roomInput = document.getElementById('tt_room');
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
                    filtered = mappings.filter(m => String(m.class_id) === String(classId) &&
                        String(m.section_id) === String(sectionId));
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
                updateRoom();
            }

            function updateTeacher() {
                if (!teacherName || !teacherId || !subjectSelect) return;
                const selected = subjectSelect.options[subjectSelect.selectedIndex];
                const tId = selected?.dataset?.teacherId || teacherId.value || '';
                const tName = selected?.dataset?.teacherName || currentTeacherName || (sectionSelect.value ?
                    'Auto assigned' : 'Select section');
                teacherId.value = tId;
                teacherName.value = tName;
            }

            function updateRoom() {
                if (!roomInput) return;
                const classId = classSelect.value;
                const sectionId = sectionSelect.value;
                if (!classId || !sectionId) {
                    roomInput.value = '';
                    return;
                }

                const mapBySection = mappings.find(m =>
                    String(m.section_id) === String(sectionId) &&
                    String(m.class_id) === String(classId) &&
                    m.room
                );

                roomInput.value = mapBySection?.room || '';
            }

            classSelect.addEventListener('change', filterSections);
            sectionSelect.addEventListener('change', updateSubjects);
            classSelect.addEventListener('change', updateRoom);
            sectionSelect.addEventListener('change', updateRoom);
            subjectSelect?.addEventListener('change', updateTeacher);
            filterSections();
        })();
    </script>
@endpush
