@extends('layouts.admin')

@section('title', 'Child Attendance')

@section('content')
    <div class="container-fluid py-4 attendance-modern">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Child Attendance</h4>
                <p class="text-muted small mb-0">Monthly percentage per child</p>
            </div>
        </div>

        @forelse ($summaries as $summary)
            <div class="card mb-4 glass-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $summary['student']->student_name }}</strong>
                    <span class="date-chip">Overall: {{ $summary['percent'] }}%</span>
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
                                @forelse ($summary['monthly'] as $month => $data)
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
        @empty
            <div class="card">
                <div class="card-body text-muted">No children mapped.</div>
            </div>
        @endforelse
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
