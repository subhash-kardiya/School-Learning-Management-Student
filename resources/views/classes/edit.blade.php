@extends('layouts.admin')

@section('title', 'Edit Class')

@section('content')
    <div class="container-fluid academic-white py-4 class-module-compact">

        <!-- PAGE HEADER -->
        <div class="page-header mb-3">
            <div>
                <p class="page-subtitle">Update class information used across the system</p>
            </div>

            <a href="{{ route('classes.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h5>Class Information</h5>
                    </div>

                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                Please fix the highlighted fields and try again.
                            </div>
                        @endif

                        <form action="{{ route('classes.update', $class->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <?php if(session('success')): ?>
                            <div class="alert alert-success border-0 shadow-sm mb-3">
                                <?php echo e(session('success')); ?>

                            </div>
                            <?php endif; ?>
                            <?php if(session('error')): ?>
                            <div class="alert alert-danger border-0 shadow-sm mb-3">
                                <?php echo e(session('error')); ?>

                            </div>
                            <?php endif; ?>


                            <!-- ROW 1 -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Class Name</label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $class->name) }}" placeholder="e.g. Class 10-A">

                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Academic Year</label>
                                    <select name="academic_year_id"
                                        class="form-select @error('academic_year_id') is-invalid @enderror">
                                        <option value="">Select Academic Year</option>
                                        @foreach ($academicYears as $year)
                                            <option value="{{ $year->id }}"
                                                {{ old('academic_year_id', $class->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- ROW 2 -->
                            <div class="row g-4 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">Class Teacher</label>
                                    <select name="class_teacher_id"
                                        class="form-select @error('class_teacher_id') is-invalid @enderror">
                                        <option value="">Select Teacher</option>
                                        @foreach ($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                {{ old('class_teacher_id', $class->class_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_teacher_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="1" {{ old('status', $class->status) == 1 ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="0" {{ old('status', $class->status) == 0 ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- FOOTER ACTIONS -->
                            <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-4">
                                <a href="{{ route('classes.index') }}" class="btn btn-light px-4">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary-fancy px-5">
                                    Update Class
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

            </div>
        </div>

    </div>
@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/class-compact.css') }}">
@endpush

@endsection
