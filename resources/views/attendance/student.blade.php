@extends('layouts.admin')

@section('title', 'My Attendance')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/attendance-compact.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4 attendance-monthly-wrap att-view--student"
        data-grid-url="{{ route('student.attendance.grid') }}">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">My Attendance</h4>
                <div class="att-filter-note">Monthly view (read-only)</div>

            </div>

        </div>

        <div id="attm-summary">
            @include('attendance.partials.monthly-summary', [
                'daysInMonth' => count($monthlyData['days']),
                'counts' => $monthlyData['counts'],
                'percent' => $percent,
            ])
        </div>

        <div class="attm-filter-bar mb-3">
            <div>
                <label class="form-label mb-1">Month</label>
                <select class="form-select" id="attm-month">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                            {{ \Illuminate\Support\Carbon::createFromDate(null, $m, 1)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" id="attm-year" value="{{ $year }}">
            <div class="attm-filter-action">
                <button type="button" class="attm-search-btn w-100" id="attm-search">Search</button>
            </div>
        </div>

        <div id="attm-grid">
            @include('attendance.partials.monthly-grid', [
                'students' => collect([$student]),
                'days' => $monthlyData['days'],
                'attendanceMap' => $monthlyData['attendanceMap'],
                'editableDate' => \Illuminate\Support\Carbon::today()->toDateString(),
                'canEdit' => false,
            ])
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const wrapper = document.querySelector('.attendance-monthly-wrap');
            if (!wrapper) return;

            const gridUrl = wrapper.getAttribute('data-grid-url');
            const gridEl = document.getElementById('attm-grid');
            const summaryEl = document.getElementById('attm-summary');
            const monthSelect = document.getElementById('attm-month');
            const yearSelect = document.getElementById('attm-year');
            const searchBtn = document.getElementById('attm-search');

            const setLoading = () => {
                if (!gridEl) return;
                gridEl.innerHTML = `
                    <div class="attm-grid-wrap">
                        <div class="p-3">
                            <div class="attm-skeleton mb-2"></div>
                            <div class="attm-skeleton mb-2"></div>
                            <div class="attm-skeleton"></div>
                        </div>
                    </div>
                `;
            };

            const loadGrid = () => {
                if (!gridUrl) return;
                setLoading();
                const params = new URLSearchParams({
                    month: monthSelect?.value || '',
                    year: yearSelect?.value || '',
                });
                fetch(`${gridUrl}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (summaryEl && data.summary) summaryEl.innerHTML = data.summary;
                        if (gridEl && data.html) gridEl.innerHTML = data.html;
                    })
                    .catch(() => {
                        if (gridEl) gridEl.innerHTML =
                            '<div class="attm-empty">Unable to load attendance.</div>';
                    });
            };

            searchBtn?.addEventListener('click', loadGrid);
        })();
    </script>
@endpush
