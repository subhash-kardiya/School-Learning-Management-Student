@extends('layouts.admin')

@section('title', 'Child Timetable')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Child Timetable</h5>
                <p class="text-muted small mb-0">View your child class timetable</p>
            </div>
        </div>

        @php
            $selectedStudentName = null;
            if (!empty($students) && !empty($selectedStudentId)) {
                $selectedStudentName = collect($students)->firstWhere('id', $selectedStudentId)?->student_name;
            }
        @endphp

        <div class="card">
            <div class="card-body">
                @include('timetable.partials.grid', [
                    'dataUrl' => route('parent.timetable.data'),
                    'filters' => [
                        'students' => $students ?? [],
                        'selectedStudentId' => $selectedStudentId ?? null,
                    ],
                    'config' => [
                        'title' => $selectedStudentName ? ($selectedStudentName . ' Timetable') : 'Child Timetable',
                        'showStudent' => true,
                        'showWeek' => false,
                        'showExport' => false,
                        'enableDetails' => false,
                        'enableCellClick' => false,
                        'addPanelId' => null,
                    ],
                ])
            </div>
        </div>
    </div>
@endsection
