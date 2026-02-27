@php
    $selectedClassId = old('class_id');
    if (!$selectedClassId && isset($mapping) && $mapping->section) {
        $selectedClassId = $mapping->section->class_id;
    }
@endphp

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <label for="teacher_id" class="form-label">Teacher</label>
        <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
            <option value="">Select Teacher</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ (string) old('teacher_id', $mapping->teacher_id ?? '') === (string) $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
        @error('teacher_id')
            <span class="text-danger d-block">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="class_id" class="form-label">Class</label>
        <select name="class_id" id="class_id" class="form-select">
            <option value="">Select Class</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" {{ (string) $selectedClassId === (string) $class->id ? 'selected' : '' }}>
                    {{ $class->academicYear?->name }} - {{ $class->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="section_id" class="form-label">Section</label>
        <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" required>
            <option value="">Select Section</option>
            @foreach ($classes as $class)
                @foreach ($class->sections as $section)
                    <option value="{{ $section->id }}" data-class-id="{{ $class->id }}"
                        {{ (string) old('section_id', $mapping->section_id ?? '') === (string) $section->id ? 'selected' : '' }}>
                        {{ $section->name }}
                    </option>
                @endforeach
            @endforeach
        </select>
        @error('section_id')
            <span class="text-danger d-block">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="subject_id" class="form-label">Subject</label>
        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
            <option value="">Select Subject</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" data-class-id="{{ $subject->class_id }}"
                    {{ (string) old('subject_id', $mapping->subject_id ?? '') === (string) $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }} ({{ $subject->subject_code }})
                </option>
            @endforeach
        </select>
        @error('subject_id')
            <span class="text-danger d-block">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="room_id" class="form-label">Room</label>
        <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
            <option value="">Select Room</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" {{ (string) old('room_id', $mapping->room_id ?? '') === (string) $room->id ? 'selected' : '' }}>
                    {{ $room->name }}{{ $room->capacity ? ' (Capacity: ' . $room->capacity . ')' : '' }}
                </option>
            @endforeach
        </select>
        @error('room_id')
            <span class="text-danger d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const $class = $('#class_id');
            const $section = $('#section_id');
            const $subject = $('#subject_id');
            const sectionOptions = $section.find('option').clone();
            const subjectOptions = $subject.find('option').clone();

            function filterSections() {
                const classId = String($class.val() || '');
                const current = String($section.val() || '');
                $section.empty().append('<option value="">Select Section</option>');

                sectionOptions.each(function() {
                    const value = $(this).attr('value');
                    if (!value) return;
                    const optClass = String($(this).data('class-id') || '');
                    if (!classId || classId === optClass) {
                        $section.append($(this).clone());
                    }
                });

                if (current && $section.find('option[value="' + current + '"]').length) {
                    $section.val(current);
                } else if (classId) {
                    $section.val('');
                }
            }

            function filterSubjects() {
                const classId = String($class.val() || '');
                const current = String($subject.val() || '');
                $subject.empty().append('<option value="">Select Subject</option>');

                subjectOptions.each(function() {
                    const value = $(this).attr('value');
                    if (!value) return;
                    const optClass = String($(this).data('class-id') || '');
                    if (!classId || classId === optClass) {
                        $subject.append($(this).clone());
                    }
                });

                if (current && $subject.find('option[value="' + current + '"]').length) {
                    $subject.val(current);
                } else if (classId) {
                    $subject.val('');
                }
            }

            $class.on('change', function() {
                filterSections();
                filterSubjects();
            });

            filterSections();
            filterSubjects();
        })();
    </script>
@endpush
