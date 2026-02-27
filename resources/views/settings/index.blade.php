@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="settings-year-wrap">
            <div class="settings-year-card">
                <div class="settings-year-top">
                    <div>
                        <p class="settings-label">System Setting</p>
                        <h4 class="mb-1">Academic Year Selection</h4>
                        <p class="text-muted mb-0">Selected year will be applied across the entire project.</p>
                    </div>
                    <i class="fas fa-calendar-alt settings-year-icon"></i>
                </div>

                <form method="POST" action="{{ route('context.filters.set') }}" class="row g-3 mt-1">
                    @csrf
                    <div class="col-lg-8">
                        <label class="form-label fw-semibold">Academic Year</label>
                        <select name="academic_year_id" class="form-select form-select-lg" required>
                            <option value="">Select Academic Year</option>
                            @foreach (($globalAcademicYears ?? collect()) as $year)
                                <option value="{{ $year->id }}"
                                    {{ (string) ($selectedAcademicYearId ?? '') === (string) $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Apply Year</button>
                        <button type="submit" formaction="{{ route('context.filters.clear') }}"
                            class="btn btn-outline-secondary btn-lg w-100">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .settings-year-wrap {
            max-width: 960px;
            margin: 0 auto;
        }
        .settings-year-card {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            padding: 24px;
        }
        .settings-year-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 8px;
        }
        .settings-label {
            margin-bottom: 6px;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #475569;
            font-weight: 700;
        }
        .settings-year-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: #1e40af;
            background: #dbeafe;
            font-size: 20px;
        }
    </style>
@endpush
