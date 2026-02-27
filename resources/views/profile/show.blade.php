@extends('layouts.admin')

@section('title', 'My Profile')

@php
    $name = 'User';
    $email = $user?->email ?? 'N/A';
    $roleLabel = $role ? ucfirst($role) : 'User';
    $mobile = $user?->mobile_no ?? $user?->mobile ?? '';
    $username = $user?->username ?? 'N/A';
    $status = $user?->status ?? 1;
    $address = $user?->address ?? '';
    $dob = $user?->date_of_birth ?? '';
    $profileImage = null;

    if ($role === 'admin' || $role === 'superadmin') {
        $name = $user?->admin_name ?? 'Admin';
        $dob = $user?->dob ?? $dob;
        $profileImage = $user?->profile_image ? asset('uploads/admins/' . $user->profile_image) : null;
    } elseif ($role === 'teacher') {
        $name = $user?->name ?? 'Teacher';
        $profileImage = $user?->profile_image ? asset('uploads/teachers/' . $user->profile_image) : null;
    } elseif ($role === 'student') {
        $name = $user?->student_name ?? 'Student';
        $profileImage = $user?->profile_image ? asset('uploads/students/' . $user->profile_image) : null;
    } elseif ($role === 'parent') {
        $name = $user?->parent_name ?? 'Parent';
        $profileImage = $user?->profile_image ? asset('uploads/parents/' . $user->profile_image) : null;
    }

    $avatar = $profileImage ?: ('https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0f766e&color=fff&size=240');
@endphp

@section('content')
    <div class="container-fluid py-4">
        <form action="{{ route($role === 'teacher' ? 'teacher.profile.update' : 'profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form" class="profile-form">
            @csrf
            @method('PUT')

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
            @endif

            <div class="profile-top-card">
                <div class="profile-top-card__avatar">
                    <img src="{{ $avatar }}" alt="Profile">
                    <label class="avatar-edit">
                        <input type="file" name="profile_image" class="d-none">
                        <i class="fas fa-pen"></i>
                    </label>
                </div>
                <div class="profile-top-card__info">
                    <h3>{{ $name }}</h3>
                    <div class="profile-badges">
                        <span class="badge-role">{{ $roleLabel }}</span>
                        <span class="badge-status {{ $status ? 'active' : 'inactive' }}">{{ $status ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="profile-sub">{{ $email }}</div>
                    <div class="profile-chips">
                        <span class="chip"><i class="fas fa-id-badge"></i>{{ $username }}</span>
                        <span class="chip"><i class="fas fa-phone"></i>{{ $mobile ?: 'N/A' }}</span>
                    </div>
                </div>
                <div class="profile-top-card__actions">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="profile-edit">
                        Edit Profile
                    </button>
                    <button type="submit" class="btn btn-primary-fancy rounded-pill px-4 d-none" id="profile-save">
                        Save Changes
                    </button>
                </div>
            </div>

            <div class="profile-kpis">
                <div class="kpi-card kpi-indigo">
                    <div class="kpi-icon"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <div class="kpi-label">Role</div>
                        <div class="kpi-value">{{ $roleLabel }}</div>
                    </div>
                </div>
                <div class="kpi-card kpi-emerald">
                    <div class="kpi-icon"><i class="fas fa-signal"></i></div>
                    <div>
                        <div class="kpi-label">Status</div>
                        <div class="kpi-value">{{ $status ? 'Active' : 'Inactive' }}</div>
                    </div>
                </div>
                <div class="kpi-card kpi-amber">
                    <div class="kpi-icon"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <div class="kpi-label">Security</div>
                        <div class="kpi-value">Standard</div>
                    </div>
                </div>
            </div>

            <div class="profile-sections">
                <div class="profile-section profile-section--form">
                    <div class="section-title">
                        <h5>Personal Information</h5>
                        <p>Edit your personal details</p>
                    </div>
                    <div class="section-body">
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control profile-input" value="{{ $name }}" disabled>
                        </div>
                        <div class="field">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile_no" class="form-control profile-input" value="{{ $mobile }}" disabled>
                        </div>
                        <div class="field">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control profile-input" value="{{ $address }}" disabled>
                        </div>
                        <div class="field">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control profile-input" value="{{ $dob }}" disabled>
                        </div>
                    </div>
                    <div class="section-footer">
                        <div class="profile-progress">
                            <div class="profile-progress__bar" style="width: {{ $mobile && $address && $dob ? '100%' : '70%' }}"></div>
                        </div>
                        <small class="text-muted">Profile completion</small>
                    </div>
                </div>

                <div class="profile-section profile-section--account">
                    <div class="section-title">
                        <h5>Account Information</h5>
                        <p>Read-only security details</p>
                    </div>
                    <div class="section-body readonly">
                        <div class="field">
                            <label>Email</label>
                            <input type="text" class="form-control" value="{{ $email }}" disabled>
                        </div>
                        <div class="field">
                            <label>Username</label>
                            <input type="text" class="form-control" value="{{ $username }}" disabled>
                        </div>
                        <div class="field">
                            <label>Role</label>
                            <input type="text" class="form-control" value="{{ $roleLabel }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="profile-section profile-section--security">
                    <div class="section-title">
                        <h5>Security</h5>
                        <p>Protect your account</p>
                    </div>
                    <div class="section-body">
                        <div class="security-row">
                            <div>
                                <strong>Change Password</strong>
                                <div class="hint">Last updated: {{ now()->subDays(12)->toDateString() }}</div>
                            </div>
                            <a href="{{ route('change.password') }}" class="btn btn-outline-primary rounded-pill">Update</a>
                        </div>
                        <div class="security-row">
                            <div>
                                <strong>Logout from all devices</strong>
                                <div class="hint">Force logout on other sessions</div>
                            </div>
                            <button type="button" class="btn btn-outline-danger rounded-pill">Logout</button>
                        </div>
                        <div class="security-row">
                            <div>
                                <strong>Two-factor authentication</strong>
                                <div class="hint">Add extra verification layer</div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-section profile-section--prefs">
                    <div class="section-title">
                        <h5>Preferences</h5>
                        <p>Personalize your workspace</p>
                    </div>
                    <div class="section-body">
                        <div class="field">
                            <label>Language</label>
                            <select class="form-select" disabled>
                                <option>English</option>
                                <option>Gujarati</option>
                                <option>Hindi</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Theme</label>
                            <select class="form-select" disabled>
                                <option>Light</option>
                                <option>Dark</option>
                            </select>
                        </div>
                        <div class="field toggle">
                            <label>Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked disabled>
                            </div>
                        </div>
                        <div class="field toggle">
                            <label>Email Alerts</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('css')
    <style>
        .profile-top-card {
            background: linear-gradient(135deg, #173c93ff, #27645fff);
            color: #fff;
            border-radius: 20px;
            padding: 24px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 20px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .profile-top-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.08), transparent 40%),
                        radial-gradient(circle at 80% 10%, rgba(255,255,255,0.12), transparent 40%);
            pointer-events: none;
        }
        .profile-top-card::after {
            content: "";
            position: absolute;
            right: -40px;
            top: -40px;
            width: 180px;
            height: 180px;
            pointer-events: none;
        }
        .profile-top-card__avatar {
            position: relative;
            width: 110px;
            height: 110px;
        }
        .profile-top-card__avatar img {
            width: 110px;
            height: 110px;
            border-radius: 999px;
            border: 3px solid rgba(255, 255, 255, 0.5);
            object-fit: cover;
            box-shadow: 0 12px 24px rgba(0,0,0,0.25);
        }
        .avatar-edit {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: #fff;
            color: #0f766e;
            border-radius: 999px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .avatar-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.2);
        }
        .profile-top-card__info h3 {
            margin: 0 0 6px 0;
            font-weight: 700;
        }
        .profile-chips {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.18);
            font-size: 0.8rem;
        }
        .profile-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 6px;
        }
        .badge-role {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .badge-status {
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-status.active {
            background: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
        }
        .badge-status.inactive {
            background: rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
        }
        .profile-sub {
            opacity: .9;
        }
        .profile-top-card__actions {
            display: flex;
            gap: 10px;
        }
        .profile-kpis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .kpi-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
        }
        .kpi-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
        }
        .kpi-value {
            font-weight: 700;
            color: #0f172a;
        }
        .kpi-indigo .kpi-icon { background: linear-gradient(135deg, #4f46e5, #6366f1); }
        .kpi-emerald .kpi-icon { background: linear-gradient(135deg, #059669, #34d399); }
        .kpi-amber .kpi-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .profile-sections {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
        .profile-section {
            background: #fff;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .profile-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(15,23,42,0.12);
        }
        .section-title h5 {
            margin: 0;
            font-weight: 700;
        }
        .section-title p {
            margin: 4px 0 12px;
            color: #64748b;
            font-size: 0.85rem;
        }
        .section-body {
            display: grid;
            gap: 12px;
        }
        .profile-section--form .section-body,
        .profile-section--account .section-body,
        .profile-section--prefs .section-body {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .profile-section--security .section-body {
            grid-template-columns: 1fr;
        }
        .section-footer {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .profile-progress {
            flex: 1;
            height: 8px;
            background: #eef2ff;
            border-radius: 999px;
            overflow: hidden;
        }
        .profile-progress__bar {
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #22c55e);
        }
        .field label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-bottom: 4px;
        }
        .field .form-control,
        .field .form-select {
            height: 44px;
        }
        .field.toggle {
            grid-column: 1 / -1;
        }
        .profile-input[disabled] {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #94a3b8;
        }
        .profile-form.is-editing .profile-input[disabled] {
            background: #fff;
            color: #0f172a;
        }
        .profile-form.is-editing .profile-input {
            border-color: #cbd5f5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
        }
        .readonly input {
            background: #f8fafc;
            border-color: #e2e8f0;
        }
        .security-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .security-row .hint {
            font-size: 0.8rem;
            color: #64748b;
        }
        .toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media (max-width: 992px) {
            .profile-top-card {
                grid-template-columns: 1fr;
                text-align: left;
            }
            .profile-top-card__actions {
                justify-content: flex-start;
            }
            .profile-kpis {
                grid-template-columns: 1fr;
            }
            .profile-sections {
                grid-template-columns: 1fr;
            }
            .profile-section--form .section-body,
            .profile-section--account .section-body,
            .profile-section--prefs .section-body {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const editBtn = document.getElementById('profile-edit');
            const saveBtn = document.getElementById('profile-save');
            const inputs = document.querySelectorAll('.profile-input');
            const form = document.getElementById('profile-form');
            if (!editBtn || !saveBtn || inputs.length === 0) return;

            editBtn.addEventListener('click', () => {
                inputs.forEach(input => input.removeAttribute('disabled'));
                saveBtn.classList.remove('d-none');
                editBtn.classList.add('d-none');
                form?.classList.add('is-editing');
            });
        })();
    </script>
@endpush
