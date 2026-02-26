@extends('layouts.admin')

@section('title', 'Academic Years')

@section('content')
    <div class="container-fluid py-2 academic-white academic-year-compact">
        <div class="page-header mb-3">
            <div>
                <p class="page-subtitle">
                    Define academic years used across admissions, attendance, exams and reports
                </p>
            </div>
            <a href="{{ route('academic.year.create') }}" class="btn btn-primary-fancy btn-sm">
                <i class="fa fa-plus me-2"></i> Add Academic Year
            </a>
        </div>

        <div class="card shadow-sm">


            <div class="card-body p-0">
                <div class="table-responsive-md">
                    <table class="table table-sm table-hover align-middle mb-0" id="academic-years-table">
                        <thead>
                            <tr>
                                <th width="20">ID</th>
                                <th>Academic Year</th>
                                <th>Start Date</th>
                                <th>End Date</th>
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
    <link rel="stylesheet" href="{{ asset('css/resize/academic-year-compact.css') }}">
@endpush

@push('scripts')
    <script>
        $(function() {
            $('#academic-years-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>' +
                    'rt' +
                    '<"d-flex justify-content-between align-items-center p-2"i p>',
                ajax: "{{ route('academic.year.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date'
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search academic years...",
                    lengthMenu: "_MENU_",
                    processing: '<div class="spinner-border text-primary" role="status"></div>',
                    paginate: {
                        next: '<i class="fas fa-chevron-right"></i>',
                        previous: '<i class="fas fa-chevron-left"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('mb-0');
                    $('.dataTables_filter input').addClass('form-control-sm');
                }
            });

            $('.dataTables_length select').addClass('form-select-sm');
        });

    </script>
@endpush
