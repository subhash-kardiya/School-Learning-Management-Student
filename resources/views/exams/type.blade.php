@extends('layouts.admin')

@section('title', 'Exam Types')

@section('content')
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Exam Types (Demo)</h5>
                <button class="btn btn-primary-fancy">
                    <i class="fa fa-plus me-2"></i> Add Type
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Term</th>
                                <th>Weightage</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Mid-Term</td>
                                <td>Term 1</td>
                                <td>40%</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td>Final</td>
                                <td>Term 2</td>
                                <td>60%</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
