@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="container-fluid py-4 role-ui role-permission-compact">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('roles.index') }}" class="btn btn-link link-secondary text-decoration-none p-0 mb-1">
                <i class="fas fa-arrow-left me-1"></i> Back to Roles
            </a>
            <p class="text-muted mb-0">Create role and assign permissions</p>
        </div>
        <div>
            <button type="submit" form="createRoleForm" class="btn btn-primary-fancy px-4">
                <i class="fas fa-save me-1"></i> Save Role
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            Please fix the highlighted fields and try again.
        </div>
    @endif

    <form action="{{ route('roles.store') }}" method="POST" id="createRoleForm">
        @csrf

        <div class="glass-card mb-4">
            <h6 class="section-title mb-4">Role Details</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Role Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" placeholder="e.g. Administrator">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description</label>
                    <input type="text" name="description"
                        class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}"
                        placeholder="Brief role summary">
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="section-title m-0">Assign Permissions</h6>
                <div class="form-check form-check-inline">
                    <input class="form-check-input @error('permissions') is-invalid @enderror" type="checkbox" id="selectAllPermissions">
                    <label class="form-check-label" for="selectAllPermissions">Select All</label>
                </div>
            </div>

            @error('permissions')
                <div class="text-danger mb-2">{{ $message }}</div>
            @enderror

            <div class="accordion" id="permissionsAccordion">
                @php
                    $groupedPermissions = $permissions->groupBy(function ($item) {
                        if (is_string($item->name) && str_contains($item->name, '_')) {
                            return explode('_', $item->name)[0];
                        }
                        return 'other';
                    });
                @endphp

                @foreach ($groupedPermissions as $group => $perms)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $group }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $group }}" aria-expanded="false"
                            aria-controls="collapse-{{ $group }}">
                            {{ ucfirst($group) }} Management
                        </button>
                    </h2>
                    <div id="collapse-{{ $group }}" class="accordion-collapse collapse"
                        aria-labelledby="heading-{{ $group }}" data-bs-parent="#permissionsAccordion">
                        <div class="accordion-body">
                            <div class="row g-2">
                                @foreach ($perms as $permission)
                                <div class="col-md-3">
                                    <label class="perm-chip w-100 text-center">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            class="permission-checkbox" {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <span>{{ ucwords(str_replace('_', ' ', $permission->name)) }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/resize/role-permission-compact.css') }}">
@endpush

@push('scripts')
<script>
    $(function() {
        $('#selectAllPermissions').on('click', function() {
            $('.permission-checkbox').prop('checked', $(this).prop('checked'));
        });
    });
</script>
@endpush
