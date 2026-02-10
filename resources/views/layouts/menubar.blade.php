{{-- <div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-graduation-cap me-2"></i>
        <span>School LMS</span>
    </div>

    @php
        $user = auth()->user();
        $dashboardRoute = 'admin.dashboard';
        if ($user && $user->hasRole('teacher')) {
            $dashboardRoute = 'teacher.dashboard';
        } elseif ($user && $user->hasRole('student')) {
            $dashboardRoute = 'student.dashboard';
        } elseif ($user && $user->hasRole('parent')) {
            $dashboardRoute = 'parent.dashboard';
        }
    @endphp

    <!-- 1️⃣ Dashboard -->
    <a href="{{ route($dashboardRoute) }}"
        class="d-flex align-items-center {{ request()->routeIs($dashboardRoute) ? 'current-page' : '' }}">
        <i class="fas fa-th-large fs-5 me-1"></i>
        <span>Dashboard</span>
    </a>

    <!-- 2️⃣ Master (Collapsible) -->
    @php
        $masterActive = request()->routeIs(
            'academic.year.*',
            'classes.*',
            'section.*',
            'subjects.*',
            'roles.*',
            'teachers.*',
            'students.*',
            'parents.*',
            'teacher.mapping*',
        );
    @endphp
    @canany(['academic_year_manage', 'class_manage', 'section_manage', 'subject_manage', 'role_view', 'role_add', 'role_edit', 'role_delete', 'teacher_view', 'student_view', 'parent_manage'])
        <a href="#submenu-master" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $masterActive ? 'active' : '' }}">
            <span>
                <i class="fas fa-layer-group fs-5"></i>
                <span>Master</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
        </a>

        <div class="collapse {{ $masterActive ? 'show' : '' }}" id="submenu-master">
            @can('academic_year_manage')
                <a href="{{ route('academic.year.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('academic.year.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-calendar"></i> Academic Year
                </a>
            @endcan

            @can('class_manage')
                <a href="{{ route('classes.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('classes.*') ? 'current-page' : '' }}">
                    <i class="fas fa-chalkboard"></i> Class
                </a>
            @endcan

            @can('section_manage')
                <a href="{{ route('section.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('section.*') ? 'current-page' : '' }}">
                    <i class="fas fa-th-large"></i> Section
                </a>
            @endcan

            @can('subject_manage')
                <a href="{{ route('subjects.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('subjects.*') ? 'current-page' : '' }}">
                    <i class="fas fa-book-open"></i> Subjects
                </a>
            @endcan

            @canany(['role_view', 'role_add', 'role_edit', 'role_delete'])
                <a href="{{ route('roles.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('roles.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-user-shield"></i> Role and Permission
                </a>
            @endcanany

            @can('teacher_view')
                <a href="{{ route('teachers.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('teachers.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-chalkboard-user"></i> Teachers
                </a>
            @endcan

            @can('student_view')
                <a href="{{ route('students.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('students.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-user-graduate"></i> Students
                </a>
            @endcan

            @can('parent_manage')
                <a href="{{ route('parents.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('parents.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-users"></i> Parents
                </a>
            @endcan

            @can('class_manage')
                <a href="{{ route('teacher.mapping') }}"
                    class="ps-5 submenu {{ request()->routeIs('teacher.mapping*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-link"></i> Class mapping
                </a>
            @endcan

            @can('certificate_manage')
                <a href="{{ route('certificate.index') }}"
                    class="ps-5 submenu {{ request()->routeIs('certificate.*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-certificate"></i> Certificate
                </a>
            @endcan
        </div>
    @endcanany

    <!-- 3️⃣ Timetable -->
    @php
        $timetableActive = request()->routeIs('timetable.*');
    @endphp
    @canany(['timetable_class', 'timetable_teacher'])
        <a href="#submenu-timetable" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $timetableActive ? 'active' : '' }}">
            <span>
                <i class="fas fa-calendar-alt fs-5"></i>
                <span>Timetable</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
        </a>
        <div class="collapse {{ $timetableActive ? 'show' : '' }}" id="submenu-timetable">
            @can('timetable_class')
                <a href="{{ route('timetable.class') }}"
                    class="ps-5 submenu {{ request()->routeIs('timetable.class*') ? 'current-page' : '' }}">
                    <i class="fa-regular fa-clock"></i> Class Time Table
                </a>
            @endcan
            @can('timetable_teacher')
                <a href="{{ route('timetable.teacher') }}"
                    class="ps-5 submenu {{ request()->routeIs('timetable.teacher*') ? 'current-page' : '' }}">
                    <i class="fa-regular fa-clock"></i> Teacher Time Table
                </a>
            @endcan
        </div>
    @endcanany

    <!-- 4️⃣ Homework -->
    @php
        $homeworkActive = request()->routeIs('homework.*');
    @endphp
    @canany(['homework_create', 'homework_list', 'homework_submission'])
        <a href="#submenu-homework" data-bs-toggle="collapse"
            class="d-flex justify-content-between align-items-center {{ $homeworkActive ? 'active' : '' }}">
            <span>
                <i class="fas fa-book fs-5"></i>
                <span>Homework</span>
            </span>
            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
        </a>
        <div class="collapse {{ $homeworkActive ? 'show' : '' }}" id="submenu-homework">
            @can('homework_create')
                <a href="{{ route('homework.create') }}"
                    class="ps-5 submenu {{ request()->routeIs('homework.create*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-plus-circle"></i> Create Homework
                </a>
            @endcan
            <a href="{{ route('homework.list') }}"
                class="ps-5 submenu {{ request()->routeIs('homework.list*') ? 'current-page' : '' }}">
                <i class="fa-solid fa-list"></i> Homework List
            </a>
            @can('homework_submission')
                <a href="{{ route('homework.submission') }}"
                    class="ps-5 submenu {{ request()->routeIs('homework.submission*') ? 'current-page' : '' }}">
                    <i class="fa-solid fa-file-circle-check"></i> Submission
                </a>
            @endcan
        </div>
    @endcanany

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
            @can('exam_type')
                <a href="{{ route('exams.type') }}"
                    class="ps-5 submenu {{ request()->routeIs('exams.type*') ? 'current-page' : '' }}">
                    <i class="fas fa-tasks"></i> Exam Type
                </a>
            @endcan
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
        <a href="{{ route('results.index') }}"
            class="d-flex align-items-center {{ request()->routeIs('results.*') ? 'current-page' : '' }}">
            <i class="fa-solid fa-square-check fs-5 me-1"></i>
            <span>Results</span>
        </a>
    @endcan

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

</div> --}}
