@extends('layouts.admin')

@section('title', 'Edit Class Mapping')

@section('content')
    <div class="container-fluid py-4 class-mapping-compact">
        <div class="mb-3">
            <a href="{{ route('teacher.mapping') }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Class Mapping
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Class Mapping</h5>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        Please fix the highlighted fields and try again.
                    </div>
                @endif

                <form action="{{ route('teacher.mapping.update', $mapping->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('teacher-mapping._form', ['mapping' => $mapping])
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary-fancy px-5">Update Mapping</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/class-mapping-compact.css') }}">
@endpush
