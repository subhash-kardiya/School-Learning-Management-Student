@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    @php
        $lineLabels = collect($attendanceWeekLabels ?? [])->values();
        $lineDataA = collect($attendancePresentWeekData ?? [])->values();
        $lineDataB = collect($attendanceAbsentWeekData ?? [])->values();
        $gradeLabels = collect($gradeClassLabels ?? [])->values();
        $gradeData = collect($gradeClassData ?? [])->values();
        $gradeLetters = collect($gradeClassLetterData ?? [])->values();
        $upcomingExams = collect($upcomingExams ?? [])->values();
        $recentStaff = collect($recentStaff ?? [])->values();
        $latestAdmissions = collect($latestAdmissions ?? [])->values();
        $calendarItems = collect($calendarItems ?? [])->values();
        $activityFeed = collect($activityFeed ?? [])->values();
        $topPerformers = collect($topPerformers ?? [])->values();
        $enrollmentLabels = collect($enrollmentLabels ?? [])->values();
        $enrollmentData = collect($enrollmentData ?? [])->values();
    @endphp


    <div class="adminx-dashboard">
        <div class="adminx-kpis">
            <a href="{{ route('students.index') }}" class="adminx-kpi-link" title="Go to Students">
                <div class="adminx-kpi-item is-highlight">
                    <small><i class="bi bi-mortarboard-fill me-1"></i>Total Students</small>
                    <strong class="kpi-animated"
                        data-value="{{ (int) ($studentCount ?? 0) }}">{{ number_format((int) ($studentCount ?? 0)) }}</strong>
                </div>
            </a>
            <a href="{{ route('teachers.index') }}" class="adminx-kpi-link" title="Go to Teachers">
                <div class="adminx-kpi-item">
                    <small><i class="bi bi-person-badge-fill me-1"></i>Total Teachers</small>
                    <strong class="kpi-animated"
                        data-value="{{ (int) ($teacherCount ?? 0) }}">{{ number_format((int) ($teacherCount ?? 0)) }}</strong>
                </div>
            </a>
            <a href="{{ route('attendance.index') }}" class="adminx-kpi-link" title="Go to Attendance">
                <div class="adminx-kpi-item">
                    <small><i class="bi bi-clipboard2-check me-1"></i>Daily Attendance</small>
                    <strong class="kpi-animated" data-value="{{ (float) ($dailyAttendancePct ?? 0) }}" data-decimals="1"
                        data-suffix="%">{{ number_format((float) ($dailyAttendancePct ?? 0), 1) }}%</strong>
                </div>
            </a>
            <a href="{{ route('students.index') }}" class="adminx-kpi-link" title="Go to Recent Admissions">
                <div class="adminx-kpi-item">
                    <small><i class="bi bi-person-plus-fill me-1"></i>Recent Admissions</small>
                    <strong class="kpi-animated"
                        data-value="{{ (int) ($recentAdmissionsCount ?? 0) }}">{{ number_format((int) ($recentAdmissionsCount ?? 0)) }}</strong>
                </div>
            </a>
            <a href="{{ route('exams.schedule') }}" class="adminx-kpi-link" title="Go to Academic Data">
                <div class="adminx-kpi-item">
                    <small><i class="bi bi-award-fill me-1"></i>Academic Average</small>
                    <strong>{{ $academicAverage ?? 'B+' }}</strong>
                </div>
            </a>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="adminx-card p-3 mb-3">
                    <div class="adminx-card-head">
                        <div>
                            <h5>Student Attendance Rate</h5>
                            <p>This week (Mon-Sat) monitoring of present vs absent students</p>
                        </div>
                        <div class="adminx-card-legend">Mon-Sat: {{ $attendanceLast7Range ?? '-' }}</div>
                    </div>
                    <div class="adminx-attendance-meta">
                        <div class="adminx-att-chip is-present">
                            <span>Present</span>
                            <strong>{{ number_format((int) ($attendanceLast7PresentTotal ?? 0)) }}</strong>
                        </div>
                        <div class="adminx-att-chip is-absent">
                            <span>Absent</span>
                            <strong>{{ number_format((int) ($attendanceLast7AbsentTotal ?? 0)) }}</strong>
                        </div>
                    </div>
                    <div class="adminx-chart-wrap adminx-chart-wrap-line"><canvas id="attendanceChart"></canvas></div>
                </div>

                <div class="adminx-card p-3 mb-3">
                    <div class="adminx-card-head">
                        <div>
                            <h5>Grade Trends</h5>
                            <p>Class 1 to 10 overall grade with school-level benchmark</p>
                        </div>
                        <div class="adminx-grade">{{ $overallGradeLabel ?? 'B+' }} <small>Overall</small></div>
                    </div>
                    <div class="adminx-grade-insights">
                        <span class="grade-chip is-top">Top: {{ $topGradeClass ?? '-' }}
                            ({{ $topGradeLabel ?? '-' }})</span>
                        <span class="grade-chip is-low">Lowest: {{ $lowGradeClass ?? '-' }}
                            ({{ $lowGradeLabel ?? '-' }})</span>
                    </div>
                    <div class="adminx-chart-wrap adminx-chart-wrap-bar"><canvas id="gradeTrendChart"></canvas></div>
                </div>

                <div class="adminx-card p-3 mb-3 adminx-admission-card">
                    <div class="adminx-card-head">
                        <div>
                            <h5 class="adminx-inline-title">New Admissions
                                <small>You have {{ $latestAdmissions->count() }} student records in review list</small>
                            </h5>
                            <div class="adminx-admission-submeta">
                                <span class="adminx-submeta-chip">Last 5 Students</span>
                            </div>
                        </div>
                        <a href="{{ route('students.create') }}" class="btn btn-primary">+ Enroll Student</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table adminx-table adminx-admission-table mb-0">
                            <thead>
                                <tr>
                                    <th class="col-customer">Student Name</th>
                                    <th class="col-basic">Basic Information</th>
                                    <th class="col-company">Standard Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestAdmissions as $row)
                                    <tr>
                                        <td>
                                            <div class="adminx-person">
                                                @if (!empty($row['profile_image']))
                                                    @php
                                                        $img = str_starts_with((string) $row['profile_image'], 'http')
                                                            ? $row['profile_image']
                                                            : asset($row['profile_image']);
                                                    @endphp
                                                    <img src="{{ $img }}" alt="{{ $row['name'] }}"
                                                        class="adminx-student-photo">
                                                @else
                                                    <span
                                                        class="avatar">{{ strtoupper(substr($row['name'], 0, 1)) }}</span>
                                                @endif
                                                <div>
                                                    <strong>{{ $row['name'] }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="adminx-student-basic">
                                                <span><i class="bi bi-telephone me-1"></i>{{ $row['mobile_no'] }}</span>
                                                <span><i
                                                        class="bi bi-calendar-event me-1"></i>{{ $row['admission_date'] }}</span>
                                            </div>
                                        </td>
                                        <td class="adminx-company-cell">
                                            <strong>{{ $row['company'] }}</strong>
                                            <small class="company-meta">Section {{ $row['section'] }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No admission records.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 adminx-right-stack">
                <div class="adminx-card p-3 mb-3 adminx-upcoming-card">
                    <div class="adminx-card-head">
                        <h5>Upcoming Exams</h5>
                    </div>
                    <div class="adminx-upcoming-viewport is-auto">
                        <ul class="adminx-list adminx-upcoming-list" id="upcomingExamList">
                            @forelse($upcomingExams as $exam)
                                <li class="adminx-up-item">
                                    <div class="adminx-up-icon">
                                        <i class="bi bi-journal-richtext"></i>
                                    </div>
                                    <div class="adminx-up-content">
                                        <strong>{{ $exam['title'] }}</strong>
                                        <small><i class="bi bi-calendar-event me-1"></i>{{ $exam['date'] }}</small>
                                    </div>
                                    <span class="adminx-up-badge">{{ $exam['meta'] ?? 'Schedule' }}</span>
                                </li>
                            @empty
                                <li class="text-muted">No upcoming exams.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="adminx-card p-3 mb-3 adminx-staff-card">
                    <div class="adminx-card-head">
                        <h5>Recently Joined Staff</h5>
                    </div>
                    @forelse($recentStaff as $staff)
                        <div class="adminx-staff-row">
                            <span class="avatar">{{ strtoupper(substr($staff['name'], 0, 1)) }}</span>
                            <div>
                                <strong>{{ $staff['name'] }}</strong>
                                <small><i class="bi bi-patch-check me-1"></i>{{ $staff['qualification'] }}</small>
                            </div>
                            <small class="adminx-exp-badge">{{ $staff['exp'] }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No staff data.</p>
                    @endforelse
                </div>

                <div class="adminx-card p-3 mb-3">
                    <div class="adminx-card-head">
                        <h5>Student Enrollment By Department / Level</h5>
                    </div>
                    <div class="adminx-chart-wrap adminx-chart-wrap-donut"><canvas id="enrollmentChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="adminx-card p-3 h-100 adminx-calendar-card">
                    <div class="adminx-card-head">
                        <h5>School Calendar</h5>
                    </div>
                    <div class="adminx-mini-cal" id="adminMiniCalendar">
                        <div class="adminx-mini-cal-head">
                            <button type="button" class="adminx-mini-cal-btn" id="calPrev"><i
                                    class="bi bi-chevron-left"></i></button>
                            <strong id="calMonthLabel">Month 2026</strong>
                            <button type="button" class="adminx-mini-cal-btn" id="calNext"><i
                                    class="bi bi-chevron-right"></i></button>
                        </div>
                        <div class="adminx-mini-cal-weekdays">
                            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                        </div>
                        <div class="adminx-mini-cal-grid" id="calGrid"></div>
                    </div>
                    <div class="adminx-mini-events" id="calEventsWrap">
                        <small class="text-muted">Click any date to add event. Events save in this browser.</small>
                        <ul id="calEventsList"></ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="adminx-card p-3 h-100 adminx-activity-card">
                    <div class="adminx-card-head">
                        <h5>Activities</h5>
                    </div>
                    <ul class="adminx-activity">
                        @forelse($activityFeed->take(6) as $act)
                            <li>
                                <span class="dot"></span>
                                <div>
                                    <strong>{{ $act['actor'] }}</strong>
                                    <small>{{ $act['text'] }}</small>
                                </div>
                                <small>{{ $act['time'] }}</small>
                            </li>
                        @empty
                            <li class="text-muted">No recent activities.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="adminx-card p-3 h-100 adminx-top-card">
                    <div class="adminx-card-head">
                        <h5>Top Performer</h5>
                    </div>
                    <div class="adminx-top-list">
                        @forelse($topPerformers as $top)
                            <div class="adminx-top-item">
                                @if (!empty($top['profile_image']))
                                    @php
                                        $topImg = str_starts_with((string) $top['profile_image'], 'http')
                                            ? $top['profile_image']
                                            : asset('storage/' . ltrim((string) $top['profile_image'], '/'));
                                    @endphp
                                    <img src="{{ $topImg }}" alt="{{ $top['name'] }}"
                                        class="top-avatar top-avatar-photo">
                                @else
                                    <span
                                        class="avatar top-avatar top-avatar-{{ ($loop->index % 5) + 1 }}">{{ strtoupper(substr($top['name'], 0, 1)) }}</span>
                                @endif
                                <div class="adminx-top-main">
                                    <strong>{{ $top['name'] }}</strong>
                                    <small>{{ $top['std'] ?? 'N/A' }}</small>
                                </div>
                                <span class="adminx-top-score">{{ $top['score'] }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No performer data.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admindeshboard.css') }}">
@endpush

@push('scripts')
    <script>
        (function() {
            function loadScript(src, onLoad) {
                const s = document.createElement('script');
                s.src = src;
                s.async = true;
                s.onload = onLoad;
                document.head.appendChild(s);
            }

            function animateKpis() {
                const nodes = document.querySelectorAll('.kpi-animated');
                nodes.forEach((node) => {
                    const target = Number(node.dataset.value || 0);
                    const decimals = Number(node.dataset.decimals || 0);
                    const suffix = node.dataset.suffix || '';
                    const duration = 900;
                    const start = performance.now();

                    function draw(now) {
                        const progress = Math.min((now - start) / duration, 1);
                        const eased = 1 - Math.pow(1 - progress, 3);
                        const current = target * eased;
                        const text = decimals > 0 ?
                            current.toFixed(decimals) :
                            Math.round(current).toLocaleString();
                        node.textContent = text + suffix;
                        if (progress < 1) requestAnimationFrame(draw);
                    }

                    requestAnimationFrame(draw);
                });
            }

            function initCharts() {
                if (typeof Chart === 'undefined') return;

                const lineLabels = @json($lineLabels);
                const lineDataA = @json($lineDataA);
                const lineDataB = @json($lineDataB);
                const gradeLabels = @json($gradeLabels);
                const gradeData = @json($gradeData);
                const gradeLetters = @json($gradeLetters);
                const overallGradePct = Number(@json($overallGradePct ?? 0));
                const gradeToPoint = {
                    'C': 1,
                    'C+': 2,
                    'B': 3,
                    'B+': 4,
                    'A': 5,
                    'A+': 6
                };
                const pointToGrade = {
                    1: 'C',
                    2: 'C+',
                    3: 'B',
                    4: 'B+',
                    5: 'A',
                    6: 'A+'
                };
                const classGradePoints = gradeLetters.map((g) => gradeToPoint[g] ?? 1);
                const overallGradePoint = (() => {
                    if (overallGradePct >= 90) return 6;
                    if (overallGradePct >= 80) return 5;
                    if (overallGradePct >= 70) return 4;
                    if (overallGradePct >= 60) return 3;
                    if (overallGradePct >= 50) return 2;
                    return 1;
                })();
                const enrollmentLabels = @json($enrollmentLabels);
                const enrollmentData = @json($enrollmentData);
                const enrollmentShortLabels = enrollmentLabels.map((label) => {
                    const txt = String(label || '');
                    if (txt.includes('High School')) return 'High School';
                    if (txt.includes('Middle School')) return 'Middle School';
                    return 'Elementary';
                });
                const grid = {
                    color: '#e4e8ef'
                };

                const attendanceCanvas = document.getElementById('attendanceChart');
                const gradeCanvas = document.getElementById('gradeTrendChart');
                const enrollmentCanvas = document.getElementById('enrollmentChart');
                const upcomingExamList = document.getElementById('upcomingExamList');
                if (!attendanceCanvas || !gradeCanvas || !enrollmentCanvas || typeof Chart === 'undefined') return;
                if (upcomingExamList && upcomingExamList.parentElement?.classList.contains('is-auto') &&
                    upcomingExamList.children.length > 1) {
                    upcomingExamList.insertAdjacentHTML('beforeend', upcomingExamList.innerHTML);
                }

                new Chart(attendanceCanvas, {
                    type: 'line',
                    data: {
                        labels: lineLabels,
                        datasets: [{
                                label: 'Present',
                                data: lineDataA,
                                borderColor: '#1f43b5',
                                backgroundColor: 'rgba(31,67,181,.18)',
                                tension: .42,
                                fill: true,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#1f43b5',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5
                            },
                            {
                                label: 'Absent',
                                data: lineDataB,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,.04)',
                                tension: .3,
                                fill: false,
                                borderWidth: 3,
                                borderDash: [8, 5],
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                pointStyle: 'rectRounded',
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#ef4444',
                                pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 12,
                                    padding: 14
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            x: {
                                grid
                            },
                            y: {
                                grid,
                                beginAtZero: true
                            }
                        }
                    }
                });

                new Chart(gradeCanvas, {
                    type: 'bar',
                    data: {
                        labels: gradeLabels,
                        datasets: [{
                                label: 'Class Overall %',
                                data: classGradePoints,
                                backgroundColor: 'rgba(58, 167, 255, 0.85)',
                                borderColor: '#2a64cc',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                barThickness: 16
                            },
                            {
                                type: 'line',
                                label: 'All Classes Overall Grade',
                                data: gradeLabels.map(() => overallGradePoint),
                                borderColor: '#17388f',
                                borderWidth: 2.5,
                                borderDash: [6, 4],
                                pointRadius: 0,
                                tension: 0,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 12,
                                    padding: 12
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: (context) => {
                                        const gradeText = pointToGrade[Math.round(Number(context.parsed
                                            .y))] ?? 'C';
                                        if (context.datasetIndex === 0) {
                                            const pct = Number(gradeData[context.dataIndex] ?? 0).toFixed(
                                                1);
                                            return `Class Grade: ${gradeText} (${pct}%)`;
                                        }
                                        return `Overall: ${gradeText}`;
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            x: {
                                grid
                            },
                            y: {
                                grid,
                                beginAtZero: true,
                                min: 1,
                                max: 6,
                                ticks: {
                                    stepSize: 1,
                                    callback: (value) => pointToGrade[value] ?? ''
                                }
                            }
                        }
                    }
                });

                new Chart(enrollmentCanvas, {
                    type: 'pie',
                    data: {
                        labels: enrollmentShortLabels.length ? enrollmentShortLabels : ['No Data'],
                        datasets: [{
                            data: enrollmentData.length ? enrollmentData : [1],
                            backgroundColor: ['#1f3fa8', '#55b6ec', '#6fd9c3'],
                            borderColor: '#f8f9fb',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                maxHeight: 58,
                                labels: {
                                    boxWidth: 10,
                                    font: {
                                        size: 10
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'rectRounded',
                                    padding: 12,
                                    generateLabels: (chart) => {
                                        const labels = chart.data.labels || [];
                                        const values = chart.data.datasets?.[0]?.data || [];
                                        const colors = chart.data.datasets?.[0]?.backgroundColor || [];
                                        return labels.map((label, idx) => ({
                                            text: `${label} (${Number(values[idx] || 0)})`,
                                            fillStyle: colors[idx] || '#94a3b8',
                                            strokeStyle: colors[idx] || '#94a3b8',
                                            hidden: false,
                                            index: idx
                                        }));
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const value = Number(context.parsed || 0);
                                        const fullLabel = enrollmentLabels[context.dataIndex] || context
                                            .label;
                                        return `${fullLabel}: ${value} students`;
                                    }
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'pieSliceLabels',
                        afterDatasetsDraw(chart) {
                            const {
                                ctx
                            } = chart;
                            const dataset = chart.data.datasets[0];
                            const meta = chart.getDatasetMeta(0);
                            if (!dataset || !meta || !meta.data?.length) return;

                            const values = (dataset.data || []).map((v) => Number(v || 0));
                            const total = values.reduce((sum, v) => sum + v, 0);
                            if (total <= 0) return;

                            ctx.save();
                            ctx.font = '700 12px Manrope, Segoe UI, sans-serif';
                            ctx.fillStyle = '#ffffff';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            meta.data.forEach((arc, idx) => {
                                const value = values[idx] || 0;
                                if (value <= 0) return;
                                const label = String(chart.data.labels?.[idx] || '');
                                const shortLabel = label.includes('High School') ?
                                    'High' :
                                    (label.includes('Middle School') ? 'Middle' : 'Elementary');
                                const pos = arc.tooltipPosition();
                                ctx.fillText(shortLabel, pos.x, pos.y - 7);
                                ctx.fillText(String(value), pos.x, pos.y + 8);
                            });

                            ctx.restore();
                        }
                    }]
                });
            }

            function initLocalCalendar() {
                const wrap = document.getElementById('adminMiniCalendar');
                if (!wrap) return;

                const grid = document.getElementById('calGrid');
                const label = document.getElementById('calMonthLabel');
                const prevBtn = document.getElementById('calPrev');
                const nextBtn = document.getElementById('calNext');
                const eventsList = document.getElementById('calEventsList');
                const storageKey = 'admin_dashboard_calendar_events_v1';
                const today = new Date();
                let viewYear = today.getFullYear();
                let viewMonth = today.getMonth();
                let selectedDateKey = '';

                function readStore() {
                    try {
                        const raw = localStorage.getItem(storageKey);
                        return raw ? JSON.parse(raw) : {};
                    } catch (e) {
                        return {};
                    }
                }

                function writeStore(data) {
                    localStorage.setItem(storageKey, JSON.stringify(data));
                }

                function dateKey(y, m, d) {
                    const mm = String(m + 1).padStart(2, '0');
                    const dd = String(d).padStart(2, '0');
                    return `${y}-${mm}-${dd}`;
                }

                function renderEvents() {
                    const store = readStore();
                    const items = selectedDateKey ? (store[selectedDateKey] || []) : [];
                    if (!eventsList) return;
                    if (!items.length) {
                        eventsList.innerHTML = '<li class="text-muted">No events for selected date.</li>';
                        return;
                    }
                    eventsList.innerHTML = items.map((txt, idx) =>
                        `<li><span>${txt}</span><button type="button" data-idx="${idx}" class="adminx-event-del">&times;</button></li>`
                    ).join('');
                }

                function renderCalendar() {
                    const first = new Date(viewYear, viewMonth, 1);
                    const last = new Date(viewYear, viewMonth + 1, 0);
                    const startWeekday = first.getDay();
                    const totalDays = last.getDate();
                    const monthName = first.toLocaleString('en-US', {
                        month: 'long'
                    });
                    label.textContent = `${monthName} ${viewYear}`;

                    const store = readStore();
                    let html = '';
                    for (let i = 0; i < startWeekday; i++) {
                        html += '<button type="button" class="is-empty" disabled></button>';
                    }
                    for (let d = 1; d <= totalDays; d++) {
                        const key = dateKey(viewYear, viewMonth, d);
                        const hasEvents = Array.isArray(store[key]) && store[key].length > 0;
                        const isToday = key === dateKey(today.getFullYear(), today.getMonth(), today.getDate());
                        const isSelected = key === selectedDateKey;
                        html +=
                            `<button type="button" class="adminx-day ${hasEvents ? 'has-event' : ''} ${isToday ? 'is-today' : ''} ${isSelected ? 'is-selected' : ''}" data-date="${key}">${d}</button>`;
                    }
                    grid.innerHTML = html;
                }

                prevBtn?.addEventListener('click', () => {
                    viewMonth -= 1;
                    if (viewMonth < 0) {
                        viewMonth = 11;
                        viewYear -= 1;
                    }
                    renderCalendar();
                });

                nextBtn?.addEventListener('click', () => {
                    viewMonth += 1;
                    if (viewMonth > 11) {
                        viewMonth = 0;
                        viewYear += 1;
                    }
                    renderCalendar();
                });

                grid?.addEventListener('click', (e) => {
                    const btn = e.target.closest('.adminx-day');
                    if (!btn) return;
                    selectedDateKey = btn.dataset.date || '';
                    renderCalendar();
                    const title = prompt(`Add event for ${selectedDateKey}:`);
                    if (title && title.trim() !== '') {
                        const store = readStore();
                        if (!Array.isArray(store[selectedDateKey])) {
                            store[selectedDateKey] = [];
                        }
                        store[selectedDateKey].push(title.trim());
                        writeStore(store);
                    }
                    renderEvents();
                    renderCalendar();
                });

                eventsList?.addEventListener('click', (e) => {
                    const delBtn = e.target.closest('.adminx-event-del');
                    if (!delBtn || !selectedDateKey) return;
                    const idx = Number(delBtn.dataset.idx);
                    const store = readStore();
                    if (!Array.isArray(store[selectedDateKey])) return;
                    store[selectedDateKey].splice(idx, 1);
                    if (!store[selectedDateKey].length) {
                        delete store[selectedDateKey];
                    }
                    writeStore(store);
                    renderEvents();
                    renderCalendar();
                });

                selectedDateKey = dateKey(today.getFullYear(), today.getMonth(), today.getDate());
                renderCalendar();
                renderEvents();
            }

            animateKpis();
            initLocalCalendar();

            if (typeof Chart !== 'undefined') {
                initCharts();
                return;
            }

            loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', function() {
                if (typeof Chart !== 'undefined') {
                    initCharts();
                    return;
                }
                loadScript('https://unpkg.com/chart.js@4.4.1/dist/chart.umd.min.js', function() {
                    initCharts();
                });
            });
        })();
    </script>
@endpush
