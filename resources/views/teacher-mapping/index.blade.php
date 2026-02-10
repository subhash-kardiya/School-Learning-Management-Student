@extends('layouts.admin')

@section('title', 'Class Mapping')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Map teachers to class sections for easy scheduling</p>
            </div>
            <button type="button" class="btn btn-primary-fancy" data-bs-toggle="collapse" data-bs-target="#mappingForm">
                <i class="fa fa-plus me-2"></i> New Mapping
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
        @endif

        <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="mappingForm">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Create Teacher Mapping</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('teacher.mapping.store') }}" method="POST">
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
                                <label for="teacher_id" class="form-label">Teacher</label>
                                <select name="teacher_id" id="teacher_id" class="form-select" required>
                                    <option value="">Select Teacher</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="section_id" class="form-label">Section</label>
                                <select name="section_id" id="section_id" class="form-select" required>
                                    <option value="">Select Section</option>
                                    @foreach ($classes as $class)
                                        @if ($class->sections && $class->sections->count())
                                            <optgroup label="{{ $class->name }}">
                                                @foreach ($class->sections as $section)
                                                    <option value="{{ $section->id }}"
                                                        {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                        {{ $section->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="subject_id" class="form-label">Subject</label>
                                <select name="subject_id" id="subject_id" class="form-select" required>
                                    <option value="">Select Subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ $subject->subject_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary-fancy px-5">Save Mapping</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Existing Mappings</h5>
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
            $('#teacher-mapping-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"d-flex justify-content-between align-items-center p-2 border-bottom"l f>rt<"d-flex justify-content-between align-items-center p-2"i p>',
                ajax: "{{ route('teacher.mapping') }}",
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
                }
            });

            $('.dataTables_length select').addClass('form-select-sm');
        });
    </script>
@endpush
