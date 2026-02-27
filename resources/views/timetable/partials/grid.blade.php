@php
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $config = $config ?? [];
    $filters = $filters ?? [];
    $breakSlots = $breakSlots ?? [];
    $timeSlots = $config['timeSlots'] ?? [];
    if (empty($timeSlots)) {
        $start = strtotime('08:00');
        $end = strtotime('16:00');
        $step = 60 * 60;
        for ($t = $start; $t < $end; $t += $step) {
            $timeSlots[] = [date('H:i', $t), date('H:i', $t + $step)];
        }
    }
@endphp

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/timetable-compact.css') }}">
@endpush

<div class="tt-print-area">
    <div class="tt-header">
        <div class="tt-title">{{ $config['title'] ?? 'Timetable' }}</div>
        <div class="tt-controls">
            @if (!empty($filters['classes']) && ($config['showClass'] ?? false))
                <select class="form-select" name="class_id" form="tt-filters">
                    <option value="">All Classes</option>
                    @foreach ($filters['classes'] as $class)
                        <option value="{{ $class->id }}" {{ ($class->name === 'Class 1' || (int) $class->id === 1) ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            @endif
            @if (!empty($filters['sections']) && ($config['showSection'] ?? false))
                <select class="form-select" name="section_id" form="tt-filters">
                    <option value="">All Sections</option>
                    @foreach ($filters['sections'] as $section)
                        <option value="{{ $section->id }}" data-class="{{ $section->class_id }}"
                            {{ ($section->name === 'A' || (int) $section->id === 1) ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            @endif
            @if (!empty($filters['teachers']) && ($config['showTeacher'] ?? false))
                <select class="form-select" name="teacher_id" form="tt-filters">
                    <option value="">All Teachers</option>
                    @foreach ($filters['teachers'] as $teacher)
                        <option value="{{ $teacher->id }}"
                            {{ (!empty($config['defaultTeacherId']) && (int) $teacher->id === (int) $config['defaultTeacherId']) ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            @endif
            @if (!empty($filters['students']) && ($config['showStudent'] ?? false))
                <select class="form-select" name="student_id" form="tt-filters">
                    @foreach ($filters['students'] as $student)
                        <option value="{{ $student->id }}" {{ ($filters['selectedStudentId'] ?? null) == $student->id ? 'selected' : '' }}>
                            {{ $student->student_name }}
                        </option>
                    @endforeach
                </select>
            @endif
            @if (!empty(session('selected_academic_year_id')))
                <input type="hidden" name="academic_year_id" value="{{ session('selected_academic_year_id') }}" form="tt-filters">
            @endif

            @if (!empty($config['addPanelId']))
                <button type="button" class="btn btn-primary-fancy" id="tt-add-entry-btn" data-bs-toggle="modal" data-bs-target="#{{ $config['addPanelId'] }}">
                    <i class="fa fa-plus me-1"></i> Add Entry
                </button>
            @endif
            @if ($config['showExport'] ?? true)
                <button type="button" class="btn btn-primary-fancy" id="tt-print">PDF / Print</button>
            @endif
        </div>
    </div>

    <div class="tt-tabs" id="tt-tabs"></div>

    <form id="tt-filters"></form>

    <div class="tt-grid-wrap">
    <table class="tt-grid" id="tt-grid" data-url="{{ $dataUrl }}"
        data-enable-details="{{ ($config['enableDetails'] ?? true) ? '1' : '0' }}"
        data-allow-cell-click="{{ ($config['enableCellClick'] ?? false) ? '1' : '0' }}"
        data-add-panel-id="{{ $config['addPanelId'] ?? '' }}"
        data-current-teacher-id="{{ $config['currentTeacherId'] ?? '' }}"
        data-time-slots='@json($timeSlots)'>
            <thead>
                <tr>
                    <th style="width: 110px;">Time</th>
                    @foreach ($days as $day)
                        <th data-day="{{ $day }}">{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody id="tt-grid-body">
                <tr>
                    <td colspan="{{ 1 + count($days) }}">
                        <div class="tt-skeleton"></div>
                        <div class="tt-skeleton"></div>
                        <div class="tt-skeleton" style="width: 70%;"></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="tt-details-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Entry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="tt-details-body"></div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const grid = document.getElementById('tt-grid');
            if (!grid) return;

            const body = document.getElementById('tt-grid-body');
            const printBtn = document.getElementById('tt-print');
            const resetBtn = document.getElementById('tt-reset');
            const findBtn = document.getElementById('tt-find');
            const form = document.getElementById('tt-filters');
            const dataUrl = grid.dataset.url;
            let days = @json($days);
            const tabs = document.getElementById('tt-tabs');
            const detailsBody = document.getElementById('tt-details-body');
            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');
            const enableDetails = grid.dataset.enableDetails === '1';
            const allowCellClick = grid.dataset.allowCellClick === '1';
            const addPanelId = grid.dataset.addPanelId || '';
            const currentTeacherId = grid.dataset.currentTeacherId || '';
            let currentSlots = [];
            const getFilterEl = (name) => document.querySelector(`[form="tt-filters"][name="${name}"]`)
                || (form ? form.querySelector(`[name="${name}"]`) : null);
            const classSelect = getFilterEl('class_id');
            const sectionSelect = getFilterEl('section_id');
            const teacherSelect = getFilterEl('teacher_id');
            const studentSelect = getFilterEl('student_id');
            const yearSelect = getFilterEl('academic_year_id');
            const addBtn = document.getElementById('tt-add-entry-btn');
            const sectionOptions = sectionSelect
                ? Array.from(sectionSelect.options).map(opt => ({
                    value: opt.value,
                    text: opt.textContent,
                    classId: opt.getAttribute('data-class') || '',
                    selected: opt.selected
                }))
                : [];

            let activeDay = null;
            const storageKey = `tt-filters-${dataUrl}`;

            function minutes(value) {
                if (!value) return 0;
                const parts = value.split(':').map(Number);
                return (parts[0] || 0) * 60 + (parts[1] || 0);
            }

            function formatTime(value) {
                if (!value) return '';
                const parts = value.split(':').map(Number);
                const h = String(parts[0] || 0).padStart(2, '0');
                const m = String(parts[1] || 0).padStart(2, '0');
                return `${h}:${m}`;
            }

            function colorFor(id) {
                const num = parseInt(id || 1, 10);
                const hue = (num * 47) % 360;
                return `hsl(${hue} 75% 90%)`;
            }

            function buildTabs() {
                if (!tabs) return;
                tabs.innerHTML = days.map(day => {
                    return `<button type="button" class="tt-tab" data-day="${day}">${day.slice(0,3)}</button>`;
                }).join('');

                tabs.addEventListener('click', (e) => {
                    const btn = e.target.closest('.tt-tab');
                    if (!btn) return;
                    activeDay = btn.dataset.day;
                    tabs.querySelectorAll('.tt-tab').forEach(el => el.classList.remove('active'));
                    btn.classList.add('active');
                    applyDayFilter();
                });
            }

            function applyDayFilter() {
                const ths = grid.querySelectorAll('thead th[data-day]');
                const rows = grid.querySelectorAll('tbody tr');
                ths.forEach(th => {
                    const show = !activeDay || th.dataset.day === activeDay;
                    th.style.display = show ? '' : 'none';
                });
                rows.forEach(row => {
                    row.querySelectorAll('td[data-day]').forEach(td => {
                        const show = !activeDay || td.dataset.day === activeDay;
                        td.style.display = show ? '' : 'none';
                    });
                });
            }

            function conflictMap(entries) {
                const conflicts = new Set();
                entries.forEach((a, i) => {
                    entries.slice(i + 1).forEach(b => {
                        if (a.day_of_week !== b.day_of_week) return;
                        if (a.type === 'break' || b.type === 'break') return;
                        const overlap = minutes(a.start_time) < minutes(b.end_time) && minutes(a.end_time) > minutes(b.start_time);
                        if (!overlap) return;
                        if (a.teacher_id === b.teacher_id || (a.room && b.room && a.room === b.room)) {
                            conflicts.add(a.id);
                            conflicts.add(b.id);
                        }
                    });
                });
                return conflicts;
            }

            function renderSlotOptions(slots) {
                const slotSelect = document.getElementById('tt_slot_select');
                if (!slotSelect) return;
                const current = slotSelect.value;
                slotSelect.innerHTML = '<option value="">Select Slot</option>';
                slots.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = `${s.start}-${s.end}`;
                    const label = s.type === 'break' ? 'Break' : (s.type === 'lunch' ? 'Lunch' : 'Period');
                    opt.textContent = `${s.start} to ${s.end} (${label})`;
                    slotSelect.appendChild(opt);
                });
                if (current && Array.from(slotSelect.options).some(o => o.value === current)) {
                    slotSelect.value = current;
                }
            }

            function updateHeader() {
                const thead = grid.querySelector('thead');
                if (!thead) return;
                thead.innerHTML = `
                    <tr>
                        <th style="width: 110px;">Time</th>
                        ${days.map(day => `<th data-day="${day}">${day}</th>`).join('')}
                    </tr>
                `;
            }

            function render(entries) {
                if (!currentSlots || currentSlots.length === 0) {
                    body.innerHTML = `<tr><td colspan="${1 + days.length}" class="tt-empty">No time slots configured.</td></tr>`;
                    return;
                }

                renderSlotOptions(currentSlots);

                const slotMap = new Map();
                entries.forEach(e => {
                    const slot = `${formatTime(e.start_time)} - ${formatTime(e.end_time)}`;
                    if (!slotMap.has(slot)) slotMap.set(slot, []);
                    slotMap.get(slot).push(e);
                });

                const slots = currentSlots.length
                    ? currentSlots.map(s => `${s.start} - ${s.end}`)
                    : Array.from(slotMap.keys()).sort((a, b) => {
                        const [aStart] = a.split(' - ');
                        const [bStart] = b.split(' - ');
                        return minutes(aStart) - minutes(bStart);
                    });

                const now = new Date();
                const todayName = days[now.getDay() - 1];
                const nowMin = now.getHours() * 60 + now.getMinutes();
                let nextKey = null;
                let nextStart = null;
                let nextDiff = null;
                const conflicts = conflictMap(entries);

                entries.forEach(e => {
                    if (e.day_of_week === todayName) {
                        const start = minutes(e.start_time);
                        if (start > nowMin && (nextStart === null || start < nextStart)) {
                            nextStart = start;
                            nextKey = `${e.start_time} - ${e.end_time}`;
                            nextDiff = start - nowMin;
                        }
                    }
                });

                body.innerHTML = slots.map(slot => {
                    const [start, end] = slot.split(' - ');
                    const slotLabel = `${formatTime(start)} to ${formatTime(end)}`;
                    const slotMeta = currentSlots.find(s => `${s.start} - ${s.end}` === slot);

                    if (slotMeta && slotMeta.type && slotMeta.type !== 'period') {
                        const label = slotMeta.type === 'lunch' ? 'Lunch Time' : 'Break Time';
                        return `
                            <tr class="tt-break-row" data-slot-start="${formatTime(start)}" data-slot-end="${formatTime(end)}">
                                <td class="tt-slot">${slotLabel}</td>
                                <td class="tt-break-cell" colspan="${days.length}">${label}</td>
                            </tr>
                        `;
                    }

                    const cells = days.map(day => {
                        const rawItems = (slotMap.get(slot) || []).filter(e => e.day_of_week === day);
                        const seenKeys = new Set();
                        const items = rawItems.filter(e => {
                            const key = [
                                e.academic_year_id,
                                e.class_id,
                                e.section_id,
                                e.day_of_week,
                                e.start_time,
                                e.end_time,
                                e.subject_id,
                                e.teacher_id,
                                e.room
                            ].join('|');
                            if (seenKeys.has(key)) return false;
                            seenKeys.add(key);
                            return true;
                        });
                        if (!items.length) return `<td class="tt-cell-empty" data-day="${day}"></td>`;
                        const cards = items.filter(e => !e.is_break).map(e => {
                            const isCurrent = e.day_of_week === todayName
                                && minutes(e.start_time) <= nowMin
                                && minutes(e.end_time) > nowMin;
                            const isNext = e.day_of_week === todayName && slot === nextKey;
                            const isConflict = conflicts.has(e.id);
                            const isOwn = currentTeacherId && String(e.teacher_id) === String(currentTeacherId);
                            const badge = isNext ? `<div class="tt-badge">Next class in ${nextDiff} min</div>` : '';
                            const inactive = e.status === 0 || e.status === '0';
                            return `
                                <div class="tt-card ${isCurrent ? 'tt-current' : ''} ${isNext ? 'tt-next' : ''} ${isConflict ? 'tt-conflict' : ''} ${inactive ? 'tt-inactive' : ''} ${isOwn ? 'tt-own' : ''}"
                                    style="background:${colorFor(e.subject_id)}"
                                    title="${e.subject?.name ?? 'Subject'} · ${e.teacher?.name ?? ''} · ${e.room ?? ''}"
                                    data-details='${encodeURIComponent(JSON.stringify({
                                        id: e.id,
                                        class_id: e.class_id,
                                        section_id: e.section_id,
                                        subject_id: e.subject_id,
                                        teacher_id: e.teacher_id,
                                        academic_year_id: e.academic_year_id,
                                        type: e.type,
                                        status: e.status,
                                        subject: e.subject?.name ?? '',
                                        teacher: e.teacher?.name ?? '',
                                        room: e.room ?? '',
                                        class: e.class?.name ?? '',
                                        section: e.section?.name ?? '',
                                        time: `${e.start_time} - ${e.end_time}`,
                                        day: e.day_of_week,
                                        start_time: formatTime(e.start_time),
                                        end_time: formatTime(e.end_time),
                                        edit_url: e.edit_url || '',
                                        update_url: e.update_url || '',
                                        delete_url: e.delete_url || '',
                                    }))}'>
                                    <div class="tt-title">${e.subject?.name ?? 'Subject'}</div>
                                    <div class="tt-meta">${e.class?.name ?? ''} ${e.section?.name ?? ''}</div>
                                    <div class="tt-meta">${e.teacher?.name ?? 'Teacher'}</div>
                                    <div class="tt-meta"><span class="tt-room">Room ${e.room ?? '-'}</span></div>
                                    ${badge}
                                </div>
                            `;
                        }).join('');
                        return `<td data-day="${day}">${cards}</td>`;
                    }).join('');

                    return `<tr data-slot-start="${formatTime(start)}" data-slot-end="${formatTime(end)}"><td class="tt-slot">${slotLabel}</td>${cells}</tr>`;
                }).join('');

                applyDayFilter();
            }

            function load() {
                const params = form ? new URLSearchParams(new FormData(form)).toString() : '';
                const url = params ? `${dataUrl}?${params}` : dataUrl;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(resp => resp.json())
                    .then((resp) => {
                        if (Array.isArray(resp)) {
                            render(resp);
                            return;
                        }
                        const entries = resp?.entries || [];
                        const settings = resp?.settings || null;
                        if (settings && Array.isArray(settings.days) && settings.days.length) {
                            days = settings.days;
                            updateHeader();
                            buildTabs();
                        }
                    if (settings && Array.isArray(settings.slots)) {
                        currentSlots = settings.slots;
                    } else {
                        currentSlots = [];
                    }
                    window.dispatchEvent(new CustomEvent('tt-settings-loaded', { detail: settings || null }));
                    render(entries);
                })
                    .catch(() => {
                        body.innerHTML = `<tr><td colspan="${1 + days.length}" class="tt-empty">Failed to load timetable.</td></tr>`;
                    });
            }

            function saveFilters() {
                if (!form) return;
                const data = Object.fromEntries(new FormData(form).entries());
                localStorage.setItem(storageKey, JSON.stringify(data));
            }

            function restoreFilters() {
                if (!form) return;
                const raw = localStorage.getItem(storageKey);
                if (!raw) return;
                try {
                    const data = JSON.parse(raw);
                    Object.entries(data).forEach(([key, value]) => {
                        const el = getFilterEl(key);
                        if (el) el.value = value;
                    });
                } catch (_) {
                    localStorage.removeItem(storageKey);
                }
            }

            function filterSectionsByClass() {
                if (!classSelect || !sectionSelect) return;
                const classId = classSelect.value;
                const previousValue = sectionSelect.value;
                sectionSelect.innerHTML = '';

                const allowed = sectionOptions.filter(opt => {
                    if (!opt.value) return true;
                    if (!classId) return false;
                    return opt.classId === classId;
                });

                allowed.forEach(opt => {
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
            }

            grid.addEventListener('click', (e) => {
                const lockSelectToValue = (selectEl, value) => {
                    if (!selectEl || selectEl.tagName !== 'SELECT') return;
                    const hasValue = value !== undefined && value !== null && String(value).length > 0;
                    Array.from(selectEl.options).forEach(opt => {
                        if (!opt.value) return;
                        opt.hidden = hasValue && opt.value !== String(value);
                    });
                    if (hasValue) {
                        selectEl.value = String(value);
                    } else {
                        Array.from(selectEl.options).forEach(opt => (opt.hidden = false));
                    }
                };

                const openForm = (data) => {
                    const panel = addPanelId ? document.getElementById(addPanelId) : null;
                    if (panel && window.bootstrap) {
                        if (panel.classList.contains('modal')) {
                            const modal = new bootstrap.Modal(panel);
                            modal.show();
                        }
                    }
                    const scope = panel || document;
                    const formEl = scope.querySelector('#tt-entry-form');
                    const methodEl = scope.querySelector('#tt_form_method');
                    const titleEl = scope.querySelector('#tt-form-title');
                    const submitEl = scope.querySelector('#tt-form-submit');
                    const slotEl = scope.querySelector('#tt_slot_select');
                    const deleteForm = scope.querySelector('#tt-delete-form');
                    const deleteBtn = scope.querySelector('#tt-form-delete');
                    const deleteMethod = scope.querySelector('#tt_delete_method');

                    if (formEl && data?.id && data?.update_url) {
                        formEl.action = data.update_url;
                        if (methodEl) methodEl.value = 'PUT';
                        if (titleEl) titleEl.textContent = 'Edit Timetable Entry';
                        if (submitEl) submitEl.textContent = 'Update Entry';
                        if (deleteForm && deleteMethod) {
                            deleteForm.action = data.delete_url || '';
                            deleteMethod.value = 'DELETE';
                            if (deleteBtn) deleteBtn.classList.remove('d-none');
                        }
                    } else if (formEl) {
                        formEl.action = formEl.dataset.createUrl || formEl.action;
                        if (methodEl) methodEl.value = 'POST';
                        if (titleEl) titleEl.textContent = 'Add Timetable Entry';
                        if (submitEl) submitEl.textContent = 'Save Entry';
                        if (deleteBtn) deleteBtn.classList.add('d-none');
                    }

                    const setVal = (name, value) => {
                        const el = scope.querySelector(`[name="${name}"]`);
                        if (el && value !== undefined && value !== null) el.value = value;
                    };

                    if (data) {
                        setVal('day_of_week', data.day);
                        setVal('start_time', data.start_time);
                        setVal('end_time', data.end_time);
                        setVal('class_id', data.class_id || classSelect?.value || '');
                        setVal('section_id', data.section_id || sectionSelect?.value || '');
                        setVal('academic_year_id', data.academic_year_id || yearSelect?.value || '');
                        setVal('room', data.room || '');
                        setVal('type', data.type || 'lecture');
                        setVal('status', String(data.status ?? '1'));
                        if (slotEl && data.start_time && data.end_time) {
                            slotEl.value = `${data.start_time}-${data.end_time}`;
                        }

                        const classField = scope.querySelector('[name="class_id"]');
                        const sectionField = scope.querySelector('[name="section_id"]');
                        const yearField = scope.querySelector('[name="academic_year_id"]');
                        if (classSelect?.value) {
                            lockSelectToValue(classField, classSelect.value);
                        } else {
                            lockSelectToValue(classField, null);
                        }
                        if (yearField && yearSelect?.value) yearField.value = yearSelect.value;
                        classField?.dispatchEvent(new Event('change', { bubbles: true }));
                        sectionField?.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                };

                if (allowCellClick) {
                    const td = e.target.closest('td[data-day]');
                    const card = e.target.closest('.tt-card');
                    const isAction = e.target.closest('.tt-action');
                    if (card && !isAction) {
                        const data = JSON.parse(decodeURIComponent(card.dataset.details || '%7B%7D'));
                        openForm(data);
                        return;
                    }
                    if (td && !isAction && !card) {
                        const day = td.dataset.day;
                        const row = td.closest('tr');
                        const start = row?.dataset?.slotStart || '';
                        const end = row?.dataset?.slotEnd || '';
                        openForm({
                            day,
                            start_time: start,
                            end_time: end,
                            class_id: classSelect?.value || '',
                            section_id: sectionSelect?.value || '',
                            academic_year_id: yearSelect?.value || '',
                            type: 'lecture',
                            status: '1',
                        });
                    }
                }

                if (!enableDetails) return;
                const card = e.target.closest('.tt-card');
                if (!card || !detailsBody) return;
                const data = JSON.parse(decodeURIComponent(card.dataset.details || '%7B%7D'));
                detailsBody.innerHTML = `
                    <div><strong>${data.subject || 'Subject'}</strong></div>
                    <div>${data.day || ''} · ${data.time || ''}</div>
                    <div>Teacher: ${data.teacher || '-'}</div>
                    <div>Class: ${data.class || '-'} ${data.section || ''}</div>
                    <div>Room: ${data.room || '-'}</div>
                `;
                const modal = new bootstrap.Modal(document.getElementById('tt-details-modal'));
                modal.show();
            });

            if (form) {
                restoreFilters();
                filterSectionsByClass();
                form.addEventListener('change', () => {
                    filterSectionsByClass();
                    saveFilters();
                    load();
                });
            }
            if (classSelect) {
                classSelect.addEventListener('change', () => {
                    filterSectionsByClass();
                    saveFilters();
                    load();
                });
            }
            if (sectionSelect) {
                sectionSelect.addEventListener('change', () => {
                    saveFilters();
                    load();
                });
            }
            if (teacherSelect) {
                teacherSelect.addEventListener('change', () => {
                    saveFilters();
                    load();
                });
            }
            if (studentSelect) {
                studentSelect.addEventListener('change', () => {
                    saveFilters();
                    load();
                });
            }
            if (yearSelect) {
                yearSelect.addEventListener('change', () => {
                    saveFilters();
                    load();
                });
            }
            if (printBtn) printBtn.addEventListener('click', () => window.print());
            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    openForm({
                        class_id: classSelect?.value || '',
                        section_id: sectionSelect?.value || '',
                        academic_year_id: yearSelect?.value || '',
                        type: 'lecture',
                        status: '1',
                    });
                });
            }
            buildTabs();
            load();
        })();
    </script>
@endpush
