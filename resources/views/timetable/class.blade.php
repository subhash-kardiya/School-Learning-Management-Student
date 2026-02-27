@extends('layouts.admin')

@section('title', 'Class Timetable')

@section('content')
    <div class="container-fluid py-4 tt-skin-classic timetable-module-compact">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 tt-page-head">
            <div>
                <h5 class="mb-1">Class Timetable</h5>
                <p class="text-muted small mb-0">Weekly view with class/section filters</p>
            </div>
            @if (auth()->user()?->hasPermission('timetable.manage_all'))
                <button type="button" class="btn btn-primary-fancy btn-sm" data-bs-toggle="collapse" data-bs-target="#tt-slots-panel">
                    <i class="fa fa-clock me-1"></i> Time Slots
                </button>
            @endif
        </div>

        @if (auth()->user()?->hasPermission('timetable.manage_all'))
            <div class="collapse mb-3 {{ $errors->settings->any() ? 'show' : '' }}" id="tt-slots-panel">
                <div class="card">
                    <div class="card-body">
                        @if ($errors->settings->any())
                            <div class="alert alert-danger border-0 shadow-sm">
                                <ul class="mb-0">
                                    @foreach ($errors->settings->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('timetable.settings.save') }}" method="POST" id="tt-settings-form">
                            @csrf
                            <input type="hidden" name="academic_year_id" id="tt-setting-year">
                            <input type="hidden" name="class_id" id="tt-setting-class">
                            <input type="hidden" name="section_id" id="tt-setting-section">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Time Slots</strong>
                                <select name="status" class="form-select form-select-sm" style="max-width: 160px;">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Days</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                        <label class="form-check form-check-inline">
                                            <input class="form-check-input tt-day" type="checkbox" value="{{ $day }}">
                                            <span class="form-check-label">{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Start</label>
                                    <input type="time" class="form-control" id="tt-slot-start">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End</label>
                                    <input type="time" class="form-control" id="tt-slot-end">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Type</label>
                                    <select class="form-select" id="tt-slot-type">
                                        <option value="period">Period</option>
                                        <option value="break">Break</option>
                                        <option value="lunch">Lunch</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary-fancy w-100" id="tt-slot-add">Add Slot</button>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Type</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tt-slot-list">
                                        <tr><td colspan="4" class="text-muted">No slots yet.</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div id="tt-days-hidden"></div>
                            <div id="tt-slots-hidden"></div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary-fancy">Save Time Slots</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif

        @include('timetable.partials.grid', [
            'dataUrl' => route('timetable.data'),
            'filters' => [
                'classes' => $classes,
                'sections' => $sections,
                'teachers' => $teachers,
            ],
            'config' => [
                'title' => 'Timetable',
                'showClass' => true,
                'showSection' => true,
                'showAcademicYear' => false,
                'showTeacher' => false,
                'showWeek' => false,
                'showExport' => true,
                'enableDetails' => false,
                'enableCellClick' => auth()->user()?->hasPermission('timetable.manage_all'),
                'addPanelId' => 'tt-entry-modal',
                'addPanelView' => null,
                'timeSlots' => [],
            ],
        ])
    </div>

    <div class="modal fade" id="tt-entry-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tt-form-title">Add Timetable Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('timetable.partials.add-form', [
                        'timeSlots' => [],
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('tt-settings-form');
            if (!form) return;

            const slotList = document.getElementById('tt-slot-list');
            const slotStart = document.getElementById('tt-slot-start');
            const slotEnd = document.getElementById('tt-slot-end');
            const slotType = document.getElementById('tt-slot-type');
            const slotAdd = document.getElementById('tt-slot-add');
            const daysHidden = document.getElementById('tt-days-hidden');
            const slotsHidden = document.getElementById('tt-slots-hidden');
            const yearHidden = document.getElementById('tt-setting-year');
            const classHidden = document.getElementById('tt-setting-class');
            const sectionHidden = document.getElementById('tt-setting-section');
            const statusSelect = form.querySelector('[name="status"]');

            let slots = [];

            function renderSlots() {
                if (!slotList || !slotsHidden) return;
                if (!slots.length) {
                    slotList.innerHTML = `<tr><td colspan="4" class="text-muted">No slots yet.</td></tr>`;
                } else {
                    slotList.innerHTML = slots.map((s, idx) => `
                        <tr>
                            <td>${s.start}</td>
                            <td>${s.end}</td>
                            <td>${s.type}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-remove="${idx}">Remove</button>
                            </td>
                        </tr>
                    `).join('');
                }

                slotsHidden.innerHTML = '';
                slots.forEach((s, idx) => {
                    slotsHidden.insertAdjacentHTML('beforeend', `
                        <input type="hidden" name="slots[${idx}][start]" value="${s.start}">
                        <input type="hidden" name="slots[${idx}][end]" value="${s.end}">
                        <input type="hidden" name="slots[${idx}][type]" value="${s.type}">
                    `);
                });
            }

            function renderDays() {
                if (!daysHidden) return;
                daysHidden.innerHTML = '';
                form.querySelectorAll('.tt-day:checked').forEach(chk => {
                    daysHidden.insertAdjacentHTML('beforeend', `
                        <input type="hidden" name="days[]" value="${chk.value}">
                    `);
                });
            }

            slotList?.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove]');
                if (!btn) return;
                const index = parseInt(btn.dataset.remove, 10);
                if (Number.isNaN(index)) return;
                slots.splice(index, 1);
                renderSlots();
            });

            slotAdd?.addEventListener('click', () => {
                if (!slotStart?.value || !slotEnd?.value) return;
                slots.push({
                    start: slotStart.value,
                    end: slotEnd.value,
                    type: slotType?.value || 'period',
                });
                slots.sort((a, b) => a.start.localeCompare(b.start));
                renderSlots();
                slotStart.value = '';
                slotEnd.value = '';
            });


            form.addEventListener('change', renderDays);
            form.addEventListener('submit', () => {
                renderDays();
            });

            window.addEventListener('tt-settings-loaded', (e) => {
                const settings = e.detail;
                if (settings && Array.isArray(settings.slots)) {
                    slots = settings.slots.map(s => ({
                        start: s.start,
                        end: s.end,
                        type: s.type || 'period',
                    }));
                } else {
                    slots = [];
                }
                renderSlots();

                const days = settings?.days || [];
                form.querySelectorAll('.tt-day').forEach(chk => {
                    chk.checked = days.includes(chk.value);
                });
                renderDays();

                if (statusSelect && settings?.status) {
                    statusSelect.value = settings.status;
                }
            });

            // Sync filter values into settings form
            const classSelect = document.querySelector('[form="tt-filters"][name="class_id"]');
            const sectionSelect = document.querySelector('[form="tt-filters"][name="section_id"]');
            const yearSelect = document.querySelector('[form="tt-filters"][name="academic_year_id"]');
            function syncFilters() {
                if (classHidden && classSelect) classHidden.value = classSelect.value || '';
                if (sectionHidden && sectionSelect) sectionHidden.value = sectionSelect.value || '';
                if (yearHidden && yearSelect) yearHidden.value = yearSelect.value || '';
            }
            [classSelect, sectionSelect, yearSelect].forEach(el => {
                el?.addEventListener('change', syncFilters);
            });
            syncFilters();

            form.addEventListener('submit', (e) => {
                syncFilters();
                const missing = !classHidden?.value || !sectionHidden?.value || !yearHidden?.value;
                if (missing) {
                    e.preventDefault();
                    alert('Please select Class, Section, and Academic Year before saving slots.');
                }
            });

            const slotsPanel = document.getElementById('tt-slots-panel');
            slotsPanel?.addEventListener('shown.bs.collapse', syncFilters);
        })();
    </script>
    @if ($errors->getBag('default')->any())
        <script>
            (function () {
                const modal = document.getElementById('tt-entry-modal');
                if (modal && window.bootstrap) {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }
            })();
        </script>
    @endif
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
@endpush
