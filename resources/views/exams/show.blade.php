@extends('layouts.admin')

@section('title', 'Exam Details')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Exam Details</h5>
            <a href="{{ route('exams.createexam') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th style="width: 200px;">Exam Name</th>
                        <td>{{ $exam->name }}</td>
                    </tr>
                    <tr>
                        <th>Academic Year</th>
                        <td>{{ $exam->academicYear->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Class</th>
                        <td>{{ $exam->class->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{ $exam->section->name ?? 'All Sections' }}</td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td>{{ $exam->start_date->format('d M, Y') }}</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>{{ $exam->end_date->format('d M, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Time</th>
                        <td>{{ $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('h:i A') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>End Time</th>
                        <td>{{ $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('h:i A') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Total Marks</th>
                        <td>{{ $exam->total_mark }}</td>
                    </tr>
                    <tr>
                        <th>Passing Marks</th>
                        <td>{{ $exam->passing_mark }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($exam->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
