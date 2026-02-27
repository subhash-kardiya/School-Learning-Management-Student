@extends('layouts.admin')

@section('title', 'Announcements')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/communication-compact.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-3">
        <div class="announce-page">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="announce-shell p-3 p-lg-4 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h4 class="announce-title">Announcements & Notices</h4>
                        <p class="announce-sub">Create targeted announcements for teachers, students, parents and classes.
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($canCreate)
                            <a href="{{ $role === 'teacher' ? route('teacher.communication.announcements.create') : route('communication.announcements.create') }}"
                                class="btn btn-primary ">
                                <i class="bi bi-plus-circle"></i> Create Announcement
                            </a>
                        @endif

                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="announce-shell">
                        <div class="card-header bg-transparent border-0 pt-3 px-3 px-lg-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="mb-0">Announcements & Notices</h5>

                            </div>

                            @if (in_array($role, ['admin', 'superadmin', 'teacher'], true))
                                <div class="announce-tools">
                                    <div class="announce-search">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="announcementSearch"
                                            placeholder="Search title or description">
                                    </div>

                                    <select id="announcementTargetFilter" class="announce-filter-select">
                                        <option value="all">All Targets</option>
                                        <option value="all-users">All Users</option>
                                        <option value="teacher">Teachers</option>
                                        <option value="student">Students</option>
                                        <option value="parent">Parents</option>
                                    </select>

                                    <select id="announcementStatusFilter" class="announce-filter-select">
                                        <option value="all">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            @endif

                            <div class="announce-stat-strip">
                                <span class="announce-stat-pill" id="announceVisibleCount">Visible:
                                    {{ $announcements->count() }}</span>
                                <span class="announce-stat-pill">Page: {{ $announcements->currentPage() }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $allItems = collect($announcements->items());
                                $teacherAnnouncements = $allItems
                                    ->filter(function ($item) {
                                        return strtolower((string) ($item->created_by_role ?? '')) === 'teacher';
                                    })
                                    ->values();
                                $adminAnnouncements = $allItems
                                    ->reject(function ($item) {
                                        return strtolower((string) ($item->created_by_role ?? '')) === 'teacher';
                                    })
                                    ->values();
                            @endphp

                            <div id="announcementTableBody">
                                @if (in_array($role, ['admin', 'superadmin', 'teacher'], true))
                                    <div class="announce-two-part">
                                        <div class="announce-part">
                                            <h6 class="announce-group-title">Admin Announcements</h6>
                                            <div class="announce-board">
                                                @forelse($adminAnnouncements as $announcement)
                                                    @include('communication.partials.announcement-card', [
                                                        'announcement' => $announcement,
                                                    ])
                                                @empty
                                                    <div class="announce-default-empty text-center text-muted py-2">
                                                        No admin announcements.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="announce-part">
                                            <h6 class="announce-group-title">Teacher Announcements</h6>
                                            <div class="announce-board">
                                                @forelse($teacherAnnouncements as $announcement)
                                                    @include('communication.partials.announcement-card', [
                                                        'announcement' => $announcement,
                                                    ])
                                                @empty
                                                    <div class="announce-default-empty text-center text-muted py-2">
                                                        No teacher announcements.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    @if ($allItems->isEmpty())
                                        <div class="announce-default-empty text-center text-muted py-4">
                                            No announcements found.
                                        </div>
                                    @endif
                                @else
                                    <div class="announce-board">
                                        @forelse($announcements as $announcement)
                                            @include('communication.partials.announcement-card', [
                                                'announcement' => $announcement,
                                            ])
                                        @empty
                                            <div class="announce-default-empty text-center text-muted py-4">
                                                No announcements found.
                                            </div>
                                        @endforelse
                                    </div>
                                @endif

                                <div class="announce-empty-row text-center text-muted py-4" id="announcementFilterEmptyRow">
                                    No announcements match current filters.
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            {{ $announcements->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('announcementSearch');
            const targetFilter = document.getElementById('announcementTargetFilter');
            const statusFilter = document.getElementById('announcementStatusFilter');
            const rows = Array.from(document.querySelectorAll('.announcement-row'));
            const emptyFiltered = document.getElementById('announcementFilterEmptyRow');
            const visibleCount = document.getElementById('announceVisibleCount');
            const defaultEmpty = document.querySelector('.announce-default-empty');

            if (!searchInput || !targetFilter || !statusFilter || !emptyFiltered || rows.length === 0) {
                return;
            }

            const applyFilters = function() {
                const search = searchInput.value.trim().toLowerCase();
                const target = targetFilter.value;
                const status = statusFilter.value;
                let count = 0;

                rows.forEach(function(row) {
                    const rowSearch = row.dataset.search || '';
                    const rowTarget = row.dataset.target || '';
                    const rowStatus = row.dataset.status || '';

                    const matchSearch = !search || rowSearch.includes(search);
                    const matchTarget = target === 'all' || rowTarget === target;
                    const matchStatus = status === 'all' || rowStatus === status;
                    const show = matchSearch && matchTarget && matchStatus;

                    row.style.display = show ? '' : 'none';
                    if (show) count++;
                });

                emptyFiltered.style.display = count === 0 ? '' : 'none';
                if (visibleCount) {
                    visibleCount.textContent = 'Visible: ' + count;
                }
                if (defaultEmpty) {
                    defaultEmpty.style.display = 'none';
                }
            };

            searchInput.addEventListener('input', applyFilters);
            targetFilter.addEventListener('change', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
        });
    </script>
@endpush
