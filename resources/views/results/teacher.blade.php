@extends('layouts.admin')

@section('title', 'Teacher Declared Results')

@push('css')
<link rel="stylesheet" href="{{ asset('css/results.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4 res-shell">
        <div class="d-flex justify-content-between align-items-center mb-3 p-3 res-title-bar">
            <div>
                <h5 class="mb-1">Teacher Declared Results</h5>
                <div class="small opacity-75">Filter by class, section, exam and search text</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @include('results.partials.print-button')
                <span class="px-3 py-2 res-status-badge">Declared Results</span>
            </div>
        </div>

        <div class="row g-3 mb-3 res-stats-row">
            <div class="col-md-2-4 col-6">
                <div class="res-stat h-100" style="background:#eff6ff;border:1px solid #bfdbfe;">
                    <div class="text-muted small">Total Students</div>
                    <div class="fs-4 fw-bold text-dark" id="teacher-summary-total">0</div>
                </div>
            </div>
            <div class="col-md-2-4 col-6">
                <div class="res-stat h-100" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <div class="text-muted small">Pass</div>
                    <div class="fs-4 fw-bold text-dark" id="teacher-summary-pass">0</div>
                </div>
            </div>
            <div class="col-md-2-4 col-6">
                <div class="res-stat h-100" style="background:#fef2f2;border:1px solid #fecaca;">
                    <div class="text-muted small">Fail</div>
                    <div class="fs-4 fw-bold text-dark" id="teacher-summary-fail">0</div>
                </div>
            </div>
            <div class="col-md-2-4 col-6">
                <div class="res-stat h-100" style="background:#ecfeff;border:1px solid #a5f3fc;">
                    <div class="text-muted small">Highest Marks</div>
                    <div class="fs-4 fw-bold text-dark" id="teacher-summary-highest">-</div>
                </div>
            </div>
            <div class="col-md-2-4 col-12">
                <div class="res-stat h-100" style="background:#fffbeb;border:1px solid #fde68a;">
                    <div class="text-muted small">Average</div>
                    <div class="fs-4 fw-bold text-dark" id="teacher-summary-average">-</div>
                </div>
            </div>
        </div>

        <div class="card res-card mb-3 res-filter-card">
            <div class="card-body">
                <form id="teacher-results-filter-form" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select name="class_id" id="teacher-result-class" class="form-select">
                            <option value="">All Classes</option>
                            @foreach (($classes ?? collect()) as $class)
                                <option value="{{ $class->id }}" {{ (string) request('class_id', '') === (string) $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Section</label>
                        <select name="section_id" id="teacher-result-section" class="form-select">
                            <option value="">All Sections</option>
                            @foreach (($sections ?? collect()) as $section)
                                <option value="{{ $section->id }}" data-class="{{ $section->class_id }}" {{ (string) request('section_id', '') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Exam</label>
                        <select name="exam_name" id="teacher-result-exam" class="form-select">
                            <option value="">All Exams</option>
                            @foreach (($examOptions ?? collect()) as $examName)
                                <option value="{{ $examName }}" {{ (string) request('exam_name', '') === (string) $examName ? 'selected' : '' }}>
                                    {{ $examName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" id="teacher-result-search" class="form-control" placeholder="Search by Exam, Student, Subject, or Roll No" value="{{ request('search_text', '') }}">
                    </div>
                </form>
            </div>
        </div>

        <div class="card res-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="teacher-result-list-title">Result Details</h5>
            </div>
            <div class="card-body p-0">
                <div class="px-3 py-2 text-muted small" id="teacher-results-help">
                    Select Class, Section, and Exam to load declared results.
                </div>
                <div class="table-responsive res-table-wrap res-teacher-no-scroll">
                    <table class="table table-hover align-middle mb-0 res-table" id="teacher-result-details-table">
                        <thead class="table-light res-head">
                            <tr>
                                <th style="width:70px;">No</th>
                                <th>Student Name</th>
                                <th>Roll No</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Result</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Select Class, Section, and Exam to load result details.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        if (!window.jQuery || !$.fn.DataTable) return;
        const style = document.createElement('style');
        style.textContent = '@media (min-width: 768px){.col-md-2-4{flex:0 0 auto;width:20%;}}';
        document.head.appendChild(style);

        const classSelect = document.getElementById('teacher-result-class');
        const sectionSelect = document.getElementById('teacher-result-section');
        const examSelect = document.getElementById('teacher-result-exam');
        const searchInput = document.getElementById('teacher-result-search');
        const tableEl = document.getElementById('teacher-result-details-table');
        const summaryTotal = document.getElementById('teacher-summary-total');
        const summaryPass = document.getElementById('teacher-summary-pass');
        const summaryFail = document.getElementById('teacher-summary-fail');
        const summaryHighest = document.getElementById('teacher-summary-highest');
        const summaryAverage = document.getElementById('teacher-summary-average');
        if (!tableEl) return;

        function updateSummary(summary) {
            const safe = summary || {};
            if (summaryTotal) summaryTotal.textContent = safe.total_students ?? 0;
            if (summaryPass) summaryPass.textContent = safe.pass_students ?? 0;
            if (summaryFail) summaryFail.textContent = safe.fail_students ?? 0;
            if (summaryHighest) summaryHighest.textContent = safe.highest_marks ?? '-';
            if (summaryAverage) summaryAverage.textContent = safe.average_percentage ?? '-';
        }

        const dt = $('#teacher-result-details-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            paging: false,
            info: false,
            lengthChange: false,
            ajax: {
                url: "{{ route('teacher.results') }}",
                data: function (d) {
                    d.class_id = classSelect ? classSelect.value : '';
                    d.section_id = sectionSelect ? sectionSelect.value : '';
                    d.exam_name = examSelect ? examSelect.value : '';
                    d.search_text = searchInput ? searchInput.value : '';
                }
            },
            columns: [
                {
                    data: null,
                    name: 'row_no',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'student_name', name: 'student_name' },
                {
                    data: 'roll_no_value',
                    name: 'roll_no_value',
                    render: function (data) {
                        return data || '-';
                    }
                },
                {
                    data: 'percentage_value',
                    name: 'percentage_value',
                    className: 'text-center',
                    render: function (data) {
                        return data || '-';
                    }
                },
                {
                    data: 'result_value',
                    name: 'result_value',
                    className: 'text-center',
                    render: function (data) {
                        if ((data || '').toLowerCase() === 'pass') {
                            return '<span class="res-pill res-pill-ok">Pass</span>';
                        }
                        if ((data || '').toLowerCase() === 'fail') {
                            return '<span class="res-pill res-pill-wait">Fail</span>';
                        }
                        return data || '-';
                    }
                },
                {
                    data: 'view_url',
                    name: 'action',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        if (!data) return '-';
                        return '<a href="' + data + '" class="btn btn-sm btn-light"><i class="fas fa-eye me-1"></i>View</a>';
                    }
                },
            ],
            drawCallback: function () {}
        });
        $('#teacher-result-details-table').on('xhr.dt', function (e, settings, json) {
            updateSummary(json ? json.summary : null);
            const isPrintMode = new URLSearchParams(window.location.search).get('print') === '1';
            if (isPrintMode && !window.__resultPrintTriggered) {
                window.__resultPrintTriggered = true;
                setTimeout(function () {
                    window.print();
                }, 350);
            }
        });

        function filterSections() {
            if (!sectionSelect || !classSelect) return;
            const classId = classSelect.value;
            Array.from(sectionSelect.options).forEach(opt => {
                if (!opt.value) return;
                const optClass = opt.getAttribute('data-class');
                const hidden = classId && optClass !== classId;
                opt.hidden = hidden;
                opt.style.display = hidden ? 'none' : '';
            });
            if (sectionSelect.selectedOptions[0] && sectionSelect.selectedOptions[0].hidden) {
                sectionSelect.value = '';
            }
        }

        function redraw() { dt.draw(); }
        function hasRequiredFilters() {
            return !!(classSelect && classSelect.value && sectionSelect && sectionSelect.value && examSelect && examSelect.value);
        }
        function syncHelpState() {
            const help = document.getElementById('teacher-results-help');
            if (!help) return;
            help.style.display = hasRequiredFilters() ? 'none' : '';
        }
        const debouncedRedraw = (() => {
            let t = null;
            return function () {
                clearTimeout(t);
                t = setTimeout(function () {
                    if (hasRequiredFilters()) {
                        redraw();
                    }
                }, 250);
            };
        })();

        classSelect && classSelect.addEventListener('change', function () {
            filterSections();
            syncHelpState();
            redraw();
        });
        sectionSelect && sectionSelect.addEventListener('change', function () {
            syncHelpState();
            redraw();
        });
        examSelect && examSelect.addEventListener('change', function () {
            syncHelpState();
            redraw();
        });
        searchInput && searchInput.addEventListener('input', function () {
            if (!hasRequiredFilters()) {
                return;
            }
            debouncedRedraw();
        });
        filterSections();
        syncHelpState();
    })();
</script>
@endpush

@include('results.partials.print-script')
