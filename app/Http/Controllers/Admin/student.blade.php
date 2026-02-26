@extends('layouts.admin')

@section('title', 'My Exam Results')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Exam Results</h5>
        </div>
        <div class="card-body">
            @if($marks->isEmpty())
                <div class="alert alert-info text-center">
                    No results have been declared yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Name</th>
                                <th>Subject</th>
                                <th>Total Marks</th>
                                <th>Marks Obtained</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($marks as $mark)
                                <tr>
                                    <td>{{ $mark->exam->name ?? '-' }}</td>
                                    <td>{{ $mark->subject->name ?? '-' }}</td>
                                    <td class="text-center">{{ $mark->exam->total_mark ?? '-' }}</td>
                                    <td class="text-center">{{ $mark->marks_obtained }}</td>
                                    <td class="text-center">
                                        @if($mark->exam && $mark->exam->total_mark > 0)
                                            {{ number_format(($mark->marks_obtained / $mark->exam->total_mark) * 100, 2) }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $mark->grade ?? '-' }}</td>
                                    <td>{{ $mark->remarks ?? ($mark->grade ? ($mark->grade . ' Grade') : '-') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any specific JS for this page if needed
</script>
@endpush
