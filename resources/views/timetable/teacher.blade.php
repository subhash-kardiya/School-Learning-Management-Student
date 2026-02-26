@extends('layouts.admin')

@section('title', 'Teacher Timetable')

@section('content')
    <div class="container-fluid py-4 timetable-module-compact">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Teacher Timetable</h5>
                <p class="text-muted small mb-0">Your assigned schedule</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @php
                    $teacherDataRoute = session('role') === 'teacher' ? 'teacher.timetable.data' : 'timetable.teacher.data';
                @endphp
                @include('timetable.partials.grid', [
                    'dataUrl' => route($teacherDataRoute),
                    'filters' => [
                        'classes' => $classes ?? [],
                        'sections' => $sections ?? [],
                        'teachers' => $teachers ?? [],
                    ],
                    'config' => [
                        'title' => 'My Timetable',
                        'showClass' => true,
                        'showSection' => true,
                        'showTeacher' => true,
                        'showWeek' => true,
                        'showTodayWeek' => true,
                        'showExport' => true,
                        'enableDetails' => false,
                        'enableCellClick' => false,
                        'addPanelId' => null,
                        'defaultTeacherId' => $defaultTeacherId ?? null,
                        'currentTeacherId' => $defaultTeacherId ?? null,
                    ],
                ])
            </div>
        </div>
    </div>
@endsection
