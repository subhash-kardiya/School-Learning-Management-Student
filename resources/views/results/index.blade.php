@extends('layouts.admin')

@section('title', 'Results')

@section('content')
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Results (Demo)</h5>
                <button class="btn btn-primary-fancy">
                    <i class="fa fa-download me-2"></i> Export
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Term</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Aarav Patel</td>
                                <td>10-A</td>
                                <td>Term 1</td>
                                <td>86%</td>
                                <td>A</td>
                            </tr>
                            <tr>
                                <td>Riya Shah</td>
                                <td>10-A</td>
                                <td>Term 1</td>
                                <td>78%</td>
                                <td>B+</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
