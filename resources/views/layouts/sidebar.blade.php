<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-graduation-cap me-2"></i>
        <span>School LMS</span>
    </div>

    @php
        $user = auth()->user();
        $isAdmin = $user && ($user->hasRole('admin') || $user->hasRole('Admin'));
        $isSuperAdmin = $user && (
            $user->hasRole('superadmin')
            || $user->hasRole('super admin')
            || $user->hasRole('super_admin')
            || $user->hasRole('Super Admin')
            || $user->hasRole('SuperAdmin')
        );
        $isStudent = $user && ($user->hasRole('student') || $user->hasRole('Student'));
        $isTeacher = $user && ($user->hasRole('teacher') || $user->hasRole('Teacher'));
        $isParent = $user && ($user->hasRole('parent') || $user->hasRole('Parent'));
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
            'parents.*',
            'teacher.mapping*',
            'certificate.*',
        ];
        $masterPermissions = [
            'academic_year_manage',
            'class_manage',
            'section_manage',
            'subject_manage',
            'role_view',
            'role_add',
            'role_edit',
            'role_delete',
            'teacher_view',
            'student_view',
            'parent_manage',
            'certificate_manage',
        ];
    @endphp

    <!-- 1️⃣ Dashboard -->
    <a href="{{ route($dashboardRoute) }}"
        class="d-flex align-items-center {{ request()->routeIs($dashboardRoute) ? 'current-page' : '' }}">
        <i class="fas fa-th-large fs-5 me-1"></i>
        <span>Dashboard</span>
    </a>

    <!-- 2️⃣ Master (Collapsible) -->
    @if ($isAdmin || $isSuperAdmin)
        @php
            $masterActive = request()->routeIs($masterRoutes);
        @endphp
        @canany($masterPermissions)
            <a href="#submenu-master" data-bs-toggle="collapse"
                class="d-flex justify-content-between align-items-center {{ $masterActive ? 'active' : '' }}">
                <span>
                    <i class="fas fa-layer-group fs-5"></i>
                    <span>Master</span>
                </span>
                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
            </a>

            <div class="collapse {{ $masterActive ? 'show' : '' }}" id="submenu-master">
                @include('layouts.partials.master-menu-items')
            </div>
        @endcanany
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
                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
            </a>
            <div class="collapse {{ $studentMenuActive ? 'show' : '' }}" id="submenu-student">
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
                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
            </a>
            <div class="collapse {{ $teacherMenuActive ? 'show' : '' }}" id="submenu-teacher">
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
                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
            </a>
            <div class="collapse {{ $parentMenuActive ? 'show' : '' }}" id="submenu-parent">
                @include('layouts.partials.master-menu-items')
            </div>
        @endcanany
    @endif

    <!-- 3️⃣ Timetable -->
    @php
        $timetableActive = request()->routeIs('timetable.*', 'student.timetable', 'parent.timetable');
    @endphp
    @if ($isStudent)
        @can('timetable_student_view')
            <a href="{{ route('student.timetable') }}"
                class="d-flex align-items-center {{ request()->routeIs('student.timetable') ? 'current-page' : '' }}">
                <i class="fas fa-calendar-alt fs-5 me-1"></i>
                <span>My Timetable</span>
            </a>
        @endcan
    @elseif ($isParent)
        @can('timetable_student_view')
            <a href="{{ route('parent.timetable') }}"
                class="d-flex align-items-center {{ request()->routeIs('parent.timetable') ? 'current-page' : '' }}">
                <i class="fas fa-calendar-alt fs-5 me-1"></i>
                <span>Child Timetable</span>
            </a>
        @endcan
    @else
        @canany(['timetable_view', 'timetable_teacher_view', 'timetable_create', 'timetable_edit', 'timetable_delete'])
            <a href="#submenu-timetable" data-bs-toggle="collapse"
                class="d-flex justify-content-between align-items-center {{ $timetableActive ? 'active' : '' }}">
                <span>
                    <i class="fas fa-calendar-alt fs-5"></i>
                    <span>Timetable</span>
                </span>
                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
            </a>
            <div class="collapse {{ $timetableActive ? 'show' : '' }}" id="submenu-timetable">
                @canany(['timetable_view', 'timetable_create', 'timetable_edit', 'timetable_delete'])
                    <a href="{{ route('timetable.class') }}"
                        class="ps-5 submenu {{ request()->routeIs('timetable.class*') ? 'current-page' : '' }}">
                        <i class="fa-regular fa-clock"></i> Class Time Table
                    </a>
                @endcanany
                @can('timetable_teacher_view')
                    <a href="{{ route('timetable.teacher') }}"
                        class="ps-5 submenu {{ request()->routeIs('timetable.teacher*') ? 'current-page' : '' }}">
                        <i class="fa-regular fa-clock"></i> Teacher Time Table
                    </a>
                @endcan
            </div>
        @endcanany
    @endif

    <!-- 3️⃣ Attendance -->
    @php
        $attendanceActive = request()->routeIs('attendance.*', 'student.attendance', 'parent.attendance');
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
                <i class="fa-solid fa-clipboard-check fs-5"></i>
                <span>Attendance</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
        </a>
        <div class="collapse {{ $attendanceActive ? 'show' : '' }}" id="submenu-attendance">
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
                <a href="{{ route('attendance.mark') }}"
                    class="ps-5 submenu {{ request()->routeIs('attendance.mark*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-check-circle"></i> Mark Attendance
                </a>
            @endcan
            @if (!$isStudent && !$isParent && !$isTeacher)
                @can('attendance_view')
                <a href="{{ route('attendance.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('attendance.index') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-list"></i> Attendance Report
                </a>
                @endcan
            @endif
        </div>
    @endif

    <!-- 4️⃣ Homework -->
    @php
        $homeworkActive = request()->routeIs('homework.*', 'student.homework.*');
        $canSeeHomework =
            $isStudent ||
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
                <i class="fas fa-book fs-5"></i>
                <span>Homework</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
        </a>
        <div class="collapse {{ $homeworkActive ? 'show' : '' }}" id="submenu-homework">
            @if ($isStudent)
                <a href="{{ route('student.homework.list') }}"
                    class="ps-5 submenu {{ request()->routeIs('student.homework.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-list"></i> Homework List
                </a>
            @endif
            @can('homework_create')
                <a href="{{ route('homework.create') }}"
                    class="ps-5 submenu {{ request()->routeIs('homework.create*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-plus-circle"></i> Create Homework
                </a>
            @endcan
            @if (!$isStudent)
                <a href="{{ route('homework.list') }}"
                    class="ps-5 submenu {{ request()->routeIs('homework.list*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-list"></i> Homework List
                </a>
            @endif
            @can('homework_submission')
                <a href="{{ route('homework.submission') }}"
                    class="ps-5 submenu {{ request()->routeIs('homework.submission*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-file-circle-check"></i> Submission
                </a>
            @endcan
        </div>
    @endif

    <!-- 5️⃣ Examination -->
    @php
        $examsActive = request()->routeIs('exams.*');
    @endphp
    @canany(['exam_type', 'exam_schedule', 'marks_entry'])
        <a href="#submenu-exams" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $examsActive ? 'active' : '' }}">
            <span>
                <i class="fa-solid fa-pen fs-5"></i>
                <span>Examination</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
        </a>
        <div class="collapse {{ $examsActive ? 'show' : '' }}" id="submenu-exams">
            @canany(['exam_type'])
                <a href="{{route('exams.createexam')}}"
                    class="ps-5 submenu {{ request()->routeIs('exams.create*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-plus-circle"></i> Create Exam
                </a>
            @endcanany
            @can('exam_schedule')
                <a href="{{ route('exams.schedule') }}"
                    class="ps-5 submenu {{ request()->routeIs('exams.schedule*') ? 'current-page' : '' }}">
                    <i class="fas fa-calendar-check"></i> Exam Schedule
                </a>
            @endcan
            @can('marks_entry')
                <a href="{{ route('exams.marks') }}"
                    class="ps-5 submenu {{ request()->routeIs('exams.marks*') ? 'current-page' : '' }}">
                    <i class="fas fa-marker"></i> Marks Entry
                </a>
            @endcan
        </div>
    @endcanany

    <!-- 6️⃣ Results -->
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

    <!-- 6️⃣ Certificates -->
    @if ($isStudent && $user && method_exists($user, 'hasPermission') && $user->hasPermission('certificate_view'))
        <a href="{{ route('student.certificate.index') }}"
            class="d-flex align-items-center {{ request()->routeIs('student.certificate.*') ? 'current-page' : '' }}">
            <i class="fa-solid fa-certificate fs-5 me-1"></i>
            <span>My Certificates</span>
        </a>
    @endif

    <!-- 7️⃣ Communication -->
    @php
        $commActive = request()->routeIs('communication.*');
    @endphp
    @canany(['notice_view', 'notice_manage'])
        <a href="#submenu-comm" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $commActive ? 'active' : '' }}">
            <span>
                <i class="fa-sharp fa-solid fa-comment fs-5"></i>
                <span>Communication</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
        </a>
        <div class="collapse {{ $commActive ? 'show' : '' }}" id="submenu-comm">
            <a href="{{ route('communication.announcements') }}"
                class="ps-5 submenu {{ request()->routeIs('communication.*') ? 'current-page' : '' }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
        </div>
    @endcanany

</div>
