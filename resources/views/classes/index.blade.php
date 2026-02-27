@extends('layouts.admin')

@section('title', 'Classes Management')

@section('content')
    <div class="container-fluid py-4 class-module-compact">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Manage class structures, academic years, and teacher assignments</p>
            </div>
            <a href="{{ route('classes.create') }}" class="btn btn-primary-fancy">
                <i class="fa fa-plus me-2"></i> Create New Class
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Existing Classes</h5>
            </div>
            <div class="card-body p-0">
                <select id="classNameFilter" class="form-select form-select-sm d-none">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->name }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="classes-table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Class Name</th>
                                <th>Academic Year</th>
                                <th>Class Teacher</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/class-compact.css') }}">
@endpush

@push('scripts')
    <script type="text/javascript">
        $(function() {
            const classNameFilter = $('#classNameFilter');

            var table = $('#classes-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-2"i p>',
                ajax: {
                    url: "{{ route('classes.index') }}",
                    data: function(d) {
                        d.class_name = classNameFilter.val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'academic_year',
                        name: 'academic_year'
                    },
                    {
                        data: 'teacher',
                        name: 'teacher'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    },
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search classes...",
                    lengthMenu: "_MENU_",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                }
            });

            $('.dataTables_length select').addClass('form-select-sm');
            $('#classes-table_filter').addClass('d-flex align-items-center gap-2');
            $('#classes-table_filter label').addClass('mb-0');
            classNameFilter.removeClass('d-none').css('min-width', '170px');
            $('#classes-table_filter').prepend(classNameFilter);
            $('#classes-table_filter').prepend(
                '<span class="text-muted small mb-0 ms-1">Filter Class</span>');

            classNameFilter.on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
