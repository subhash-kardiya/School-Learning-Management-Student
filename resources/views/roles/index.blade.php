@extends('layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
<div class="container-fluid py-4 role-ui role-permission-compact">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <p class="text-muted mb-0">Manage access control & security policies</p>
        </div>
        @can('role_add')
            <a href="{{ route('roles.create') }}" class="btn btn-primary-fancy">
                <i class="fas fa-plus me-1"></i> New Role
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="glass-card">
        <h6 class="section-title mb-3">Existing Roles</h6>

        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0" id="roles-table">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th class="text-end" width="120">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/resize/role-permission-compact.css') }}">
@endpush

@push('scripts')
<script>
    $(function() {
        $('#roles-table').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"d-flex justify-content-between align-items-center pb-3 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-4"i p>',
            ajax: "{{ route('roles.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'permissions',
                    name: 'permissions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (Array.isArray(data) && data.length > 0) {
                            let limit = 4;
                            let displayed = data.slice(0, limit).map(p => {
                                return `<span class="permission-pill">${p.name.replace(/_/g, ' ')}</span>`;
                            }).join('');

                            if (data.length > limit) {
                                displayed += `<span class="badge bg-secondary rounded-pill ms-1">+${data.length - limit} more</span>`;
                            }
                            return displayed;
                        }
                        return '<span class="text-muted small">No permissions assigned</span>';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end'
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search roles...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="d-flex justify-content-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                paginate: {
                    next: '<i class="fas fa-chevron-right"></i>',
                    previous: '<i class="fas fa-chevron-left"></i>'
                }
            },
            initComplete: function() {
                $('.dataTables_length select').removeClass('form-select-sm');
                $('.dataTables_filter input').removeClass().addClass('form-control form-control-sm');
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('mb-0');
            }
        });
    });
</script>
@endpush
