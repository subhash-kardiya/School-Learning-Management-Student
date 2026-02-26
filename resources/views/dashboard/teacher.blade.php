@extends('layouts.admin')

@section('title', 'Teacher Dashboard')

@section('content')
    <div id="dashboard-content">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">My Students</h6>
                            <h3 class="fw-bold mb-0">120</h3>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Assigned Classes</h6>
                            <h3 class="fw-bold mb-0">5</h3>
                        </div>
                        <div class="icon-box bg-info bg-opacity-10 text-info">
                            <i class="fas fa-chalkboard fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Today's Lectures</h6>
                            <h3 class="fw-bold mb-0">4</h3>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Attendance Status</h5>
                        @permission('attendance_mark')
                            <a href="javascript:void(0)" class="btn btn-sm btn-primary rounded-pill">Mark Attendance</a>
                        @endpermission
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Class 10</td>
                                        <td>A</td>
                                        <td>45</td>
                                        <td><span class="badge bg-success">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>Class 9</td>
                                        <td>B</td>
                                        <td>38</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Recent Notices</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-primary border-0 mb-3" role="alert">
                            <div class="d-flex">
                                <i class="fas fa-info-circle mt-1 me-2"></i>
                                <div><strong>Meeting:</strong><br>Staff meeting at 2 PM.</div>
                            </div>
                        </div>
                        <div class="alert alert-light border mb-3" role="alert">
                            <div class="d-flex">
                                <i class="fas fa-bullhorn text-warning mt-1 me-2"></i>
                                <div><strong>Exam:</strong><br>Mid-term paper submission deadline.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
