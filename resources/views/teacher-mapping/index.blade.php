@extends('layouts.admin')

@section('title', 'Class Mapping')

@section('content')
    <div class="container-fluid py-4 class-mapping-compact">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Map teachers to class sections for easy scheduling</p>
            </div>
            <a href="{{ route('teacher.mapping.create') }}" class="btn btn-primary-fancy">
                <i class="fa fa-plus me-2"></i> New Mapping
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Existing Mappings</h5>
            </div>
            <div class="d-none" id="mapping-filter-holder">
                <select id="filter-class-id" class="form-select form-select-sm" style="min-width: 180px;">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <select id="filter-section-id" class="form-select form-select-sm" style="min-width: 170px;">
                    <option value="">All Sections</option>
                </select>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="teacher-mapping-table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Teacher</th>
                                <th>Class / Section</th>
                                <th>Subject</th>
                                <th>Room</th>
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
    <link rel="stylesheet" href="{{ asset('css/resize/class-mapping-compact.css') }}">
@endpush

@push('scripts')
    <script type="text/javascript">
        $(function() {
            const classSections = @json(
                $classes->mapWithKeys(function ($class) {
                    return [
                        $class->id => $class->sections->map(function ($section) {
                                return ['id' => $section->id, 'name' => $section->name];
                            })->values(),
                    ];
                }));

            function renderFilterSections(classId) {
                const $filterSection = $('#filter-section-id');
                const selected = $filterSection.val();
                $filterSection.empty().append('<option value="">All Sections</option>');

                if (!classId || !classSections[classId]) return;

                classSections[classId].forEach(function(item) {
                    $filterSection.append(`<option value="${item.id}">${item.name}</option>`);
                });

                if (selected && $filterSection.find(`option[value="${selected}"]`).length) {
                    $filterSection.val(selected);
                }
            }

            const table = $('#teacher-mapping-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-2"i p>',
                ajax: {
                    url: "{{ route('teacher.mapping') }}",
                    data: function(d) {
                        d.class_id = $('#filter-class-id').val();
                        d.section_id = $('#filter-section-id').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'teacher_name',
                        name: 'teacher_name'
                    },
                    {
                        data: 'mapping_info',
                        name: 'mapping_info',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'subject_name',
                        name: 'subject_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'room_name',
                        name: 'room_name',
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
                    searchPlaceholder: "Search mappings...",
                    lengthMenu: "_MENU_",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                },
                initComplete: function() {
                    const $filter = $('#teacher-mapping-table_filter');
                    $filter.addClass('d-flex align-items-center gap-2');
                    $filter.find('label').addClass('mb-0');
                    $('#filter-section-id').insertBefore($filter.find('label'));
                    $('#filter-class-id').insertBefore($('#filter-section-id'));
                }
            });

            $('.dataTables_length select').addClass('form-select-sm');
            $('#filter-class-id').on('change', function() {
                renderFilterSections(this.value);
                $('#filter-section-id').val('');
                table.draw();
            });
            $('#filter-section-id').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush
