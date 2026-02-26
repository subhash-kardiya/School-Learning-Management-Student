@extends('layouts.admin')

@section('title', 'Attendance Report')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/attendance-compact.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4 attendance-monthly-wrap att-view--report"
        data-grid-url="{{ route('attendance.report.grid') }}">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Attendance Report</h4>
                <div class="att-filter-note">Monthly report (read-only)</div>
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

        <div class="row g-3 mb-3 align-items-end bg-white p-3 rounded shadow-sm mt-3">

            <!-- Class -->
            <div class="col-md-3">
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
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label class="form-label mb-1">Month</label>
                <select class="form-select" id="attm-month">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                            {{ \Illuminate\Support\Carbon::createFromDate(null, $m, 1)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Button -->
            <div class="col-md-3">
                <button type="button" class="btn btn-primary w-100" id="attm-search">
                    Search
                </button>
            </div>

            <input type="hidden" id="attm-year" value="{{ $year }}">

        </div>

        <div class="d-flex justify-content-end gap-2 mb-3">
            <button type="button" class="attm-search-btn" id="attm-export-pdf">Export PDF</button>
        </div>

        <div id="attm-grid">
            @include('attendance.partials.monthly-grid', [
                'students' => $students,
                'days' => $monthlyData['days'],
                'attendanceMap' => $monthlyData['attendanceMap'],
                'editableDate' => \Illuminate\Support\Carbon::today()->toDateString(),
                'canEdit' => false,
            ])
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const wrapper = document.querySelector('.attendance-monthly-wrap');
            if (!wrapper) return;

            const gridUrl = wrapper.getAttribute('data-grid-url');
            const gridEl = document.getElementById('attm-grid');
            const summaryEl = document.getElementById('attm-summary');
            const classSelect = document.getElementById('attm-class');
            const sectionSelect = document.getElementById('attm-section');
            const monthSelect = document.getElementById('attm-month');
            const yearSelect = document.getElementById('attm-year');
            const searchBtn = document.getElementById('attm-search');
            const exportPdfBtn = document.getElementById('attm-export-pdf');
            const filterError = document.getElementById('attm-filter-error');

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

            const exportPdf = () => {
                const table = gridEl?.querySelector('table');
                if (!table) return;
                const exportTable = table.cloneNode(true);
                exportTable.querySelectorAll('img').forEach(img => img.remove());
                const win = window.open('', '_blank');
                if (!win) return;
                const title = 'Attendance Report';
                const classLabel = classSelect?.selectedOptions?.[0]?.textContent?.trim() || 'N/A';
                const sectionLabel = sectionSelect?.selectedOptions?.[0]?.textContent?.trim() || 'N/A';
                const monthLabel = monthSelect?.selectedOptions?.[0]?.textContent?.trim() || '';
                const yearLabel = yearSelect?.value || '{{ $year }}';
                win.document.write(`
                    <html>
                        <head>
                            <title>${title}</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 16px; }
                                h3 { margin: 0 0 8px; }
                                .meta { margin: 0 0 12px; color: #374151; font-size: 13px; }
                                table { width: 100%; border-collapse: collapse; font-size: 11px; }
                                th, td { border: 1px solid #e5e7eb; padding: 4px 6px; text-align: center; }
                                th:first-child, td:first-child { text-align: left; min-width: 180px; }
                                thead th { background: #f3f4f6; }
                            </style>
                        </head>
                        <body>
                            <h3>${title}</h3>
                            <div class="meta"><strong>Class:</strong> ${classLabel} &nbsp; <strong>Section:</strong> ${sectionLabel} &nbsp; <strong>Month:</strong> ${monthLabel} ${yearLabel}</div>
                            ${exportTable.outerHTML}
                        </body>
                    </html>
                `);
                win.document.close();
                win.focus();
                win.print();
                win.close();
            };

            classSelect?.addEventListener('change', () => {
                filterSections(true);
                loadGrid();
            });
            sectionSelect?.addEventListener('change', loadGrid);
            monthSelect?.addEventListener('change', loadGrid);
            yearSelect?.addEventListener?.('change', loadGrid);
            searchBtn?.addEventListener('click', loadGrid);
            exportPdfBtn?.addEventListener('click', exportPdf);
            filterSections(true);
            if (classSelect?.value && sectionSelect?.value) {
                loadGrid();
            }
        })();
    </script>
@endpush
