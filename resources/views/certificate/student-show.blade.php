@extends('layouts.admin')

@section('title', 'Certificate View')

@section('content')
    <div class="container-fluid py-4 certificate-modern">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 no-print">
            <div>
                <h4 class="mb-1">Certificate</h4>
                <p class="text-muted small mb-0">Preview and print</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('student.certificate.index') }}" class="btn btn-soft">Back</a>
                <button class="btn btn-modern" onclick="window.print()">Print</button>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-body p-4">
                <div class="preview-head">
                    <div class="crest">SL</div>
                    <div>
                        <div class="school-title">School LMS</div>
                        <div class="school-sub">Official Certificate</div>
                    </div>
                    <div class="chip">{{ $certificate->certificate_type === 'leaving' ? 'Leaving' : 'Bonafide' }}</div>
                </div>

                <div class="certificate-sheet premium">
                    <h2 class="text-center mb-2">
                        {{ $certificate->certificate_type === 'bonafide' ? 'Bonafide Certificate' : 'Leaving Certificate' }}
                    </h2>
                    <p class="text-center text-muted mb-4">Generated on {{ $certificate->issue_date }}</p>

                    <p class="lead">
                        This is to certify that <strong>{{ $certificate->student?->student_name }}</strong>,
                        Roll No <strong>{{ $certificate->student?->roll_no ?? 'N/A' }}</strong>,
                        studying in <strong>{{ $certificate->student?->class?->name ?? 'N/A' }}</strong>
                        (Section <strong>{{ $certificate->student?->section?->name ?? 'N/A' }}</strong>),
                        Academic Year <strong>{{ $certificate->academicYear?->name ?? 'N/A' }}</strong>,
                        has been a bonafide student of this institution.
                    </p>

                    @if ($certificate->certificate_type === 'leaving')
                        <p class="lead">
                            Reason for leaving: <strong>{{ $certificate->reason ?? 'N/A' }}</strong>.
                            Conduct: <strong>{{ $certificate->conduct ?? 'N/A' }}</strong>.
                        </p>
                    @endif

                    @if (!empty($certificate->remarks))
                        <p class="lead">Remarks: <strong>{{ $certificate->remarks }}</strong></p>
                    @endif

                    <div class="sign-row">
                        <div class="sign-box">
                            <div class="sign-line"></div>
                            <div class="fw-bold">Class Teacher</div>
                        </div>
                        <div class="sign-box">
                            <div class="sign-line"></div>
                            <div class="fw-bold">Principal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/certificate.css') }}">
@endpush

@push('scripts')
    @if (request('print'))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
@endpush
