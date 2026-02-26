@php
    $userName = 'Super Admin';
    $userEmail = 'admin@schoollms.com';
    $role = session('role');
    $roleLabel = $role ? ucfirst($role) : 'User';
    $profileRoute = route($role === 'teacher' ? 'teacher.profile.show' : 'profile.show');
    $quickLinks = [];

    if (isset($authUser)) {
        $userEmail = $authUser->email;
        if ($role == 'admin') {
            $userName = $authUser->admin_name ?? 'Super Admin';
        } elseif ($role == 'teacher') {
            $userName = $authUser->name;
        } elseif ($role == 'student') {
            $userName = $authUser->student_name;
        } elseif ($role == 'parent') {
            $userName = $authUser->parent_name;
        }
    }

    if ($role === 'admin' || $role === 'superadmin') {
        $quickLinks[] = [
            'label' => 'Settings',
            'icon' => 'fas fa-cog',
            'url' => route('settings.index'),
        ];
    }
    if ($role === 'teacher') {
        $quickLinks[] = [
            'label' => 'My Timetable',
            'icon' => 'fas fa-calendar-alt',
            'url' => route('teacher.timetable'),
        ];
    }
    if ($role === 'student') {
        $quickLinks[] = [
            'label' => 'My Timetable',
            'icon' => 'fas fa-calendar-alt',
            'url' => route('student.timetable'),
        ];
    }
    if ($role === 'parent') {
        $quickLinks[] = [
            'label' => 'Child Timetable',
            'icon' => 'fas fa-calendar-alt',
            'url' => route('parent.timetable'),
        ];
    }
    $hideGlobalContextFilters = request()->routeIs('homework.*', 'student.homework.list', 'parent.homework.list');
    $showGlobalContextFilters = trim($__env->yieldContent('show_global_context_filters')) === '1';
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $announcementCount = 0;
    $announcementItems = collect();
    $announcementRoute = null;

    if ($role === 'teacher') {
        $announcementRoute = route('teacher.communication.announcements');
    } elseif ($role === 'admin' || $role === 'superadmin') {
        $announcementRoute = route('communication.announcements');
    } elseif ($role === 'student' && Route::has('student.communication.announcements')) {
        $announcementRoute = route('student.communication.announcements');
    } elseif ($role === 'parent' && Route::has('parent.communication.announcements')) {
        $announcementRoute = route('parent.communication.announcements');
    }

    if (isset($authUser) && class_exists(\App\Models\Announcement::class)) {
        $baseQuery = \App\Models\Announcement::query()->activeWindow()->visibleTo((string) $role, $authUser);
        $lastSeen = session('announcements_last_seen_' . $role . '_' . (int) ($authUser->id ?? 0));

        $unreadQuery = (clone $baseQuery);
        if (in_array($role, ['admin', 'superadmin'], true)) {
            // Admin bell: notify when Teacher created announcements.
            $unreadQuery->where('created_by_role', 'teacher');
        } elseif ($role === 'teacher') {
            // Teacher bell: notify when Admin/Superadmin created announcements.
            $unreadQuery->whereIn('created_by_role', ['admin', 'superadmin']);
        } elseif (in_array($role, ['student', 'parent'], true)) {
            // Student/Parent bell: notify when Admin or Teacher created announcements.
            $unreadQuery->whereIn('created_by_role', ['admin', 'superadmin', 'teacher']);
        }

        if (!empty($lastSeen)) {
            $unreadQuery->where('created_at', '>', $lastSeen);
        }

        $announcementCount = (clone $unreadQuery)->count();
        $announcementItems = (clone $unreadQuery)->latest()->limit(5)->get();

        if ($announcementItems->isEmpty()) {
            $announcementItems = (clone $baseQuery)->latest()->limit(5)->get();
        }
    }
@endphp

<div class="main-content-header">
    <button class="btn toggle-button d-md-none mb-3" id="sidebarToggle" data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
        <i class="fas fa-bars"></i> Menu
    </button>

    <div class="modern-header-shell mb-">
        <div class="modern-header-left">
            <h2 class="modern-header-title "><span id="modernGreeting">{{ $greeting }}</span>,
                <span>{{ $userName }}</span>
            </h2>

        </div>

        <div class="modern-header-right">
            <div class="modern-date-pill bg-white text-dark">
                <i class="far fa-calendar-alt"></i>
                <span>{{ now()->format('d M Y') }}</span>
            </div>

            <button class="modern-icon-btn modern-search-toggle" type="button" aria-label="Open search"
                id="modernSearchToggle">
                <i class="fas fa-search"></i>
            </button>

            <div class="modern-search-wrap" id="modernSearchWrap">
                <input type="text" class="modern-search-input" id="modernSearchInput" placeholder="Search Here">
            </div>

            <div class="dropdown">
                <button class="modern-icon-btn modern-bell-btn" type="button" aria-label="Notifications"
                    id="announcementBellDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    @if ($announcementCount > 0)
                        <span class="modern-icon-dot"></span>
                        <span class="modern-bell-count">{{ $announcementCount > 9 ? '9+' : $announcementCount }}</span>
                    @endif
                </button>

                <div class="dropdown-menu dropdown-menu-end modern-notice-menu p-0"
                    aria-labelledby="announcementBellDropdown">
                    <div class="modern-notice-head">
                        <span>Latest Announcements</span>
                        <span class="modern-notice-badge">{{ $announcementCount }}</span>
                    </div>

                    <div class="modern-notice-list">
                        @forelse($announcementItems as $notice)
                            <a href="{{ $announcementRoute ?? 'javascript:void(0)' }}" class="modern-notice-item">
                                <div class="modern-notice-title">{{ $notice->title }}</div>
                                <div class="modern-notice-desc">
                                    {{ \Illuminate\Support\Str::limit($notice->description, 58) }}</div>
                            </a>
                        @empty
                            <div class="modern-notice-empty">No new announcements</div>
                        @endforelse
                    </div>

                    @if ($announcementRoute)
                        <a href="{{ $announcementRoute }}" class="modern-notice-footer">View all announcements</a>
                    @endif
                </div>
            </div>

            <div class="dropdown">
                <a href="javascript:void(0)"
                    class="d-flex align-items-center text-decoration-none dropdown-toggle no-arrow" id="profileDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=0056b3&color=fff"
                        class="modern-profile-avatar" alt="Profile image">
                </a>

                <ul class="dropdown-menu dropdown-menu-end profile-box shadow-sm m-3" aria-labelledby="profileDropdown">
                    <li class="profile-top-block text-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=eef2ff&color=1d4ed8"
                            class="profile-top-avatar" alt="Profile avatar">
                        <h6 class="profile-top-name">{{ $userName }}</h6>
                        <p class="profile-top-email">{{ $userEmail }}</p>
                        <span class="profile-role-chip">{{ $roleLabel }}</span>
                        <a href="{{ $profileRoute }}" class="btn profile-manage-btn">Manage your profile</a>
                    </li>

                    <li>
                        <hr class="profile-divider">
                    </li>

                    @foreach ($quickLinks as $link)
                        <li>
                            <a class="dropdown-item profile-action-link" href="{{ $link['url'] }}">
                                <i class="{{ $link['icon'] }}"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
                            @csrf
                        </form>

                        <a class="dropdown-item profile-action-link profile-action-danger" href="javascript:void(0)"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @if (session()->has('auth_id') && !$hideGlobalContextFilters && $showGlobalContextFilters)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="POST" action="{{ route('context.filters.set') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Academic Year</label>
                        <select name="academic_year_id" id="globalAcademicYearSelect"
                            class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach ($globalAcademicYears ?? collect() as $year)
                                <option value="{{ $year->id }}"
                                    {{ (string) ($selectedAcademicYearId ?? '') === (string) $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Class</label>
                        <select name="class_id" id="globalClassSelect" class="form-select form-select-sm">
                            <option value="">All Classes</option>
                            @foreach ($globalClasses ?? collect() as $class)
                                <option value="{{ $class->id }}"
                                    {{ (string) ($selectedClassId ?? '') === (string) $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Section</label>
                        <select name="section_id" id="globalSectionSelect" class="form-select form-select-sm">
                            <option value="">All Sections</option>
                            @foreach ($globalSections ?? collect() as $section)
                                <option value="{{ $section->id }}"
                                    {{ (string) ($selectedSectionId ?? '') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-sm btn-primary w-100">Apply</button>
                        <button formaction="{{ route('context.filters.clear') }}"
                            class="btn btn-sm btn-outline-secondary w-100">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const greetingEl = document.getElementById('modernGreeting');
            if (greetingEl) {
                const hour = new Date().getHours();
                const text = hour < 12 ? 'Good Morning' : (hour < 17 ? 'Good Afternoon' : 'Good Evening');
                greetingEl.textContent = text;
            }

            const searchToggle = document.getElementById('modernSearchToggle');
            const searchWrap = document.getElementById('modernSearchWrap');
            const searchInput = document.getElementById('modernSearchInput');
            if (searchToggle && searchWrap) {
                searchToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    searchWrap.classList.toggle('is-open');
                    if (searchWrap.classList.contains('is-open') && searchInput) {
                        setTimeout(() => searchInput.focus(), 120);
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!searchWrap.classList.contains('is-open')) return;
                    if (searchWrap.contains(e.target) || searchToggle.contains(e.target)) return;
                    searchWrap.classList.remove('is-open');
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        searchWrap.classList.remove('is-open');
                    }
                });
            }

            const classSelect = document.getElementById('globalClassSelect');
            const sectionSelect = document.getElementById('globalSectionSelect');
            if (!classSelect || !sectionSelect) return;

            const sectionUrlTemplate = @json(route('sections.by.class', ['classId' => '__CLASS__']));

            const resetSections = function() {
                sectionSelect.innerHTML = '<option value="">All Sections</option>';
            };

            const loadSections = async function(classId) {
                if (!classId) {
                    resetSections();
                    return;
                }

                try {
                    const response = await fetch(sectionUrlTemplate.replace('__CLASS__', classId), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        resetSections();
                        return;
                    }

                    const sections = await response.json();
                    const options = ['<option value="">All Sections</option>'];

                    sections.forEach(function(section) {
                        options.push('<option value="' + section.id + '">' + section.name +
                            '</option>');
                    });

                    sectionSelect.innerHTML = options.join('');
                } catch (e) {
                    resetSections();
                }
            };

            classSelect.addEventListener('change', function() {
                loadSections(this.value);
            });
        });
    </script>
@endpush
