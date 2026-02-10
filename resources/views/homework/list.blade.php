@extends('layouts.admin')

@section('title', 'Homework List')

@section('content')
    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Homework List</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                @if (isset($student))
                                    <th>Submission</th>
                                    <th>Feedback</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($homeworks as $hw)
                                <tr>
                                    <td>{{ $hw->title }}</td>
                                    <td>{{ $hw->class?->name ?? 'N/A' }} / {{ $hw->section?->name ?? 'N/A' }}</td>
                                    <td>{{ $hw->subject?->name ?? 'N/A' }}</td>
                                    <td>{{ $hw->due_date }}</td>
                                    <td>
                                        @if ($hw->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    @if (isset($student))
                                        @php
                                            $submission = $submissions[$hw->id] ?? null;
                                        @endphp
                                        <td>
                                            @if ($submission)
                                                <span class="badge bg-success">{{ $submission->status }}</span>
                                            @else
                                                <form action="{{ route('student.homework.submit', $hw->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="file" name="attachment" class="form-control form-control-sm mb-2" required>
                                                    <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($submission && $submission->feedback)
                                                <span class="text-muted">{{ $submission->feedback }}</span>
                                            @else
                                                <span class="text-muted small">No feedback</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ isset($student) ? 7 : 5 }}" class="text-center text-muted py-4">
                                        No homework found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
