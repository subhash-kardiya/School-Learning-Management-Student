@extends('layouts.admin')

@section('title', 'My Attendance')

@section('content')
    <div class="container-fluid py-4 attendance-modern">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="mb-1">My Attendance</h4>
                <p class="text-muted small mb-0">Overall attendance percentage</p>
            </div>
        </div>

        <div class="card glass-card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-0">{{ $percent }}%</h3>
                    <p class="text-muted mb-0">Overall Attendance</p>
                </div>
                <div class="date-chip">Student: {{ $student->student_name }}</div>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-header">
                <strong>Monthly Summary</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Present</th>
                                <th>Total</th>
                                <th>Percent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($monthly as $month => $data)
                                <tr>
                                    <td>{{ $month }}</td>
                                    <td>{{ $data['present'] }}</td>
                                    <td>{{ $data['total'] }}</td>
                                    <td>{{ $data['percent'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No attendance data.</td>
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
