@extends('layouts.admin')

@section('title', 'Exam Schedule')

@section('content')
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Exam Schedule (Demo)</h5>
                <button class="btn btn-primary-fancy">
                    <i class="fa fa-calendar-plus me-2"></i> Create Schedule
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Time</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2026-02-15</td>
                                <td>10-A</td>
                                <td>Mathematics</td>
                                <td>09:00 - 12:00</td>
                                <td>Hall 1</td>
                            </tr>
                            <tr>
                                <td>2026-02-17</td>
                                <td>10-A</td>
                                <td>Science</td>
                                <td>09:00 - 12:00</td>
                                <td>Hall 2</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
