@extends('layouts.admin')

@section('title', 'Attendance Report')

@section('content')
    <div class="container-fluid py-4 attendance-modern">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Attendance Report</h4>
                <p class="text-muted small mb-0">Class-wise daily report</p>
            </div>
            <div class="date-chip">Date: {{ $date }}</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif

        <div class="card glass-card mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('attendance.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select">
                            <option value="">All</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}">
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-modern px-4">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class / Section</th>
                                <th>Status</th>
                                <th class="text-end">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $row)
                                <tr>
                                    <td>{{ $row->student?->student_name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $row->student?->class?->name ?? 'N/A' }} /
                                        {{ $row->student?->section?->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $row->status == 'present' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($row->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('attendance.update') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="attendance_id" value="{{ $row->id }}">
                                            <select name="status" class="form-select form-select-sm d-inline w-auto">
                                                <option value="present" {{ $row->status == 'present' ? 'selected' : '' }}>Present</option>
                                                <option value="absent" {{ $row->status == 'absent' ? 'selected' : '' }}>Absent</option>
                                            </select>
                                            <button class="btn btn-sm btn-modern">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .attendance-modern {
            background: linear-gradient(180deg, #f6f8ff 0%, #f9fbff 100%);
            border-radius: 16px;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #eef1ff;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.06);
            border-radius: 14px;
        }
        .btn-modern {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 14px;
        }
        .btn-modern:hover { filter: brightness(0.95); color: #fff; }
        .date-chip {
            background: #eef2ff;
            color: #374151;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
        }
        .table-modern thead th {
            background: #f3f4ff;
            border-bottom: 0;
        }
    </style>
@endsection
