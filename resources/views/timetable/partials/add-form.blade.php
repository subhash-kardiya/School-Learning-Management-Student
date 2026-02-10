<div class="tt-add-panel">
    <div class="p-2">
        <form action="{{ route('timetable.class.store') }}" method="POST" id="tt-entry-form" data-create-url="{{ route('timetable.class.store') }}">
            @csrf
            <input type="hidden" name="_method" id="tt_form_method" value="POST">
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
                <div class="col-md-4">
                    <label class="form-label">Slot</label>
                    <select class="form-select" id="tt_slot_select">
                        <option value="">Select Slot</option>
                        @foreach (($timeSlots ?? []) as $slot)
                            <option value="{{ $slot[0] }}-{{ $slot[1] }}">{{ $slot[0] }} to {{ $slot[1] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4" data-tt-field="lecture">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select" id="tt_subject_id">
                        <option value="">Select Subject</option>
                    </select>
                </div>
                <div class="col-md-4" data-tt-field="lecture">
                    <label class="form-label">Teacher (Auto)</label>
                    <input type="text" class="form-control" id="tt_teacher_name" placeholder="Auto assigned" disabled>
                    <input type="hidden" name="teacher_id" id="tt_teacher_id">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Room Number</label>
                    <input type="text" name="room" class="form-control" id="tt_room" placeholder="e.g. 101">
                </div>
                <input type="hidden" name="class_id" id="tt_class_id">
                <input type="hidden" name="section_id" id="tt_section_id">
                <input type="hidden" name="academic_year_id" id="tt_academic_year_id">
                <input type="hidden" name="day_of_week" id="tt_day_of_week">
                <input type="hidden" name="start_time" id="tt_start_time">
                <input type="hidden" name="end_time" id="tt_end_time">
                <input type="hidden" name="type" value="lecture">
                <input type="hidden" name="status" value="1">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary-fancy px-5" id="tt-form-submit">Save Entry</button>
            </div>
        </form>
        <div class="d-flex justify-content-end mt-3">
            <form action="" method="POST" id="tt-delete-form">
                @csrf
                <input type="hidden" name="_method" id="tt_delete_method" value="DELETE">
                <button type="submit" class="btn btn-outline-danger px-4 d-none" id="tt-form-delete"
                    onclick="return confirm('Delete this entry?')">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const classInput = document.getElementById('tt_class_id');
            const sectionInput = document.getElementById('tt_section_id');
            const subjectSelect = document.getElementById('tt_subject_id');
            const teacherName = document.getElementById('tt_teacher_name');
            const teacherId = document.getElementById('tt_teacher_id');
            const slotSelect = document.getElementById('tt_slot_select');
            const startInput = document.getElementById('tt_start_time');
            const endInput = document.getElementById('tt_end_time');
            if (!classInput || !sectionInput) return;

            const mappings = @json($subjectMappings ?? []);
            const subjectsAll = @json($subjects ?? []);

            function updateSubjects() {
                if (!subjectSelect) return;
                const classId = classInput.value;
                const sectionId = sectionInput.value;
                const current = subjectSelect.value;
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';

                let filtered = [];
                if (sectionId) {
                    const seen = new Set();
                    // Prefer section-specific mappings
                    mappings.forEach(m => {
                        if (String(m.section_id) !== String(sectionId)) return;
                        if (classId && String(m.class_id) !== String(classId)) return;
                        if (seen.has(m.subject_id)) return;
                        seen.add(m.subject_id);
                        filtered.push(m);
                    });
                    // Also include any class-level mappings not already shown
                    if (classId) {
                        mappings.forEach(m => {
                            if (String(m.class_id) !== String(classId)) return;
                            if (seen.has(m.subject_id)) return;
                            seen.add(m.subject_id);
                            filtered.push(m);
                        });
                    }
                } else if (classId) {
                    // Show subjects for class if section not chosen yet
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

                if (current && Array.from(subjectSelect.options).some(o => o.value === current)) {
                    subjectSelect.value = current;
                }

                updateTeacher();
            }

            function updateTeacher() {
                if (!teacherName || !teacherId || !subjectSelect) return;
                const selected = subjectSelect.options[subjectSelect.selectedIndex];
                const tId = selected?.dataset?.teacherId || '';
                const tName = selected?.dataset?.teacherName || '';
                teacherId.value = tId;
                teacherName.value = tName || (sectionInput.value ? 'Auto assigned' : 'Select section');
            }

            classInput.addEventListener('change', updateSubjects);
            sectionInput.addEventListener('change', updateSubjects);
            subjectSelect?.addEventListener('change', updateTeacher);
            updateSubjects();

            if (slotSelect && startInput && endInput) {
                slotSelect.addEventListener('change', () => {
                    const value = slotSelect.value || '';
                    const parts = value.split('-');
                    if (parts.length === 2) {
                        startInput.value = parts[0];
                        endInput.value = parts[1];
                    }
                });
            }
        })();
    </script>
@endpush
