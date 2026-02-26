@extends('layouts.admin')

@section('title', 'Students Management')

@section('content')
    <div class="container-fluid py-4 student-module-compact">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <p class="text-muted mb-0">Manage student profiles, class assignments, and academic status</p>
            </div>
            @can('student_add')
                <a href="{{ route('students.create') }}" class="btn btn-primary shadow-sm px-4 py-2">
                    <i class="fas fa-user-plus me-2"></i> New Admission
                </a>
            @endcan
        </div>

        <div class="card glass-card shadow-sm border-0">
            <div class="d-none" id="student-filter-holder">
                <select id="filter-class" class="form-select form-select-sm" style="min-width: 160px;">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <select id="filter-section" class="form-select form-select-sm" style="min-width: 160px;">
                    <option value="">All Sections</option>
                </select>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="students-table" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th width="20">#</th>
                                <th>Student</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/student-compact.css') }}">
@endpush

@push('scripts')
    <script>
        $(function() {
            const classSections = @json(
                $classes->mapWithKeys(function ($class) {
                    return [
                        $class->id => $class->sections->map(function ($section) {
                                return ['id' => $section->id, 'name' => $section->name];
                            })->values(),
                    ];
                }));

            function renderSections(classId) {
                const $section = $('#filter-section');
                const selected = $section.val();
                $section.empty().append('<option value="">All Sections</option>');

                if (!classId || !classSections[classId]) {
                    return;
                }

                classSections[classId].forEach(function(item) {
                    $section.append(`<option value="${item.id}">${item.name}</option>`);
                });

                if (selected && $section.find(`option[value="${selected}"]`).length) {
                    $section.val(selected);
                }
            }

            var table = $('#students-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('students.data') }}",
                    data: function(d) {
                        d.class_id = $('#filter-class').val();
                        d.section_id = $('#filter-section').val();
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
                        name: 'student_name',
                        render: function(data, type, row) {
                            return `<div class="student-info">
                                <img src="${row.avatar}" alt="Avatar">
                                <div>
                                    <div class="fw-bold">${row.name}</div>
                                    <div class="text-muted small">${row.roll_no ?? ''}</div>
                                </div>
                            </div>`;
                        }
                    },
                    {
                        data: 'username',
                        name: 'username',
                        render: function(data) {
                            return `<span class="username-badge">${data}</span>`;
                        }
                    },
                    {
                        data: 'email',
                        name: 'email',
                        render: function(data) {
                            return `<i class="fas fa-envelope me-1"></i>${data}`;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data) {
                            return data == 1 ?
                                '<span class="status-badge status-active">Active</span>' :
                                '<span class="status-badge status-inactive">Inactive</span>';
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
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-3"i p>',
                language: {
                    search: "",
                    searchPlaceholder: "Search students...",
                    lengthMenu: "_MENU_",
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                },
                initComplete: function() {
                    const $wrapper = $(this.api().table().container());
                    const $filter = $wrapper.find('div.dataTables_filter');
                    const $label = $filter.find('label');
                    const $input = $label.find('input');

                    const $grid = $('<div class="student-search-grid"></div>');
                    $filter.addClass('d-flex align-items-center gap-2 ms-auto');
                    $label.addClass('mb-0');
                    $input.addClass('form-control form-control-sm').attr('placeholder', 'Search students...');

                    $label.contents().filter(function() {
                        return this.nodeType === 3;
                    }).remove();

                    const $class = $('#filter-class');
                    const $section = $('#filter-section');

                    $grid.css({
                        display: 'flex',
                        flexWrap: 'nowrap',
                        alignItems: 'center',
                        gap: '10px',
                        overflowX: 'auto'
                    });

                    $class.css({
                        minWidth: '170px',
                        width: '170px',
                        flex: '0 0 auto',
                        display: 'inline-block'
                    });
                    $section.css({
                        minWidth: '170px',
                        width: '170px',
                        flex: '0 0 auto',
                        display: 'inline-block'
                    });
                    $input.css({
                        minWidth: '220px',
                        width: '220px',
                        flex: '0 0 auto',
                        display: 'inline-block'
                    });

                    $grid.append('<span class="student-filter-label">Filter Class</span>');
                    $grid.append($class);
                    $grid.append($section);
                    $grid.append($input);

                    $label.replaceWith($grid);
                }
            });

            $('#filter-class').on('change', function() {
                renderSections(this.value);
                $('#filter-section').val('');
                table.draw();
            });

            $('#filter-section').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush
