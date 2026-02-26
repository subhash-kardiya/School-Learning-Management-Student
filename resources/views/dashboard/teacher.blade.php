@extends('layouts.admin')

@section('title', 'Teacher Dashboard')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/teacher-dashboard.css') }}">
@endpush

@section('content')
    @php
        $performanceLabels = collect($classAttendanceLabels ?? [])->values();
        $studyData = collect($classAttendancePresentData ?? [])->values();
        $assessmentData = collect($classAttendanceAbsentData ?? [])->values();
        if ($performanceLabels->isEmpty()) {
            $performanceLabels = collect(['Std 5-A', 'Std 6-B', 'Std 7-A', 'Std 8-C'])->values();
            $studyData = collect([28, 24, 31, 26])->values();
            $assessmentData = collect([6, 5, 4, 7])->values();
        }

        $teacherAssignedClasses = (int) ($assignedClassesCount ?? 0);
        $teacherStudents = (int) ($myStudentsCount ?? 0);
        $teacherLecturesToday = (int) ($todaysLecturesCount ?? 0);
        $teacherAttendanceStatus = (string) ($attendanceStatusSummary ?? '0/0 Completed');
        $teacherPendingReview = (int) ($pendingReviewCount ?? 0);

        $teacherTodaySchedule = collect($todaySchedule ?? [])->values();
        $teacherMyClasses = collect($myClassRows ?? [])->values();
        $teacherRecentSubmissions = collect($recentSubmissions ?? [])->values();
        $teacherAnnouncements = collect($latestAnnouncements ?? [])->values();
        $teacherQuickActions = collect($quickActions ?? [])->values();
    @endphp

    <div class="td2-shell">
        <div class="td2-top">
            <div class="td2-top-card c1">
                <i class="bi bi-journals"></i>
                <strong>{{ $teacherAssignedClasses }}</strong>
                <span>Total Classes</span>
            </div>
            <div class="td2-top-card c2">
                <i class="bi bi-mortarboard"></i>
                <strong>{{ $teacherStudents }}</strong>
                <span>Total Students</span>
            </div>
            <div class="td2-top-card c3">
                <i class="bi bi-calendar3"></i>
                <strong>{{ $teacherLecturesToday }}</strong>
                <span>Today's Lectures</span>
            </div>

            <div class="td2-top-card c4">
                <i class="bi bi-journal-check"></i>
                <strong>{{ $teacherPendingReview }}</strong>
                <span>Pending Review</span>
            </div>
        </div>

        <div class="td2-main">
            <div class="td2-card td2-performance">
                <div class="td2-head">
                    <h6>Today Present vs Absent (Assigned Classes)</h6>
                    <a href="#">View All</a>
                </div>
                <canvas id="tdStudentPerformance"></canvas>
            </div>
            <div class="td2-card td2-attendance">
                <div class="td2-head">
                    <h6>Today's Lectures</h6>
                    <a href="#">View All</a>
                </div>
                <div class="td2-lecture-meta">
                    <span>{{ $teacherLecturesToday }} lectures today</span>
                </div>
                @if ($teacherTodaySchedule->isNotEmpty())
                    <div class="td2-lecture-scroll">
                        <div class="td2-lecture-track">
                            <div class="td2-lecture-group">
                                @foreach ($teacherTodaySchedule as $lecture)
                                    <article class="td2-lecture-item">
                                        <div class="td2-lecture-title">{{ $lecture['subject'] }}</div>
                                        <div class="td2-lecture-sub">{{ $lecture['class'] }} | Room {{ $lecture['room'] }}
                                        </div>
                                        <div class="td2-lecture-time">{{ $lecture['time'] }}</div>
                                    </article>
                                @endforeach
                            </div>
                            <div class="td2-lecture-group" aria-hidden="true">
                                @foreach ($teacherTodaySchedule as $lecture)
                                    <article class="td2-lecture-item">
                                        <div class="td2-lecture-title">{{ $lecture['subject'] }}</div>
                                        <div class="td2-lecture-sub">{{ $lecture['class'] }} | Room {{ $lecture['room'] }}
                                        </div>
                                        <div class="td2-lecture-time">{{ $lecture['time'] }}</div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="td2-lecture-empty">No lectures scheduled for today.</div>
                @endif
            </div>
        </div>

        <div class="td2-bottom">
            <div class="td2-card td2-actions">
                <div class="td2-head">
                    <h6>Quick Actions</h6>
                </div>
                <div class="td2-action-grid">
                    @foreach ($teacherQuickActions as $action)
                        <a href="{{ $action['url'] }}" class="td2-action-btn">
                            <i class="bi {{ $action['icon'] }}"></i>
                            <span>{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                    @if ($teacherQuickActions->isEmpty())
                        <div class="td2-lecture-empty">No actions available.</div>
                    @endif
                </div>
            </div>

            <div class="td2-card td2-classes">
                <div class="td2-head">
                    <h6>My Classes</h6>
                    <a href="#">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($teacherMyClasses as $row)
                                <tr>
                                    <td>{{ $row['class'] }}</td>
                                    <td>{{ $row['subject'] }}</td>
                                    <td>{{ $row['students'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No class mapping found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="td2-card td2-submissions">
                <div class="td2-head">
                    <h6>Recent Submissions</h6>
                    <a href="#">View All</a>
                </div>
                <ul class="td2-sub-list">
                    @forelse ($teacherRecentSubmissions as $row)
                        <li>
                            <div>
                                <strong>{{ $row['student'] }}</strong>
                                <small>{{ $row['homework'] }}</small>
                            </div>
                            <span>{{ $row['submitted'] }}</span>
                        </li>
                    @empty
                        <li class="empty">No recent submissions.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="td2-lower">
            <div class="td2-card td2-table">
                <div class="td2-head">
                    <h6>Today's Time Table</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($teacherTodaySchedule as $row)
                                <tr>
                                    <td>{{ $row['time'] }}</td>
                                    <td>{{ $row['class'] }}</td>
                                    <td>{{ $row['subject'] }}</td>
                                    <td>{{ $row['room'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No timetable for today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="td2-card td2-notice">
                <div class="td2-head">
                    <h6>Announcements</h6>
                    <a href="#">View All</a>
                </div>
                <ul class="td2-notice-list">
                    @forelse ($teacherAnnouncements as $announcement)
                        <li>
                            <strong>{{ $announcement->title }}</strong>
                            <small>{{ \Illuminate\Support\Str::limit(strip_tags((string) $announcement->message), 90) }}</small>
                        </li>
                    @empty
                        <li class="empty">No announcements.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

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

            function initCharts() {
                if (typeof Chart === 'undefined') return;

                const performanceLabels = @json($performanceLabels);
                const studyData = @json($studyData);
                const assessmentData = @json($assessmentData);
                const perf = document.getElementById('tdStudentPerformance');
                if (perf) {
                    new Chart(perf, {
                        type: 'bar',
                        data: {
                            labels: performanceLabels,
                            datasets: [{
                                    label: 'Present',
                                    data: studyData,
                                    backgroundColor: '#5b6bfa',
                                    borderRadius: 4,
                                    categoryPercentage: 0.62,
                                    barPercentage: 0.9
                                },
                                {
                                    label: 'Absent',
                                    data: assessmentData,
                                    backgroundColor: '#f9b8c7',
                                    borderRadius: 4,
                                    categoryPercentage: 0.62,
                                    barPercentage: 0.9
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'start'
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#eef2f8'
                                    }
                                }
                            }
                        }
                    });
                }
            }

            if (typeof Chart !== 'undefined') return initCharts();
            loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', initCharts);
        })();
    </script>
@endpush
