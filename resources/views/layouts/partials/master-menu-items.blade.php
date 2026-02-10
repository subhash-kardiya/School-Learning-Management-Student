@can('academic_year_manage')
    <a href="{{ route('academic.year.index') }}"
        class="ps-5 submenu {{ request()->routeIs('academic.year.*') ? 'current-page' : '' }}">
        <i class="fa-solid fa-calendar"></i> Academic Year
    </a>
@endcan

@can('class_manage')
    <a href="{{ route('classes.index') }}" class="ps-5 submenu {{ request()->routeIs('classes.*') ? 'current-page' : '' }}">
        <i class="fas fa-chalkboard"></i> Class
    </a>
@endcan

@can('section_manage')
    <a href="{{ route('section.index') }}" class="ps-5 submenu {{ request()->routeIs('section.*') ? 'current-page' : '' }}">
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
