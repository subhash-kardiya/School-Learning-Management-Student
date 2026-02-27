@extends('layouts.admin')

@section('title', 'Edit Academic Year')

@section('content')
    <div class="container-fluid academic-white py-4 academic-year-compact">

        <!-- PAGE HEADER -->
        <div class="page-header mb-3">
            <div>
                <p class="page-subtitle">Update academic year details used system-wide</p>
            </div>

            <a href="{{ route('academic.year.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <!-- SUCCESS MESSAGE -->
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

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h5>Academic Year Information</h5>
                    </div>

                    <div class="card-body p-4">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                Please fix the highlighted fields and try again.
                            </div>
                        @endif

                        <form action="{{ route('academic.year.update', $year->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Academic Year Name</label>
                                    <input name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $year->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date"
                                        class="form-control @error('start_date') is-invalid @enderror"
                                        value="{{ old('start_date', $year->start_date) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date"
                                        class="form-control @error('end_date') is-invalid @enderror"
                                        value="{{ old('end_date', $year->end_date) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-4">
                                <a href="{{ route('academic.year.index') }}" class="btn btn-light px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary-fancy px-5">
                                    Update Academic Year
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>

    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/academic-year-compact.css') }}">
@endpush
