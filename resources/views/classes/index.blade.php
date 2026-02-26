@extends('layouts.admin')

@section('title', 'Classes Management')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Manage class structures, academic years, and teacher assignments</p>
            </div>
            <button type="button" class="btn btn-primary-fancy" data-bs-toggle="collapse" data-bs-target="#classForm">
                <i class="fa fa-plus me-2"></i> Create New Class
            </button>
        </div>

        <!-- Collapsible form -->
        <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="classForm">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Create New Class</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('classes.store') }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm mb-4">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Class Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="e.g. Class 10-A" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="academic_year_id" class="form-label">Academic Year</label>
                                <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                                    <option value="">Select Academic Year</option>
                                    @php
                                        $academicYears = \App\Models\AcademicYear::all();
                                    @endphp
                                    @foreach ($academicYears as $year)
                                        <option value="{{ $year->id }}"
                                            {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="class_teacher_id" class="form-label">Class Teacher</label>
                                <select name="class_teacher_id" id="class_teacher_id" class="form-select" required>
                                    <option value="">Select Teacher</option>
                                    @php
                                        $teachers = \App\Models\Teacher::all();
                                    @endphp
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ old('class_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary-fancy px-5">Save Class Information</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- List of Classes -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Existing Classes</h5>
            </div>
            <div class="card-body p-0">
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

@push('scripts')
    <script type="text/javascript">
        $(function() {
            var table = $('#classes-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-2"i p>',
                ajax: "{{ route('classes.index') }}",
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
        });
    </script>
@endpush
