@extends('layouts.admin')

@section('title', 'Edit Academic Year')

@section('content')
    <div class="container-fluid academic-white py-4">

        <!-- PAGE HEADER -->
        <div class="page-header mb-3">
            <div>
                <p class="page-subtitle">Update academic year details used system-wide</p>
            </div>

            <a href="{{ route('academic.year.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h5>Academic Year Information</h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('academic.year.update', $year->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            @if ($errors->any())
                                <div class="alert alert-danger border-0 mb-4">
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Academic Year Name</label>
                                    <input name="name" class="form-control" value="{{ $year->name }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ $year->start_date }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $year->end_date }}"
                                        required>
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
