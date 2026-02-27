@extends('layouts.admin')

@section('title', 'My Timetable')

@section('content')
    <div class="container-fluid py-4">
        

        <div class="card">
            <div class="card-body">
                @include('timetable.partials.grid', [
                    'dataUrl' => route('student.timetable.data'),
                    'filters' => [],
                    'config' => [
                        'title' => 'My Timetable',
                        'showWeek' => false,
                        'showTodayWeek' => false,
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
