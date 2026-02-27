<div class="row g-3 mb-3 attm-summary-wrap">

    <div class="col-md-3 col-6">
        <div class="card text-center shadow-sm attm-summary-card attm-summary-card--days">
            <div class="card-body attm-summary-body">
                <div class="attm-summary-icon"><i class="fas fa-calendar-alt"></i></div>
                <h6 class="text-muted">Total Days</h6>
                <h4 class="fw-bold attm-summary-value">{{ $daysInMonth }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card text-center shadow-sm border-success attm-summary-card attm-summary-card--present">
            <div class="card-body attm-summary-body">
                <div class="attm-summary-icon"><i class="fas fa-user-check"></i></div>
                <h6 class="text-muted">Present</h6>
                <h4 class="fw-bold text-success attm-summary-value">
                    {{ $counts['present'] ?? 0 }}
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card text-center shadow-sm border-danger attm-summary-card attm-summary-card--absent">
            <div class="card-body attm-summary-body">
                <div class="attm-summary-icon"><i class="fas fa-user-times"></i></div>
                <h6 class="text-muted">Absent</h6>
                <h4 class="fw-bold text-danger attm-summary-value">
                    {{ $counts['absent'] ?? 0 }}
                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card text-center shadow-sm border-primary attm-summary-card attm-summary-card--percent">
            <div class="card-body attm-summary-body">
                <div class="attm-summary-icon"><i class="fas fa-chart-line"></i></div>
                <h6 class="text-muted">Attendance %</h6>
                <h4 class="fw-bold text-primary attm-summary-value">
                    {{ $percent }}%
                </h4>
            </div>
        </div>
    </div>

</div>
