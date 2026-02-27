@extends('layouts.admin')

@section('title', 'Create Section')

@section('content')
    <div class="container-fluid py-4 section-module-compact">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Create section and assign class with capacity</p>
            </div>
            <a href="{{ route('section.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Back
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

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 fw-bold">Section Information</h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                Please fix the highlighted fields and try again.
                            </div>
                        @endif

                        <form action="{{ route('section.store') }}" method="POST">
                            @csrf

                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label for="name" class="form-label fw-semibold">Section Name</label>
                                    <input type="text" class="form-control shadow-sm @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}" placeholder="e.g. Section A">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="class_id" class="form-label fw-semibold">Assigned Class</label>
                                    <select name="class_id"
                                        class="form-select shadow-sm @error('class_id') is-invalid @enderror">
                                        <option value="">Select Class</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}"
                                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="capacity" class="form-label fw-semibold">Capacity</label>
                                    <input type="number"
                                        class="form-control shadow-sm @error('capacity') is-invalid @enderror"
                                        name="capacity" min="1" value="{{ old('capacity') }}" placeholder="e.g. 40">
                                    @error('capacity')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="status" class="form-label fw-semibold">Status</label>
                                    <select name="status"
                                        class="form-select shadow-sm @error('status') is-invalid @enderror">
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="{{ route('section.index') }}" class="btn btn-light px-4 shadow-sm"
                                    style="border-radius:10px;">Cancel</a>
                                <button type="submit" class="btn btn-primary-fancy px-5 shadow-sm">Save Section</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/section-compact.css') }}">
@endpush
