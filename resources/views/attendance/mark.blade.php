@extends('layouts.admin')

@section('title', 'Mark Attendance')

@section('content')
    <div class="container-fluid py-4 attendance-modern">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Mark Attendance</h4>
                <p class="text-muted small mb-0">Today only · Select class & section to load students</p>
            </div>
            <div class="date-chip">Date: {{ $today }}</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif

        <div class="card glass-card mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('attendance.mark') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ $selectedClass == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" required>
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" {{ $selectedSection == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-modern w-100">Load Students</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($students->count())
            <div class="card glass-card">
                <div class="card-header d-flex justify-content-between align-items-center border-0">
                    <strong>Student List</strong>
                    <span class="meta-chip">Total: {{ $students->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <form method="POST" action="{{ route('attendance.mark.save') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClass }}">
                        <input type="hidden" name="section_id" value="{{ $selectedSection }}">
                        <input type="hidden" name="date" value="{{ $today }}">

                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th width="200">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        @php
                                            $status = $todayAttendance[$student->id]->status ?? 'present';
                                        @endphp
                                        <tr>
                                            <td>{{ $student->student_name }}</td>
                                            <td>
                                                <select name="attendance[{{ $student->id }}]" class="form-select">
                                                    <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Present</option>
                                                    <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Absent</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3 text-end">
                            <button type="submit" class="btn btn-modern px-5">Save Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-muted">No students loaded yet. Select class & section.</div>
            </div>
        @endif
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
            padding: 10px 16px;
        }
        .btn-modern:hover {
            filter: brightness(0.95);
            color: #fff;
        }
        .date-chip, .meta-chip {
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
