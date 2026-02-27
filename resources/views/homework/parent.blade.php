@extends('layouts.admin')

@section('title', 'Child Homework Status')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/homework-compact.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4 hw-page">
        <div class="card hw-card">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Child Homework Status</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Child</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Homework</th>
                                <th>Assigned By</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                @php
                                    $submitted = $row['status'] === 'Yes';
                                    $statusText = $submitted ? 'Submitted' : 'Not Submitted';
                                @endphp
                                <tr>
                                    <td class="ps-3">{{ $row['student_name'] }}</td>
                                    <td>{{ $row['class_section'] ?? '-' }}</td>
                                    <td>{{ $row['subject'] ?? '-' }}</td>
                                    <td>{{ $row['homework_title'] }}</td>
                                    <td>{{ $row['assigned_by'] ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($row['due_date'])->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $submitted ? 'bg-success' : 'bg-danger' }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No homework found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
