@extends('layouts.admin')

@section('title', 'Create Subject')

@section('content')
<div class="container-fluid py-4 subject-module-compact">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('subjects.index') }}" class="btn btn-link text-decoration-none text-muted p-0 mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Subjects List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold">Subject Information</h5>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            Please fix the highlighted fields and try again.
                        </div>
                    @endif
                    <form action="{{ route('subjects.store') }}" method="POST">
                        @csrf

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Subject Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control shadow-sm @error('name') is-invalid @enderror"
                                    placeholder="e.g. Mathematics" value="{{ old('name') }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="subject_code" class="form-label fw-semibold">Course Code</label>
                                <input type="text" name="subject_code" id="subject_code"
                                    class="form-control shadow-sm @error('subject_code') is-invalid @enderror"
                                    placeholder="e.g. MATH101" value="{{ old('subject_code') }}">
                                @error('subject_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="class_id" class="form-label fw-semibold">Assign Class</label>
                                <select name="class_id" id="class_id"
                                    class="form-select shadow-sm @error('class_id') is-invalid @enderror">
                                    <option value="">Select Class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold">Subject Status</label>
                                <select name="status" id="status"
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
                            <a href="{{ route('subjects.index') }}" class="btn btn-light px-4 shadow-sm"
                                style="border-radius:10px;">Cancel</a>
                            <button type="submit" class="btn btn-primary-fancy px-5 shadow-sm">Save Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/subject-compact.css') }}">
@endpush
