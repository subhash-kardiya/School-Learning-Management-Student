@extends('layouts.admin')

@section('title', 'Homework Submission')

@section('content')
    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Homework Submission</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Homework</th>
                                <th>Submitted On</th>
                                <th>Status</th>
                                <th>Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($submissions as $sub)
                                <tr>
                                    <td>{{ $sub->student?->student_name ?? 'N/A' }}</td>
                                    <td>{{ $sub->homework?->class?->name ?? 'N/A' }} / {{ $sub->homework?->section?->name ?? 'N/A' }}</td>
                                    <td>{{ $sub->homework?->title ?? 'N/A' }}</td>
                                    <td>{{ $sub->submitted_at ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $sub->status }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('homework.submission.feedback', $sub->id) }}" method="POST">
                                            @csrf
                                            <input type="text" name="feedback" class="form-control form-control-sm mb-2"
                                                value="{{ $sub->feedback }}" placeholder="Feedback">
                                            <select name="status" class="form-select form-select-sm mb-2">
                                                <option value="Submitted" {{ $sub->status == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                                                <option value="Reviewed" {{ $sub->status == 'Reviewed' ? 'selected' : '' }}>Reviewed</option>
                                                <option value="Pending" {{ $sub->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No submissions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
