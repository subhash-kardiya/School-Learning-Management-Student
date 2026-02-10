@extends('layouts.admin')

@section('title', 'Marks Entry')

@section('content')
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Marks Entry (Demo)</h5>
                <button class="btn btn-primary-fancy">
                    <i class="fa fa-save me-2"></i> Save
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Aarav Patel</td>
                                <td>10-A</td>
                                <td>Mathematics</td>
                                <td>88</td>
                                <td>A</td>
                            </tr>
                            <tr>
                                <td>Riya Shah</td>
                                <td>10-A</td>
                                <td>Mathematics</td>
                                <td>76</td>
                                <td>B+</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
