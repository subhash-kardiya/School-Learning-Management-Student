@php
    $isTeacherViewingAdminAnnouncement = $role === 'teacher'
        && strtolower((string) ($announcement->created_by_role ?? '')) !== 'teacher';

    $canManageRow = !$isTeacherViewingAdminAnnouncement
        && ($canManageAll || ((int) $announcement->created_by === (int) ($authUser->id ?? 0)));
@endphp
<article class="announcement-card announcement-row"
    data-status="{{ strtolower($announcement->status) }}"
    data-target="{{ ($announcement->target_role ?? $announcement->role_type) === 'all' ? 'all-users' : strtolower($announcement->target_role ?? $announcement->role_type) }}"
    data-search="{{ strtolower($announcement->title . ' ' . $announcement->description . ' ' . ($announcement->classRoom?->name ?? '')) }}">
    <div class="announcement-head">
        <div class="announcement-title-wrap">
            <span class="announcement-icon"><i class="bi bi-megaphone"></i></span>
            <div>
                <h6 class="announcement-title">{{ $announcement->title }}</h6>
                <div class="announcement-meta">
                    <span class="announcement-pill">For: {{ $announcement->target_audience_label }}</span>
                    <span class="announcement-pill">{{ $announcement->classRoom?->name ?? 'All Classes' }}</span>
                    <span class="announcement-pill">By: {{ $announcement->creator_name }} ({{ $announcement->creator_role_label }})</span>
                    <span class="badge announce-status-badge {{ $announcement->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                        {{ ucfirst($announcement->status) }}
                    </span>
                </div>
            </div>
        </div>
        <small class="announcement-date">
            {{ optional($announcement->start_date)->format('d M Y') }} - {{ optional($announcement->end_date)->format('d M Y') }}
        </small>
    </div>

    <p class="announcement-description">{{ \Illuminate\Support\Str::limit($announcement->description, 180) }}</p>

    @if ($canCreate)
        <div class="announcement-actions">
            @if ($canManageRow)
                <a class="btn btn-sm btn-outline-primary"
                    href="{{ $role === 'teacher' ? route('teacher.communication.announcements.edit', $announcement) : route('communication.announcements.edit', $announcement) }}">
                    Edit
                </a>
                <form method="POST"
                    action="{{ $role === 'teacher' ? route('teacher.communication.announcements.destroy', $announcement) : route('communication.announcements.destroy', $announcement) }}"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Delete this announcement?')">Delete</button>
                </form>
            @else
                <span class="text-muted small">Read only</span>
            @endif
        </div>
    @endif
</article>
