@extends('layouts.admin')

@section('title', 'Attendance')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/attendance-compact.css') }}">
@endpush

@section('content')
    @php
        $attendanceGridRoute = session('role') === 'teacher' ? 'teacher.attendance.grid' : 'attendance.grid';
        $attendanceUpdateCellRoute =
            session('role') === 'teacher' ? 'teacher.attendance.update.cell' : 'attendance.update.cell';
    @endphp
    <div class="container-fluid py-4 attendance-monthly-wrap att-view--teacher att-view--mark"
        data-grid-url="{{ route($attendanceGridRoute) }}" data-update-url="{{ route($attendanceUpdateCellRoute) }}">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Add Attendance</h4>
                <div class="att-filter-note">Monthly grid · Click only today to update status</div>

            </div>

        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                Please fix the highlighted fields and try again.
            </div>
        @endif
        <div id="attm-filter-error" class="alert alert-danger d-none"></div>
        <div id="attm-summary">
            @include('attendance.partials.monthly-summary', [
                'daysInMonth' => count($monthlyData['days']),
                'counts' => $monthlyData['counts'],
                'percent' => $percent,
            ])
        </div>

        <div class="row g-9 mb-3 align-items-end justify-content-end bg-white p-3 rounded shadow-sm mt-3">

            <!-- Class -->
            <div class="col-md-2">
                <label class="form-label mb-1">Class</label>
                <select class="form-select" id="attm-class">
                    <option value="">Select Class</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}"
                            {{ (string) $selectedClass === (string) $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-2">
                <label class="form-label mb-1">Section</label>
                <select class="form-select" id="attm-section">
                    <option value="">Select Section</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" data-class-id="{{ $section->class_id }}"
                            {{ (string) $selectedSection === (string) $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Month -->
            <div class="col-md-2">
                <label class="form-label mb-1">Month</label>
                <select class="form-select" id="attm-month">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                            {{ \Illuminate\Support\Carbon::createFromDate(null, $m, 1)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>



            <input type="hidden" id="attm-year" value="{{ $year }}">

        </div>


        <div id="attm-grid">
            @include('attendance.partials.monthly-grid', [
                'students' => $students,
                'days' => $monthlyData['days'],
                'attendanceMap' => $monthlyData['attendanceMap'],
                'editableDate' => \Illuminate\Support\Carbon::today()->toDateString(),
                'canEdit' => true,
            ])
        </div>

        <div class="attm-popover" id="attm-popover">
            <div class="attm-popover-title">Update Status</div>
            <div class="attm-pop-actions">
                <button type="button" class="attm-pop-btn present" data-status="present">Present</button>
                <button type="button" class="attm-pop-btn absent" data-status="absent">Absent</button>
            </div>
        </div>


    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const wrapper = document.querySelector('.attendance-monthly-wrap');
            if (!wrapper) return;

            const gridUrl = wrapper.getAttribute('data-grid-url');
            const updateUrl = wrapper.getAttribute('data-update-url');
            const gridEl = document.getElementById('attm-grid');
            const summaryEl = document.getElementById('attm-summary');
            const popover = document.getElementById('attm-popover');
            const toast = document.getElementById('attm-toast');
            const classSelect = document.getElementById('attm-class');
            const sectionSelect = document.getElementById('attm-section');
            const monthSelect = document.getElementById('attm-month');
            const yearSelect = document.getElementById('attm-year');
            const searchBtn = document.getElementById('attm-search');
            const filterError = document.getElementById('attm-filter-error');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const iconMap = {
                present: '<span class="attm-icon present">&#10003;</span>',
                absent: '<span class="attm-icon absent">&#10005;</span>',
                empty: '<span class="attm-icon">-</span>',
            };

            const showToast = () => {
                if (window.AppToast && typeof window.AppToast.show === 'function') {
                    window.AppToast.show('Attendance saved successfully.', 'success', 4000);
                    return;
                }
                if (!toast) return;
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 1800);
            };

            const setLoading = () => {
                if (!gridEl) return;
                gridEl.innerHTML = `
                    <div class="attm-grid-wrap">
                        <div class="p-3">
                            <div class="attm-skeleton mb-2"></div>
                            <div class="attm-skeleton mb-2"></div>
                            <div class="attm-skeleton mb-2"></div>
                            <div class="attm-skeleton"></div>
                        </div>
                    </div>
                `;
            };

            const loadGrid = () => {
                if (!gridUrl) return;
                if (!classSelect?.value || !sectionSelect?.value) {
                    if (filterError) {
                        filterError.textContent = 'Please select both Class and Section.';
                        filterError.classList.remove('d-none');
                    }
                    classSelect?.classList.toggle('is-invalid', !classSelect?.value);
                    sectionSelect?.classList.toggle('is-invalid', !sectionSelect?.value);
                    return;
                }
                if (filterError) filterError.classList.add('d-none');
                classSelect?.classList.remove('is-invalid');
                sectionSelect?.classList.remove('is-invalid');
                setLoading();

                const params = new URLSearchParams({
                    class_id: classSelect?.value || '',
                    section_id: sectionSelect?.value || '',
                    month: monthSelect?.value || '',
                    year: yearSelect?.value || '',
                });

                fetch(`${gridUrl}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (summaryEl && data.summary) summaryEl.innerHTML = data.summary;
                        if (gridEl && data.html) gridEl.innerHTML = data.html;
                    })
                    .catch(() => {
                        if (gridEl) gridEl.innerHTML =
                            '<div class="attm-empty">Unable to load attendance.</div>';
                    });
            };

            const filterSections = (autoPick = true) => {
                if (!sectionSelect || !classSelect) return;
                const classId = classSelect.value;
                Array.from(sectionSelect.options).forEach(option => {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }
                    const optionClass = option.getAttribute('data-class-id');
                    option.hidden = classId && optionClass !== classId;
                });
                const selectedHidden = classId && sectionSelect.selectedOptions.length && sectionSelect
                    .selectedOptions[0].hidden;
                if (selectedHidden) {
                    sectionSelect.value = '';
                }
                if (autoPick && classId && !sectionSelect.value) {
                    const firstVisible = Array.from(sectionSelect.options).find(opt => opt.value && !opt.hidden);
                    if (firstVisible) {
                        sectionSelect.value = firstVisible.value;
                    }
                }
            };

            const hidePopover = () => {
                if (popover) popover.classList.remove('show');
            };

            const showPopover = (cell) => {
                if (!popover || !cell) return;
                const rect = cell.getBoundingClientRect();
                popover.style.top = `${window.scrollY + rect.bottom + 6}px`;
                popover.style.left = `${window.scrollX + rect.left}px`;
                popover.dataset.studentId = cell.dataset.studentId;
                popover.dataset.date = cell.dataset.date;
                popover.dataset.label = cell.dataset.label;
                popover.dataset.targetId = cell.dataset.cellId || '';
                popover.classList.add('show');
            };

            document.addEventListener('click', (event) => {
                const cell = event.target.closest('.attm-cell-editable');
                if (cell) {
                    showPopover(cell);
                    return;
                }
                if (!event.target.closest('#attm-popover')) {
                    hidePopover();
                }
            });

            popover?.addEventListener('click', (event) => {
                const btn = event.target.closest('.attm-pop-btn');
                if (!btn) return;
                const status = btn.getAttribute('data-status');
                const studentId = popover.dataset.studentId;
                const date = popover.dataset.date;
                const label = popover.dataset.label || '';
                if (!studentId || !date || !status) return;

                const payload = new URLSearchParams({
                    student_id: studentId,
                    date: date,
                    status: status,
                    _token: csrf,
                });

                fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'Accept': 'application/json',
                        },
                        body: payload.toString(),
                    })
                    .then(async response => {
                        if (!response.ok) {
                            throw new Error('Request failed');
                        }
                        return response.json();
                    })
                    .then(() => {
                        const cell = document.querySelector(
                            `.attm-cell[data-student-id="${studentId}"][data-date="${date}"]`);
                        if (cell) {
                            cell.dataset.status = status;
                            cell.dataset.tooltip =
                                `${status.charAt(0).toUpperCase() + status.slice(1)} on ${label}`;
                            cell.innerHTML = iconMap[status] || iconMap.empty;
                        }
                        showToast();
                        hidePopover();
                        loadGrid();
                    })
                    .catch(() => {
                        if (window.AppToast && typeof window.AppToast.show === 'function') {
                            window.AppToast.show('Unable to save attendance. Please try again.', 'error',
                                4000);
                        }
                        hidePopover();
                    });
            });

            classSelect?.addEventListener('change', () => {
                filterSections(true);
                loadGrid();
            });
            sectionSelect?.addEventListener('change', loadGrid);
            monthSelect?.addEventListener('change', loadGrid);
            yearSelect?.addEventListener?.('change', loadGrid);
            searchBtn?.addEventListener('click', loadGrid);
            filterSections(true);
            if (classSelect?.value && sectionSelect?.value) {
                loadGrid();
            }
        })();
    </script>
@endpush
