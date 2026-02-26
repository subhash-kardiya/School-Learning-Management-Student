@extends($layout ?? 'layouts.admin')

@section('title', 'Child Declared Results')

@push('css')
<link rel="stylesheet" href="{{ asset('css/results.css') }}">
@endpush

@section('content')
    @php
        $examCount = $marks->count();
        $avgPercent = $marks->count() > 0
            ? number_format(
                $marks->filter(fn($m) => ($m->exam->total_mark ?? 0) > 0 && $m->marks_obtained !== null)
                    ->avg(fn($m) => ($m->marks_obtained / max(1, (float) ($m->exam->total_mark ?? 0))) * 100) ?? 0,
                2
            )
            : null;
        $totalObtained = (float) $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
        $totalMarks = (float) $marks->sum(fn($m) => (float) ($m->exam->total_mark ?? 0));
        $overallPercentage = $totalMarks > 0 ? round(($totalObtained / $totalMarks) * 100, 2) : 0;
        $hasPassingRules = $marks->contains(fn($m) => $m->exam?->passing_mark !== null);
        if ($hasPassingRules) {
            $overallPass = $marks->count() > 0
                && $marks->every(function ($m) {
                    return $m->marks_obtained !== null
                        && $m->exam?->passing_mark !== null
                        && (float) $m->marks_obtained >= (float) $m->exam->passing_mark;
                });
        } else {
            $overallPass = $marks->count() > 0 && $overallPercentage >= 60;
        }

        $fallbackGradeRules = [
            ['name' => 'A', 'start_mark' => 90, 'end_mark' => 100],
            ['name' => 'B', 'start_mark' => 80, 'end_mark' => 89.99],
            ['name' => 'C', 'start_mark' => 70, 'end_mark' => 79.99],
            ['name' => 'D', 'start_mark' => 60, 'end_mark' => 69.99],
            ['name' => 'F', 'start_mark' => 0, 'end_mark' => 59.99],
        ];
        $overallGrade = \App\Models\Grade::query()
            ->where('start_mark', '<=', $overallPercentage)
            ->where('end_mark', '>=', $overallPercentage)
            ->orderByDesc('start_mark')
            ->value('name');
        if (!$overallGrade) {
            foreach ($fallbackGradeRules as $rule) {
                if ($overallPercentage >= $rule['start_mark'] && $overallPercentage <= $rule['end_mark']) {
                    $overallGrade = $rule['name'];
                    break;
                }
            }
        }
        if (strtoupper((string) $overallGrade) === 'E') {
            $overallGrade = 'F';
        }
        $overallGrade = $overallGrade ?: '-';

        $numToWords = function ($num) use (&$numToWords) {
            $num = (int) $num;
            $ones = [0 => 'ZERO', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE', 6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE', 10 => 'TEN', 11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN', 16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN'];
            $tens = [2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY', 6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY'];
            if ($num < 20) return $ones[$num];
            if ($num < 100) return $tens[intdiv($num, 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
            if ($num < 1000) return $ones[intdiv($num, 100)] . ' HUNDRED' . ($num % 100 ? ' ' . $numToWords($num % 100) : '');
            if ($num < 1000000) return $numToWords(intdiv($num, 1000)) . ' THOUSAND' . ($num % 1000 ? ' ' . $numToWords($num % 1000) : '');
            return (string) $num;
        };
        $obtainedInWords = $numToWords((int) round($totalObtained)) . ' ONLY';
    @endphp
    <div class="container-fluid py-4 res-shell">
        <div class="d-flex justify-content-between align-items-center mb-3 p-3 res-title-bar">
            <div>
                <h5 class="mb-1">Child Declared Results</h5>
                <div class="small opacity-75">Select child and view project declared result</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @include('results.partials.print-button')
                <span class="px-3 py-2 res-status-badge">Declared Results</span>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="res-stat h-100" style="background:#ecfeff;border:1px solid #a5f3fc;">
                    <div class="text-muted small">Children Linked</div>
                    <div class="fs-4 fw-bold text-dark">{{ $children->count() }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="res-stat h-100" style="background:#eff6ff;border:1px solid #bfdbfe;">
                    <div class="text-muted small">Declared Subjects</div>
                    <div class="fs-4 fw-bold text-dark">{{ $examCount }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="res-stat h-100" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <div class="text-muted small">Average Percentage</div>
                    <div class="fs-4 fw-bold text-dark">{{ $avgPercent !== null ? $avgPercent . '%' : '-' }}</div>
                </div>
            </div>
        </div>

        <div class="card res-card mb-3 res-filter-card">
            <div class="card-header">
                <h5 class="mb-0">Child Declared Results</h5>
            </div>
            <div class="card-body">
                @if ($children->isNotEmpty())
                    <form method="GET" action="{{ route('parent.results') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="session_id" class="form-label">Select Academic Year</label>
                            <select name="session_id" id="session_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Select Academic Year</option>
                                @foreach (($sessionOptions ?? collect()) as $sessionOption)
                                    <option value="{{ $sessionOption->id }}" {{ (int) ($selectedSessionId ?? 0) === (int) $sessionOption->id ? 'selected' : '' }}>
                                        {{ $sessionOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="student_id" class="form-label">Select Child</label>
                            <select name="student_id" id="student_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Select Child</option>
                                @foreach ($children as $child)
                                    <option value="{{ $child->id }}" {{ (string) $child->id === (string) request('student_id', '') ? 'selected' : '' }}>
                                        Child {{ $loop->iteration }} - {{ $child->student_name }}
                                        (Roll: {{ $child->roll_no ?? 'N/A' }}, {{ $child->class->name ?? 'N/A' }}{{ $child->section ? ' - ' . $child->section->name : '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @else
                    <div class="alert alert-info mb-0">No children are mapped to this parent account.</div>
                @endif
            </div>
        </div>

        @if (!empty($canShowParentResult) && $selectedStudent)
            @php
                $examNames = $marks->map(fn($m) => $m->exam->name ?? null)->filter()->unique()->values();
                $selectedExamLabel = $examNames->count() === 1 ? $examNames->first() : ($examNames->count() > 1 ? 'Multiple Exams' : '-');
                $declaredAt = null;
                if ($examNames->count() === 1) {
                    $singleExam = $marks->firstWhere('exam.name', $examNames->first())?->exam;
                    if (!empty($singleExam?->updated_at)) {
                        $declaredAt = \Carbon\Carbon::parse($singleExam->updated_at);
                    }
                }
                if (!$declaredAt) {
                    $declaredAt = $marks->map(fn($m) => $m->exam?->updated_at)
                        ->filter()
                        ->map(fn($d) => \Carbon\Carbon::parse($d))
                        ->sortDesc()
                        ->first();
                }
            @endphp
            <div class="card res-card mb-3">
                <div class="card-body">
                    <div class="res-sheet">
                        <div class="res-sheet-head">Student Details</div>
                        <div class="res-sheet-row">
                            <div class="res-k">Student Name</div>
                            <div class="res-v">{{ $selectedStudent->student_name ?? '-' }}</div>
                            <div class="res-k">Roll No</div>
                            <div class="res-v">{{ $selectedStudent->roll_no ?? '-' }}</div>
                            <div class="res-k">Class</div>
                            <div class="res-v">{{ $selectedStudent->class->name ?? '-' }}</div>
                            <div class="res-k">Section</div>
                            <div class="res-v">{{ $selectedStudent->section->name ?? '-' }}</div>
                            <div class="res-k">Exam Type</div>
                            <div class="res-v">{{ $selectedExamLabel }}</div>
                            <div class="res-k">Declared Date</div>
                            <div class="res-v">{{ $declaredAt ? $declaredAt->format('d M Y') : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (empty($canShowParentResult))
            <div class="card res-card mb-3">
                <div class="card-body text-center py-4">
                    <div class="fw-semibold text-dark">Select Academic Year and Child to view declared results.</div>
                </div>
            </div>
        @endif

        @if (!empty($canShowParentResult))
        <div class="card res-card">
            <div class="card-header">
                <h6 class="mb-0">
                    {{ $selectedStudent ? ($selectedStudent->student_name . ' - Result Details') : 'Result Details' }}
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive res-table-wrap">
                    <table class="table table-hover align-middle mb-0 res-table">
                        <thead class="table-light res-head">
                            <tr>
                                <th style="width:70px;">No</th>
                                <th>Subject Name</th>
                                <th>Grade</th>
                                <th class="text-center">Total Marks</th>
                                <th class="text-center">Passing Marks</th>
                                <th class="text-center">Obtain Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($marks as $mark)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $mark->subject->name ?? '-' }}</td>
                                    <td><span class="res-pill res-grade">{{ $mark->grade ?? '-' }}</span></td>
                                    <td class="text-center"><span class="res-num">{{ $mark->exam->total_mark ?? '-' }}</span></td>
                                    <td class="text-center"><span class="res-num">{{ $mark->exam->passing_mark ?? '-' }}</span></td>
                                    <td class="text-center"><span class="res-num">{{ $mark->marks_obtained ?? '-' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No declared result found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($marks->count() > 0)
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end pe-4">Total</th>
                                <th class="text-center"><span class="res-num">{{ number_format($totalMarks, 2) }}</span></th>
                                <th class="text-center">-</th>
                                <th class="text-center"><span class="res-num">{{ number_format($totalObtained, 2) }}</span></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            @if ($marks->count() > 0)
            <div class="res-total-box">
                <div class="res-total-grid">
                    <div class="res-total-item">
                        <div class="res-total-k">Percentage</div>
                        <div class="res-total-v">{{ number_format($overallPercentage, 2) }}%</div>
                    </div>
                    <div class="res-total-item">
                        <div class="res-total-k">Grade</div>
                        <div class="res-total-v">{{ $overallGrade }}</div>
                    </div>
                    <div class="res-total-item">
                        <div class="res-total-k">Result</div>
                        <div class="res-total-v">{{ $overallPass ? 'PASS' : 'FAIL' }}</div>
                    </div>
                </div>
                <div class="res-total-words">
                    Total Obtain Marks In Words: {{ $obtainedInWords }}
                </div>
                <div class="res-total-words text-center" style="margin-top:12px;">
                    {{ $overallPass ? 'CONGRATULATIONS!! You have passed this exam.' : 'Sorry! You have not cleared exam.' }}
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
@endsection

@include('results.partials.print-script')
