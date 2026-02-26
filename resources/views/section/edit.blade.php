@extends('layouts.admin')

@section('title', 'Edit Section')

@section('content')
    <div class="container-fluid py-4 section-module-compact">

        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('section.index') }}" class="btn btn-link text-decoration-none text-muted p-0 mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Sections
                </a>
            </div>
        </div>

        <!-- Edit Form Card -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-bold">Section Information</h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                Please Fix the hignlighted fields and try again.
                            </div>
                        @endif
                        <form action="{{ route('section.update', $section->id) }}" method="POST">
                            @csrf
                            @method('PUT')



                            <!-- Form Fields -->
                            <div class="row g-4 mb-4">
                                <!-- Section Name -->
                                <div class="col-md-4">
                                    <label for="name" class="form-label fw-semibold ">Section Name</label>
                                    <input type="text" class="form-control shadow-sm @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name', $section->name) }}"
                                        placeholder="e.g. Section A">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Assigned Class -->
                                <div class="col-md-4">
                                    <label for="class_id" class="form-label fw-semibold">Assigned Class</label>
                                    <select name="class_id"
                                        class="form-select shadow-sm @error('class_id') is-invalid @enderror">
                                        <option value="">Select Class</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}"
                                                {{ old('class_id', $section->class_id) == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Capacity -->
                                <div class="col-md-4">
                                    <label for="capacity" class="form-label fw-semibold">Capacity</label>
                                    <input type="number"
                                        class="form-control shadow-sm @error('capacity') is-invalid @enderror"
                                        name="capacity" min="1" value="{{ old('capacity', $section->capacity) }}"
                                        placeholder="e.g. 40">
                                    @error('capacity')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="col-md-4">
                                    <label for="status" class="form-label fw-semibold">Status</label>
                                    <select name="status"
                                        class="form-select shadow-sm @error('status') is-invalid @enderror">
                                        <option value="1"
                                            {{ old('status', $section->status) == 1 ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0"
                                            {{ old('status', $section->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="{{ route('section.index') }}" class="btn btn-light px-4 shadow-sm"
                                    style="border-radius:10px;">Discard Changes</a>
                                <button type="submit" class="btn btn-primary-fancy px-5 shadow-sm">Update Section</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional Extra CSS for Modern Effects -->
    <style>
        .shadow-sm {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .btn-primary-fancy {
            background-color: #5D59E0;
            border-color: #5D59E0;
            color: #fff;
            border-radius: 10px;
        }

        .btn-primary-fancy:hover {
            background-color: #4b47c7;
            border-color: #4b47c7;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(93, 89, 224, 0.25);
            border-color: #5D59E0;
        }
    </style>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/section-compact.css') }}">
@endpush
