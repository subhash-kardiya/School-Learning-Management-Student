@extends('layouts.admin')

@section('title', 'Sections Management')

@section('content')
    <div class="container-fluid py-4 section-module-compact">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Manage class sections and seating capacity allocations</p>
            </div>
            <a href="{{ route('section.create') }}" class="btn btn-primary-fancy">
                <i class="fa fa-plus me-2"></i> Create New Section
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
                <h5 class="card-title mb-0">Existing Sections</h5>
            </div>
            <div class="card-body p-0">
                <select id="sectionClassFilter" class="form-select form-select-sm d-none">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="sections-table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Section Name</th>
                                <th>Class Name</th>
                                <th>Status</th>
                                <th>Capacity</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function() {
            const classFilter = $('#sectionClassFilter');

            var table = $('#sections-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-4"i p>',
                ajax: {
                    url: "{{ route('section.index') }}",
                    data: function(d) {
                        d.class_id = classFilter.val();
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
                        data: 'class_name',
                        name: 'class_name'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'capacity',
                        name: 'capacity'
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
                    searchPlaceholder: "Search sections...",
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
            $('#sections-table_filter').addClass('d-flex align-items-center gap-2');
            $('#sections-table_filter label').addClass('mb-0');
            classFilter.removeClass('d-none').css('min-width', '170px');
            $('#sections-table_filter').prepend(classFilter);
            $('#sections-table_filter').prepend('<span class="text-muted small mb-0 ms-1">Filter Class</span>');
            classFilter.on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/section-compact.css') }}">
@endpush
