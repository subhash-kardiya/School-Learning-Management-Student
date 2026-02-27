<div class="sidebar" id="sidebar-accordion">
    <div class="sidebar-brand">
        <button type="button" class="sidebar-collapse-btn" id="sidebarDesktopToggle" aria-label="Toggle sidebar">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <i class="fas fa-graduation-cap"></i>
        <span>School LMS</span>
    </div>

    @php
        $user = auth()->user();
        $sessionRole = strtolower((string) session('role'));
        $isAdmin = ($user && ($user->hasRole('admin') || $user->hasRole('Admin'))) || $sessionRole === 'admin';
        $isSuperAdmin = $user && (
            $user->hasRole('superadmin')
            || $user->hasRole('super admin')
            || $user->hasRole('super_admin')
            || $user->hasRole('Super Admin')
            || $user->hasRole('SuperAdmin')
        );
        $isSuperAdmin = $isSuperAdmin || $sessionRole === 'superadmin';
        $isStudent = ($user && ($user->hasRole('student') || $user->hasRole('Student'))) || $sessionRole === 'student';
        $isTeacher = ($user && ($user->hasRole('teacher') || $user->hasRole('Teacher'))) || $sessionRole === 'teacher';
        $isParent = ($user && ($user->hasRole('parent') || $user->hasRole('Parent'))) || $sessionRole === 'parent';
        $dashboardRoute = 'admin.dashboard';
        if ($isTeacher) {
            $dashboardRoute = 'teacher.dashboard';
        } elseif ($isStudent) {
            $dashboardRoute = 'student.dashboard';
        } elseif ($isParent) {
            $dashboardRoute = 'parent.dashboard';
        }
        $masterRoutes = [
            'academic.year.*',
            'classes.*',
            'section.*',
            'subjects.*',
            'roles.*',
            'teachers.*',
            'students.*',
            'rooms.*',
            'teacher.mapping*',
            'certificate.*',
        ];
        $masterPermissions = [
            'academic_year_manage',
            'class_manage',
            'room_manage',
            'section_manage',
            'subject_manage',
            'role_view',
            'role_add',
            'role_edit',
            'role_delete',
            'teacher_view',
            'student_view',
            'certificate_manage',
        ];
        $showAdministrationSection = $isAdmin || $isSuperAdmin || \Illuminate\Support\Facades\Gate::any($masterPermissions);
        $showMasterMenu = $isSuperAdmin || \Illuminate\Support\Facades\Gate::any($masterPermissions);
        $attendanceMarkRoute = $isTeacher ? 'teacher.attendance.mark' : 'attendance.mark';
        $attendanceIndexRoute = 'attendance.index';
        $homeworkCreateRoute = $isTeacher ? 'teacher.homework.create' : 'homework.create';
        $homeworkListRoute = $isTeacher ? 'teacher.homework.list' : 'homework.list';
        $examTypeRoute = $isTeacher ? 'teacher.exams.type' : 'exams.type';
        $examScheduleRoute = $isTeacher ? 'teacher.exams.schedule' : 'exams.schedule';
        $examMarksRoute = $isTeacher ? 'teacher.exams.marks' : 'exams.marks';
        $resultsRoute = $isTeacher ? 'teacher.results.index' : 'results.index';
        $announcementsRoute = 'communication.announcements';
        if ($isTeacher) {
            $announcementsRoute = 'teacher.communication.announcements';
        } elseif ($isStudent) {
            $announcementsRoute = 'student.communication.announcements';
        } elseif ($isParent) {
            $announcementsRoute = 'parent.communication.announcements';
        }
    @endphp

    <a href="{{ route($dashboardRoute) }}"
        class="d-flex align-items-center {{ request()->routeIs($dashboardRoute) ? 'current-page' : '' }}">
        <span>
            <i class="fa-solid fa-table-cells-large fs-5"></i>
            <span>Dashboard</span>
        </span>
    </a>

    @if ($showAdministrationSection)
        <div class="sidebar-section-title">Administration</div>

        @if ($isAdmin || $isSuperAdmin)
            @php
                $masterActive = request()->routeIs($masterRoutes);
            @endphp
            @if ($showMasterMenu)
                <a href="#submenu-master" data-bs-toggle="collapse"
                    class="d-flex justify-content-between align-items-center {{ $masterActive ? 'active' : '' }}">
                    <span>
                        <i class="bi bi-bounding-box fs-5"></i>
                        <span>master</span>
                    </span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>

                <div class="collapse {{ $masterActive ? 'show' : '' }}" id="submenu-master" data-bs-parent="#sidebar-accordion">
                    @include('layouts.partials.master-menu-items')
                </div>
            @endif
        @endif

        @if ($isStudent)
            @canany($masterPermissions)
                @php
                    $studentMenuActive = !$isAdmin && !$isSuperAdmin && request()->routeIs($masterRoutes);
                @endphp
                <a href="#submenu-student" data-bs-toggle="collapse"
                    class="d-flex justify-content-between align-items-center {{ $studentMenuActive ? 'active' : '' }}">
                    <span>
                        <i class="fa-solid fa-user-graduate fs-5"></i>
                        <span>Student</span>
                    </span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <div class="collapse {{ $studentMenuActive ? 'show' : '' }}" id="submenu-student" data-bs-parent="#sidebar-accordion">
                    @include('layouts.partials.master-menu-items')
                </div>
            @endcanany
        @endif

        @if ($isTeacher)
            @canany($masterPermissions)
                @php
                    $teacherMenuActive = !$isAdmin && !$isSuperAdmin && request()->routeIs($masterRoutes);
                @endphp
                <a href="#submenu-teacher" data-bs-toggle="collapse"
                    class="d-flex justify-content-between align-items-center {{ $teacherMenuActive ? 'active' : '' }}">
                    <span>
                        <i class="fa-solid fa-chalkboard-user fs-5"></i>
                        <span>Teacher</span>
                    </span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <div class="collapse {{ $teacherMenuActive ? 'show' : '' }}" id="submenu-teacher" data-bs-parent="#sidebar-accordion">
                    @include('layouts.partials.master-menu-items')
                </div>
            @endcanany
        @endif

        @if ($isParent)
            @canany($masterPermissions)
                @php
                    $parentMenuActive = !$isAdmin && !$isSuperAdmin && request()->routeIs($masterRoutes);
                @endphp
                <a href="#submenu-parent" data-bs-toggle="collapse"
                    class="d-flex justify-content-between align-items-center {{ $parentMenuActive ? 'active' : '' }}">
                    <span>
                        <i class="fa-solid fa-users fs-5"></i>
                        <span>Parent</span>
                    </span>
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <div class="collapse {{ $parentMenuActive ? 'show' : '' }}" id="submenu-parent" data-bs-parent="#sidebar-accordion">
                    @include('layouts.partials.master-menu-items')
                </div>
            @endcanany
        @endif
    @endif

    <div class="sidebar-section-title">Academics</div>

    @php
        $timetableActive = request()->routeIs('timetable.*', 'teacher.timetable*', 'student.timetable*', 'parent.timetable*');
    @endphp
    @if ($isStudent)
        @can('timetable_student_view')
            <a href="{{ route('student.timetable') }}"
                class="d-flex align-items-center {{ request()->routeIs('student.timetable*') ? 'current-page' : '' }}">
                <i class="fas fa-calendar-alt fs-5 me-1"></i>
                <span>My Timetable</span>
            </a>
        @endcan
    @elseif ($isParent)
        @can('timetable_student_view')
            <a href="{{ route('parent.timetable') }}"
                class="d-flex align-items-center {{ request()->routeIs('parent.timetable*') ? 'current-page' : '' }}">
                <i class="fas fa-calendar-alt fs-5 me-1"></i>
                <span>Child Timetable</span>
            </a>
        @endcan
    @else
        @canany(['timetable_view', 'timetable_teacher_view', 'timetable_create', 'timetable_edit', 'timetable_delete'])
            <a href="#submenu-timetable" data-bs-toggle="collapse"
                class="d-flex justify-content-between align-items-center {{ $timetableActive ? 'active' : '' }}">
                <span>
                    <i class="bi bi-card-text fs-5"></i>
                    <span>TimeTable</span>
                </span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="collapse {{ $timetableActive ? 'show' : '' }}" id="submenu-timetable" data-bs-parent="#sidebar-accordion">
                @canany(['timetable_view', 'timetable_create', 'timetable_edit', 'timetable_delete'])
                    <a href="{{ route('timetable.class') }}"
                        class="ps-5 submenu {{ request()->routeIs('timetable.class*') ? 'current-page' : '' }}">
                        <i class="fa-regular fa-clock"></i> Class Time Table
                    </a>
                @endcanany
                @can('timetable_teacher_view')
                    @php
                        $teacherTimetableRoute = $isTeacher ? 'teacher.timetable' : 'timetable.teacher';
                    @endphp
                    <a href="{{ route($teacherTimetableRoute) }}"
                        class="ps-5 submenu {{ request()->routeIs('timetable.teacher*', 'teacher.timetable*') ? 'current-page' : '' }}">
                        <i class="fa-regular fa-clock"></i> Teacher Time Table
                    </a>
                @endcan
            </div>
        @endcanany
    @endif

    @php
        $attendanceActive = request()->routeIs('attendance.*', 'teacher.attendance.*', 'student.attendance', 'parent.attendance');
        $canSeeAttendance =
            $isStudent || $isParent ||
            ($user && method_exists($user, 'hasPermission') && (
                $user->hasPermission('attendance_mark') ||
                $user->hasPermission('attendance_view') ||
                $user->hasPermission('attendance_report')
            ));
    @endphp
    @if ($canSeeAttendance)
        <a href="#submenu-attendance" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $attendanceActive ? 'active' : '' }}">
            <span>
                <i class="bi bi-graph-up fs-5"></i>
                <span>Attendance</span>
            </span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>
        <div class="collapse {{ $attendanceActive ? 'show' : '' }}" id="submenu-attendance" data-bs-parent="#sidebar-accordion">
            @if ($isStudent)
                <a href="{{ route('student.attendance') }}"
                    class="ps-5 submenu {{ request()->routeIs('student.attendance') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-eye"></i> My Attendance
                </a>
            @endif
            @if ($isParent)
                <a href="{{ route('parent.attendance') }}"
                    class="ps-5 submenu {{ request()->routeIs('parent.attendance') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-eye"></i> Child Attendance
                </a>
            @endif
            @can('attendance_mark')
                <a href="{{ route($attendanceMarkRoute) }}"
                    class="ps-5 submenu {{ request()->routeIs('attendance.mark*', 'teacher.attendance.mark*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-check-circle"></i> Mark Attendance
                </a>
            @endcan
            @if (!$isStudent && !$isParent && !$isTeacher)
                @can('attendance_view')
                <a href="{{ route($attendanceIndexRoute) }}"
                    class="ps-5 submenu {{ request()->routeIs('attendance.index') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-list"></i> Attendance Report
                </a>
                @endcan
            @endif
        </div>
    @endif

    @php
        $homeworkActive = request()->routeIs('homework.*', 'teacher.homework.*', 'student.homework.*', 'parent.homework.*');
        $canSeeHomework =
            $isStudent || $isParent ||
            ($user && method_exists($user, 'hasPermission') && (
                $user->hasPermission('homework_create') ||
                $user->hasPermission('homework_list') ||
                $user->hasPermission('homework_submission')
            ));
    @endphp
    @if ($canSeeHomework)
        <a href="#submenu-homework" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $homeworkActive ? 'active' : '' }}">
            <span>
                <i class="bi bi-grid-1x2 fs-5"></i>
                <span>Homework</span>
            </span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>
        <div class="collapse {{ $homeworkActive ? 'show' : '' }}" id="submenu-homework" data-bs-parent="#sidebar-accordion">
            @if ($isStudent)
                <a href="{{ route('student.homework.list') }}"
                    class="ps-5 submenu {{ request()->routeIs('student.homework.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-list"></i> My Homework
                </a>
            @endif
            @if ($isParent)
                <a href="{{ route('parent.homework.list') }}"
                    class="ps-5 submenu {{ request()->routeIs('parent.homework.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-eye"></i> Homework Status
                </a>
            @endif
            @if ($isTeacher && $user && method_exists($user, 'hasPermission') && $user->hasPermission('homework_create'))
                <a href="{{ route($homeworkCreateRoute) }}"
                    class="ps-5 submenu {{ request()->routeIs('homework.create*', 'teacher.homework.create*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-plus-circle"></i> Create Homework
                </a>
            @endif
            @if (!$isStudent && !$isParent)
                <a href="{{ route($homeworkListRoute) }}"
                    class="ps-5 submenu {{ request()->routeIs('homework.list*', 'teacher.homework.list*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-list"></i> Homework List
                </a>
            @endif
        </div>
    @endif

    @php
        $examsActive = request()->routeIs('exams.*', 'teacher.exams.*');
    @endphp
    @canany(['exam_type', 'exam_schedule', 'marks_entry'])
        <a href="#submenu-exams" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $examsActive ? 'active' : '' }}">
            <span>
                <i class="bi bi-layers fs-5"></i>
                <span>Examination</span>
            </span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>
        <div class="collapse {{ $examsActive ? 'show' : '' }}" id="submenu-exams">
            @canany(['exam_type'])
                <a href="{{route('exams.createexam')}}"
                    class="ps-5 submenu {{ request()->routeIs('exams.create*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-plus-circle"></i> Create Exam
                </a>
            @endcanany
            @can('exam_schedule')
                <a href="{{ route($examScheduleRoute) }}"
                    class="ps-5 submenu {{ request()->routeIs('exams.schedule*', 'teacher.exams.schedule*') ? 'current-page' : '' }}">
                    <i class="fas fa-calendar-check"></i> Exam Schedule
                </a>
            @endcan
            @can('marks_entry')
                <a href="{{ route($examMarksRoute) }}"
                    class="ps-5 submenu {{ request()->routeIs('exams.marks*', 'teacher.exams.marks*') ? 'current-page' : '' }}">
                    <i class="fas fa-marker"></i> Marks Entry
                </a>
            @endcan
        </div>
    @endcanany

    <div class="sidebar-section-title">Reports</div>

    @can('result_view')
        @php
            $resultsRoute = ($isStudent ?? false) ? 'student.results' : 'results.index';
            $resultsActive = request()->routeIs('results.*') || request()->routeIs('student.results');
        @endphp
        <a href="{{ route($resultsRoute) }}"
            class="d-flex align-items-center {{ $resultsActive ? 'current-page' : '' }}">
            <i class="fa-solid fa-square-check fs-5 me-1"></i>
            <span>Results</span>
        </a>
    @endcan

    @if ($isStudent)
        <a href="{{ route('student.certificate.index') }}"
            class="d-flex align-items-center {{ request()->routeIs('student.certificate.*') ? 'current-page' : '' }}">
            <i class="fa-solid fa-certificate fs-5 me-1"></i>
            <span>My Certificates</span>
        </a>
    @endif

    @php
        $commActive = request()->routeIs('communication.*', 'teacher.communication.*', 'student.communication.*', 'parent.communication.*');
    @endphp
    <div class="sidebar-section-title">Collaboration</div>

    @canany(['notice_view', 'notice_manage'])
        <a href="#submenu-comm" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $commActive ? 'active' : '' }}">
            <span>
                <i class="bi bi-person-circle fs-5"></i>
                <span>Communication</span>
            </span>
            <i class="fas fa-chevron-right menu-arrow"></i>
        </a>
        <div class="collapse {{ $commActive ? 'show' : '' }}" id="submenu-comm" data-bs-parent="#sidebar-accordion">
            <a href="{{ route($announcementsRoute) }}"
                class="ps-5 submenu {{ request()->routeIs('communication.*', 'teacher.communication.*', 'student.communication.*', 'parent.communication.*') ? 'current-page' : '' }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
        </div>
    @endcanany

</div>
