@extends($layout ?? 'layouts.admin')

@section('title', 'Declared Results')

@push('css')
<link rel="stylesheet" href="{{ asset('css/results.css') }}">
@endpush

@section('content')
    @php
        $declaredCount = $marks->filter(fn($m) => (int) ($m->exam->result_declared ?? 0) === 1)->count();
        $avgPercent = $marks->count() > 0
            ? number_format(
                $marks->filter(fn($m) => ($m->exam->total_mark ?? 0) > 0 && $m->marks_obtained !== null)
                    ->avg(fn($m) => ($m->marks_obtained / max(1, (float) ($m->exam->total_mark ?? 0))) * 100) ?? 0,
                2
            )
            : null;
    @endphp
    <div class="container-fluid py-4 res-shell">
        <div class="d-flex justify-content-between align-items-center mb-3 p-3 res-title-bar">
            <div>
                <h5 class="mb-1">School LMS Declared Results</h5>
                <div class="small opacity-75">Project result declaration view</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @include('results.partials.print-button')
                <span class="px-3 py-2 res-status-badge">Declared Results</span>
            </div>
        </div>

        @if (session('role') === 'student' && !empty($studentResultFilters))
            <div class="card res-card mb-3 res-filter-card">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.results') }}" id="student-result-filters" class="row g-3">
                        <input type="hidden" name="class_id" value="{{ $selectedClassId ?? '' }}">
                        <input type="hidden" name="section_id" value="{{ $selectedSectionId ?? '' }}">
                        <div class="row">
                            <div class="res-block-head w-100">Search</div>
                            <div class="col-6">
                                <label for="session_id" class="form-label mb-2 res-select-label">Select Academic Year</label>
                                <select name="session_id" id="session_id" class="form-select res-select-box mb-3" onchange="this.form.submit()">
                                    <option value="">Select Academic Year</option>
                                    @foreach (($sessionOptions ?? collect()) as $sessionOption)
                                        <option value="{{ $sessionOption->id }}" {{ (int) ($selectedSessionId ?? 0) === (int) $sessionOption->id ? 'selected' : '' }}>
                                            {{ $sessionOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="exam_name" class="form-label mb-2 res-select-label">Select Exam</label>
                                <select name="exam_name" id="exam_name" class="form-select res-select-box" onchange="this.form.submit()" {{ empty($selectedSessionId) ? 'disabled' : '' }}>
                                    <option value="">Select Exam</option>
                                    @foreach (($examNameOptions ?? collect()) as $examName)
                                        <option value="{{ $examName }}" {{ (string) ($selectedExamName ?? '') === (string) $examName ? 'selected' : '' }}>
                                            {{ $studentProfile->class->name ?? 'Class' }}{{ $studentProfile->section ? ' - ' . $studentProfile->section->name : '' }} - {{ $examName }}
                                        </option>
                                    @endforeach
                                </select>                        
                            </div>
                        </div>
                        
                       
                    </form>
                </div>
            </div>
        @endif

        @if (session('role') !== 'student')
            <div class="row g-3 mb-3 res-stats-row">
                <div class="col-md-4">
                    <div class="res-stat h-100" style="background:#eff6ff;border:1px solid #bfdbfe;">
                        <div class="text-muted small">Total Rows</div>
                        <div class="fs-4 fw-bold text-dark">{{ $marks->count() }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="res-stat h-100" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <div class="text-muted small">Declared Rows</div>
                        <div class="fs-4 fw-bold text-dark">{{ $declaredCount }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="res-stat h-100" style="background:#ecfeff;border:1px solid #a5f3fc;">
                        <div class="text-muted small">Average Percentage</div>
                        <div class="fs-4 fw-bold text-dark">{{ $avgPercent !== null ? $avgPercent . '%' : '-' }}</div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('role') === 'student' && !empty($studentResultFilters))
            @if (empty($selectedSessionId))
            <div class="card res-card mb-3">
                <div class="card-body text-center py-4">
                    <div class="fw-semibold text-dark">Select academic year to view declared results.</div>
                </div>
            </div>
            @elseif (empty($selectedExamName))
            <div class="card res-card mb-3">
                <div class="card-body text-center py-4">
                    <div class="fw-semibold text-dark">Select exam to view declared results.</div>
                </div>
            </div>
            @endif

            @if (!empty($canShowResult))
            <div class="card res-card mb-3">
                <div class="card-body">
                    @php
                        $declaredAt = null;
                        if (!empty($selectedExam) && !empty($selectedExam->updated_at)) {
                            $declaredAt = \Carbon\Carbon::parse($selectedExam->updated_at);
                        } else {
                            $declaredAt = $marks->map(fn($m) => $m->exam?->updated_at)
                                ->filter()
                                ->map(fn($d) => \Carbon\Carbon::parse($d))
                                ->sortDesc()
                                ->first();
                        }
                    @endphp
                    <div class="res-sheet">
                        <div class="res-sheet-head">Student Details</div>
                        <div class="res-sheet-row">
                            <div class="res-k">Student Name</div>
                            <div class="res-v">{{ $selectedStudentName ?? '-' }}</div>
                            <div class="res-k">Roll No</div>
                            <div class="res-v">{{ $selectedRollNo ?? '-' }}</div>
                            <div class="res-k">Class</div>
                            <div class="res-v">{{ $studentProfile->class->name ?? '-' }}</div>
                            <div class="res-k">Section</div>
                            <div class="res-v">{{ $studentProfile->section->name ?? '-' }}</div>
                            <div class="res-k">Exam type</div>
                            <div class="res-v">{{ $selectedExamName ?? '-' }}</div>
                            <div class="res-k">Declared Date</div>
                            <div class="res-v">{{ $declaredAt ? $declaredAt->format('d M Y') : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card res-card res-subject-card">
                <div class="card-header d-flex justify-content-between align-items-center res-subject-head">
                    <h5 class="mb-0">All Subjects Declared Result</h5>
                    <span class="res-exam-pill">{{ $selectedAcademicYearName ?? 'Academic Year' }}</span>
                </div>
                <div class="card-body p-0">
                    @php
                        $totalObtained = (float) $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
                        $totalMarks = (float) $marks->sum(fn($m) => (float) ($m->exam->total_mark ?? 0));
                        $overallPercentage = $totalMarks > 0 ? round(($totalObtained / $totalMarks) * 100, 2) : 0;
                        $hasPassingRules = $marks->contains(fn($m) => $m->exam?->passing_mark !== null);
                        if ($hasPassingRules) {
                            $overallPass = $marks->count() > 0
                                && $marks->every(function ($m) {
                                    return $m->marks_obtained !== null
                                        && $m->exam?->passing_mark !== null
                                        && (float) $m->marks_obtained >= (float) $m->exam->passing_mark;
                                });
                        } else {
                            $overallPass = $marks->count() > 0 && $overallPercentage >= 60;
                        }

                        $numToWords = function ($num) use (&$numToWords) {
                            $num = (int) $num;
                            $ones = [0 => 'ZERO', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE', 6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE', 10 => 'TEN', 11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN', 16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN'];
                            $tens = [2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY', 6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY'];

                            if ($num < 20) return $ones[$num];
                            if ($num < 100) return $tens[intdiv($num, 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
                            if ($num < 1000) return $ones[intdiv($num, 100)] . ' HUNDRED' . ($num % 100 ? ' ' . $numToWords($num % 100) : '');
                            if ($num < 1000000) return $numToWords(intdiv($num, 1000)) . ' THOUSAND' . ($num % 1000 ? ' ' . $numToWords($num % 1000) : '');
                            return (string) $num;
                        };
                        $obtainedInWords = $numToWords((int) round($totalObtained)) . ' ONLY';
                    @endphp
                    <div class="table-responsive res-table-wrap">
                        <table class="table table-hover align-middle mb-0 res-table" id="{{ !empty($useAjaxStudentResults) ? 'student-results-table' : '' }}">
                            <thead class="table-light res-head">
                                <tr>
                                    <th style="width:70px;">No</th>
                                    <th>Subject Name</th>
                                    <th>Grade</th>
                                    <th class="text-center">Total Marks</th>
                                    <th class="text-center">Passing Marks</th>
                                    <th class="text-center">Obtain Marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($marks as $mark)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $mark->subject->name ?? '-' }}</td>
                                        <td><span class="res-pill res-grade">{{ $mark->grade ?? '-' }}</span></td>
                                        <td class="text-center"><span class="res-num">{{ $mark->exam->total_mark ?? '-' }}</span></td>
                                        <td class="text-center"><span class="res-num">{{ $mark->exam->passing_mark ?? '-' }}</span></td>
                                        <td class="text-center"><span class="res-num">{{ $mark->marks_obtained ?? '-' }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No declared result records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($marks->count() > 0)
                            <tfoot>
                                <tr class="res-total-row">
                                    <th colspan="3" class="text-end pe-4">Total</th>
                                    <th class="text-center"><span class="res-num">{{ number_format($totalMarks, 2) }}</span></th>
                                    <th class="text-center">-</th>
                                    <th class="text-center"><span class="res-num">{{ number_format($totalObtained, 2) }}</span></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    @if ($marks->count() > 0)
                    @php
                        $gradeInfo = \App\Models\Grade::resolveGrade($overallPercentage);
                        $overallGrade = $gradeInfo['name'];
                    @endphp
                    <div class="res-total-box">
                        <div class="res-total-grid">
                            <div class="res-total-item">
                                <div class="res-total-k">Percentage</div>
                                <div class="res-total-v">{{ number_format($overallPercentage, 2) }}%</div>
                            </div>
                            <div class="res-total-item">
                                <div class="res-total-k">Grade</div>
                                <div class="res-total-v">{{ $overallGrade }}</div>
                            </div>
                            <div class="res-total-item">
                                <div class="res-total-k">Result</div>
                                <div class="res-total-v">{{ $overallPass ? 'PASS' : 'FAIL' }}</div>
                            </div>
                        </div>
                        <div class="res-total-words">
                            Total Obtain Marks In Words: {{ $obtainedInWords }}
                        </div>
                        <div class="res-total-words text-center" style="margin-top:12px;">
                            {{ $overallPass ? 'CONGRATULATIONS!! You have passed this exam.' : 'Sorry! You have not cleared exam.' }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        @else
            @php
                $showRemarksColumn = session('role') !== 'teacher';
            @endphp
            @if (session('role') === 'teacher' && !empty($useAjaxResults))
                <div class="card res-card mb-3 res-filter-card">
                    <div class="card-body">
                        <form id="teacher-results-filter-form" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Class</label>
                                <select name="class_id" id="teacher-result-class" class="form-select">
                                    <option value="">All Classes</option>
                                    @foreach (($classes ?? collect()) as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Section</label>
                                <select name="section_id" id="teacher-result-section" class="form-select">
                                    <option value="">All Sections</option>
                                    @foreach (($sections ?? collect()) as $section)
                                        <option value="{{ $section->id }}" data-class="{{ $section->class_id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Exam</label>
                                <select name="exam_name" id="teacher-result-exam" class="form-select">
                                    <option value="">All Exams</option>
                                    @foreach (($examOptions ?? collect()) as $examName)
                                        <option value="{{ $examName }}">
                                            {{ $examName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" id="teacher-result-search" class="form-control" placeholder="Search by Exam, Student, Subject, or Roll No">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card res-card mb-3" id="teacher-student-detail-card" style="display:none;">
                    <div class="card-body">
                        <div class="res-sheet">
                            <div class="res-sheet-head">Student Details</div>
                            <div class="res-sheet-row">
                                <div class="res-k">Student Name</div>
                                <div class="res-v" id="teacher-detail-student-name">-</div>
                                <div class="res-k">Roll No</div>
                                <div class="res-v" id="teacher-detail-roll-no">-</div>
                                <div class="res-k">Class</div>
                                <div class="res-v" id="teacher-detail-class">-</div>
                                <div class="res-k">Section</div>
                                <div class="res-v" id="teacher-detail-section">-</div>
                                <div class="res-k">Exam Type</div>
                                <div class="res-v" id="teacher-detail-exam">-</div>
                                <div class="res-k">Declared Date</div>
                                <div class="res-v" id="teacher-detail-declared-date">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="card res-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="{{ (session('role') === 'teacher' && !empty($useAjaxResults)) ? 'teacher-result-list-title' : '' }}">
                        {{ (session('role') === 'teacher' && !empty($useAjaxResults)) ? 'Result Details' : 'Declared Results' }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if (session('role') === 'teacher' && !empty($useAjaxResults))
                        <div class="px-3 py-2 text-muted small" id="teacher-results-help">
                            Select Class, Section, and Exam to load declared results.
                        </div>
                    @endif
                    <div class="table-responsive {{ (session('role') === 'teacher' && !empty($useAjaxResults)) ? 'res-table-wrap res-teacher-no-scroll' : '' }}">
                        <table class="table table-hover align-middle mb-0 res-table" id="{{ (session('role') === 'teacher' && !empty($useAjaxResults)) ? 'teacher-result-details-table' : '' }}">
                            <thead class="table-light res-head">
                                <tr>
                                    @if (session('role') === 'teacher' && !empty($useAjaxResults))
                                        <th style="width:70px;">No</th>
                                        <th>Subject Name</th>
                                        <th>Grade</th>
                                        <th class="text-center">Total Marks</th>
                                        <th class="text-center">Passing Marks</th>
                                        <th class="text-center">Obtain Marks</th>
                                    @else
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Exam</th>
                                        <th>Subject</th>
                                        <th class="text-center">Marks</th>
                                        <th class="text-center">Total</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                        @if ($showRemarksColumn)
                                            <th>Remarks</th>
                                        @endif
                                        <th class="text-center">Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if (session('role') === 'teacher' && !empty($useAjaxResults))
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Select Class, Section, and Exam to load result details.</td>
                                    </tr>
                                @else
                                @forelse ($marks as $mark)
                                    @php
                                        $obtained = $mark->marks_obtained;
                                        $total = $mark->exam->total_mark ?? 0;
                                        $percentValue = ($obtained !== null && $total > 0)
                                            ? round(($obtained / $total) * 100, 2)
                                            : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $mark->student->student_name ?? '-' }}</td>
                                        <td>{{ $mark->student->class->name ?? '-' }}</td>
                                        @if (session('role') === 'teacher' && !empty($useAjaxResults))
                                            <td>{{ $mark->section->name ?? '-' }}</td>
                                        @endif
                                        <td>{{ $mark->exam->name ?? '-' }}</td>
                                        <td>{{ $mark->subject->name ?? '-' }}</td>
                                        <td class="text-center"><span class="res-num">{{ $obtained !== null ? $obtained : '-' }}</span></td>
                                        <td class="text-center"><span class="res-num">{{ $total ?: '-' }}</span></td>
                                        <td>
                                            @if ($percentValue !== null)
                                                <div class="res-progress-wrap">
                                                    <div class="res-progress"><span style="width: {{ min(100, max(0, $percentValue)) }}%"></span></div>
                                                    <div class="small mt-1 fw-semibold">{{ number_format($percentValue, 2) }}%</div>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="res-pill res-grade">{{ $mark->grade ?? '-' }}</span>
                                        </td>
                                        @if ($showRemarksColumn)
                                            <td><span class="res-note" title="{{ $mark->remarks ?? '-' }}">{{ $mark->remarks ?? '-' }}</span></td>
                                        @endif
                                        <td class="text-center">
                                            @if (($mark->exam->result_declared ?? 0) == 1)
                                                <span class="res-pill res-pill-ok">Result Declared</span>
                                            @else
                                                <span class="res-pill res-pill-wait">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        @php $baseCols = 9; @endphp
                                        <td colspan="{{ $showRemarksColumn ? ($baseCols + 1) : $baseCols }}" class="text-center py-4 text-muted">No result records found.</td>
                                    </tr>
                                @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @if (method_exists($marks, 'links'))
                    <div class="card-footer">
                        {{ $marks->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection

@if (session('role') === 'teacher' && !empty($useAjaxResults))
@push('scripts')
<script>
    (function () {
        if (!window.jQuery || !$.fn.DataTable) return;

        const classSelect = document.getElementById('teacher-result-class');
        const sectionSelect = document.getElementById('teacher-result-section');
        const examSelect = document.getElementById('teacher-result-exam');
        const searchInput = document.getElementById('teacher-result-search');
        const tableEl = document.getElementById('teacher-result-details-table');
        const detailCard = document.getElementById('teacher-student-detail-card');
        const detailStudentName = document.getElementById('teacher-detail-student-name');
        const detailRollNo = document.getElementById('teacher-detail-roll-no');
        const detailClass = document.getElementById('teacher-detail-class');
        const detailSection = document.getElementById('teacher-detail-section');
        const detailExam = document.getElementById('teacher-detail-exam');
        const detailDeclaredDate = document.getElementById('teacher-detail-declared-date');
        const resultTitle = document.getElementById('teacher-result-list-title');
        if (!tableEl) return;

        const dt = $('#teacher-result-details-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            paging: false,
            info: false,
            lengthChange: false,
            ajax: {
                url: "{{ route('results.index') }}",
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
                { data: 'subject_name', name: 'subject_name' },
                {
                    data: 'grade_value',
                    name: 'grade_value',
                    render: function (data) {
                        return '<span class="res-pill res-grade">' + (data || '-') + '</span>';
                    }
                },
                {
                    data: 'total_mark_value',
                    name: 'total_mark_value',
                    className: 'text-center',
                    render: function (data) {
                        return '<span class="res-num">' + (data ?? '-') + '</span>';
                    }
                },
                {
                    data: 'passing_mark_value',
                    name: 'passing_mark_value',
                    className: 'text-center',
                    render: function (data) {
                        return '<span class="res-num">' + (data ?? '-') + '</span>';
                    }
                },
                {
                    data: 'marks_value',
                    name: 'marks_value',
                    className: 'text-center',
                    render: function (data) {
                        return '<span class="res-num">' + (data ?? '-') + '</span>';
                    }
                },
            ],
            drawCallback: function () {
                if (!detailCard) return;
                const rows = this.api().rows({ page: 'current' }).data();
                if (!rows || rows.length === 0) {
                    detailCard.style.display = 'none';
                    if (resultTitle) {
                        resultTitle.textContent = 'Result Details';
                    }
                    return;
                }
                const row = rows[0];
                detailStudentName.textContent = row.student_name || '-';
                detailRollNo.textContent = row.roll_no_value || '-';
                detailClass.textContent = row.class_name || '-';
                detailSection.textContent = row.section_name || '-';
                detailExam.textContent = row.exam_name || '-';
                detailDeclaredDate.textContent = row.declared_date_value || '-';
                if (resultTitle) {
                    resultTitle.textContent = (row.student_name || 'Student') + ' - Result Details';
                }
                detailCard.style.display = '';
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
            if (!hasRequiredFilters()) {
                detailCard && (detailCard.style.display = 'none');
            }
            redraw();
        });
        sectionSelect && sectionSelect.addEventListener('change', function () {
            syncHelpState();
            if (!hasRequiredFilters()) {
                detailCard && (detailCard.style.display = 'none');
            }
            redraw();
        });
        examSelect && examSelect.addEventListener('change', function () {
            syncHelpState();
            if (!hasRequiredFilters()) {
                detailCard && (detailCard.style.display = 'none');
            }
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
        if (!hasRequiredFilters()) {
            detailCard && (detailCard.style.display = 'none');
        }
    })();
</script>
@endpush
@endif

@include('results.partials.print-script')

@if (session('role') === 'student' && !empty($useAjaxStudentResults) && !empty($canShowResult))
@push('scripts')
<script>
    (function () {
        if (!window.jQuery || !$.fn.DataTable) return;
        const tableEl = document.getElementById('student-results-table');
        if (!tableEl) return;

        $('#student-results-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            paging: false,
            info: false,
            ordering: false,
            ajax: {
                url: "{{ route('student.results') }}",
                data: function (d) {
                    d.session_id = "{{ (int) ($selectedSessionId ?? 0) }}";
                    d.exam_name = "{{ (string) ($selectedExamName ?? '') }}";
                    d.class_id = "{{ (int) ($selectedClassId ?? 0) }}";
                    d.section_id = "{{ (int) ($selectedSectionId ?? 0) }}";
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'subject_name', name: 'subject_name' },
                { data: 'grade_value', name: 'grade_value' },
                { data: 'total_mark_value', name: 'total_mark_value', className: 'text-center' },
                { data: 'passing_mark_value', name: 'passing_mark_value', className: 'text-center' },
                { data: 'obtained_mark_value', name: 'obtained_mark_value', className: 'text-center' },
            ],
        });
    })();
</script>
@endpush
@endif
