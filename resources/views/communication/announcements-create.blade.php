@extends('layouts.admin')

@section('title', 'Create Announcement')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/communication-compact.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-3">
        <div class="announce-create-page">
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

            <div class="announce-create-shell">
                <div class="card-header bg-transparent border-0 pt-3 px-3 px-lg-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Create Announcement</h5>
                    <a href="{{ $role === 'teacher' ? route('teacher.communication.announcements') : route('communication.announcements') }}"
                        class="btn btn-outline-secondary btn-sm">
                        Back to List
                    </a>
                </div>
                <div class="card-body px-3 px-lg-4 pb-4">
                    <form method="POST"
                        class="announce-form"
                        action="{{ $role === 'teacher' ? route('teacher.communication.announcements.store') : route('communication.announcements.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control"
                                value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target Audience</label>
                            <select name="target_role" class="form-select" required>
                                @if ($role === 'teacher')
                                    <option value="student" {{ old('target_role') === 'student' ? 'selected' : '' }}>Students (Class-wise)</option>
                                    <option value="parent" {{ old('target_role') === 'parent' ? 'selected' : '' }}>Parents (Class-wise)</option>
                                @else
                                    <option value="all" {{ old('target_role') === 'all' ? 'selected' : '' }}>All Users</option>
                                    <option value="teacher" {{ old('target_role') === 'teacher' ? 'selected' : '' }}>Only Teachers</option>
                                    <option value="student" {{ old('target_role') === 'student' ? 'selected' : '' }}>Only Students</option>
                                    <option value="parent" {{ old('target_role') === 'parent' ? 'selected' : '' }}>Only Parents</option>
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Class (Optional)</label>
                            <select name="class_id" class="form-select">
                                <option value="">All Classes</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ old('start_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ old('end_date', now()->toDateString()) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <button class="btn btn-primary w-100">Submit Announcement</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
