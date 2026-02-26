@extends($layout ?? 'layouts.admin')

@section('title', 'Result View')

@push('css')
<link rel="stylesheet" href="{{ asset('css/results.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4 res-shell">
    <div class="d-flex justify-content-between align-items-center mb-3 p-3 res-title-bar">
        <div>
            <h5 class="mb-1">Student Result View</h5>
            <div class="small opacity-75">Subject wise marks and pass/fail status</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ $backRoute ?? route('teacher.results') }}" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            @include('results.partials.print-button')
        </div>
    </div>

    <div class="card res-card mb-3">
        <div class="card-body">
            <div class="res-sheet">
                <div class="res-sheet-head">Student Details</div>
                <div class="res-sheet-row">
                    <div class="res-k">Student Name</div>
                    <div class="res-v">{{ $studentModel->student_name ?? '-' }}</div>
                    <div class="res-k">Roll No</div>
                    <div class="res-v">{{ $studentModel->roll_no ?? '-' }}</div>
                    <div class="res-k">Class</div>
                    <div class="res-v">{{ $studentModel->class->name ?? '-' }}</div>
                    <div class="res-k">Section</div>
                    <div class="res-v">{{ $section->name ?? '-' }}</div>
                    <div class="res-k">Exam Name</div>
                    <div class="res-v">{{ $examModel->name ?? '-' }}</div>
                    <div class="res-k">Declared Date</div>
                    <div class="res-v">{{ $declaredDate }}</div>
                    <div class="res-k">Academic Year</div>
                    <div class="res-v">{{ $academicYearName }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card res-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Subject Wise Result</h5>
            <span class="res-pill {{ strtolower($overallResult) === 'pass' ? 'res-pill-ok' : 'res-pill-wait' }}">
                {{ strtoupper($overallResult) }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive res-table-wrap">
                <table class="table table-hover align-middle mb-0 res-table">
                    <thead class="table-light res-head">
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Subject</th>
                            <th class="text-center">Total Marks</th>
                            <th class="text-center">Passing Marks</th>
                            <th class="text-center">Obtained Marks</th>
                            <th class="text-center">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subjectRows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->subject_name }}</td>
                                <td class="text-center">{{ $row->total_mark }}</td>
                                <td class="text-center">{{ $row->passing_mark }}</td>
                                <td class="text-center">{{ $row->obtained_mark }}</td>
                                <td class="text-center">
                                    @if (strtolower($row->status) === 'pass')
                                        <span class="res-pill res-pill-ok">Pass</span>
                                    @elseif (strtolower($row->status) === 'fail')
                                        <span class="res-pill res-pill-wait">Fail</span>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No result data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <strong>Message:</strong> {{ $resultMessage }}
        </div>
    </div>
</div>
@endsection

@include('results.partials.print-script')

