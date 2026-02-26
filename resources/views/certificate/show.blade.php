@extends('layouts.admin')

@section('title', 'Certificate View')

@section('content')
    <div class="container-fluid py-4 certificate-modern certificate-module-compact">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 no-print">
            <div>
                <h4 class="mb-1">Certificate</h4>
                <p class="text-muted small mb-0">Preview and print</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('certificate.index') }}" class="btn btn-soft">Back</a>
                @if ($certificate->status === 'approved')
                    <button class="btn btn-soft" onclick="window.print()">Print</button>
                @endif
                @if (($canApprove ?? false) && $certificate->status === 'pending')
                    <form action="{{ route('certificate.approve', $certificate->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">Approve</button>
                    </form>
                    <form action="{{ route('certificate.reject', $certificate->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-body p-4">
                <div class="preview-head">
                    <div class="crest">subhash</div>
                    <div>
                        <div class="school-title">School LMS</div>
                        <div class="school-sub">Official Certificate</div>
                    </div>
                    <div class="chip">{{ ucfirst($certificate->certificate_type) }}</div>
                </div>

                <div class="certificate-sheet premium">
                    @php
                        $studentName = $certificate->student?->student_name ?? 'N/A';
                        $rollNo = $certificate->student?->roll_no ?? 'N/A';
                        $className = $certificate->student?->class?->name ?? 'N/A';
                        $sectionName = $certificate->student?->section?->name ?? 'N/A';
                        $yearName = $certificate->academicYear?->name ?? 'N/A';
                        $issueDateText = $certificate->issue_date
                            ? \Illuminate\Support\Carbon::parse($certificate->issue_date)->format('d F Y')
                            : '-';
                        $refNo = $certificate->certificate_no ?? 'Pending';
                        $isBonafide = $certificate->certificate_type === 'bonafide';
                        $fatherName =
                            data_get($certificate, 'student.parent.parent_name') ??
                            (data_get($certificate, 'student.father_name') ??
                                (data_get($certificate, 'student.father') ?? 'N/A'));
                        $dobText =
                            data_get($certificate, 'student.date_of_birth') ??
                            (data_get($certificate, 'student.dob') ?? 'N/A');
                        $admissionDateText = data_get($certificate, 'student.admission_date')
                            ? \Illuminate\Support\Carbon::parse(
                                data_get($certificate, 'student.admission_date'),
                            )->format('d-m-Y')
                            : ($certificate->student?->created_at
                                ? $certificate->student->created_at->format('d-m-Y')
                                : 'N/A');
                        $applicationDateText = $certificate->created_at
                            ? $certificate->created_at->format('d-m-Y')
                            : 'N/A';
                        $nextClassText =
                            $certificate->resolved_next_class ??
                            (data_get($certificate, 'next_class') ??
                                (data_get($certificate, 'promoted_to_class') ?? 'Not Applicable'));
                        $workingDaysPresentText =
                            $certificate->resolved_working_days_present ??
                            (data_get($certificate, 'working_days_present') ??
                                (data_get($certificate, 'days_present') ?? '0'));
                        $conductText = $certificate->resolved_conduct ?? ($certificate->conduct ?? 'Good');
                        $purposeText = $certificate->reason ?? ($certificate->remarks ?? 'Official Purpose');
                        $classTeacherName =
                            data_get($certificate, 'student.class.teacher.name') ??
                            (data_get($certificate, 'student.class.class_teacher_name') ?? 'Class Teacher');
                        $principalName = (string) config('app.certificate_principal_name', 'Principal');
                        $principalTitle = (string) config('app.certificate_principal_title', 'Principal');
                    @endphp
                    @if (!$isBonafide)
                        <div class="leaving-doc">
                            <div class="leaving-topbar">
                                <div><strong>Admission No:</strong> {{ $rollNo }}</div>
                                <div class="leaving-top-title">SCHOOL LEAVING CERTIFICATE</div>
                                <div><strong>Certificate No:</strong> {{ $refNo }}</div>
                            </div>
                            <table class="leaving-form-table">
                                <tr>
                                    <td>1. Scholar's Name</td>
                                    <td>{{ strtoupper($studentName) }}</td>
                                </tr>
                                <tr>
                                    <td>2. Father's / Guardian Name</td>
                                    <td>{{ strtoupper($fatherName) }}</td>
                                </tr>
                                <tr>
                                    <td>3. Nationality</td>
                                    <td>INDIAN</td>
                                </tr>
                                <tr>
                                    <td>4. Date of First Admission in the School</td>
                                    <td>{{ $admissionDateText }}</td>
                                </tr>
                                <tr>
                                    <td>5. Date of Birth (as per admission register)</td>
                                    <td>{{ $dobText }}</td>
                                </tr>
                                <tr>
                                    <td>6. Class in which the pupil last studied (in words)</td>
                                    <td>{{ $className }} {{ $sectionName }}</td>
                                </tr>
                                <tr>
                                    <td>7. School/Board Annual Exam last taken with Result</td>
                                    <td>Class Passed</td>
                                </tr>
                                <tr>
                                    <td>8. Whether failed, if so once/twice in same class</td>
                                    <td>NO</td>
                                </tr>
                                <tr>
                                    <td>9. Subjects Studied</td>
                                    <td>English, Mathematics, Science, Social Science, Computer</td>
                                </tr>
                                <tr>
                                    <td>10. Whether qualified for promotion to higher class</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <td>11. If so, to which class (in words)</td>
                                    <td>{{ $nextClassText }}</td>
                                </tr>
                                <tr>
                                    <td>12. Total no. of working days present</td>
                                    <td>{{ $workingDaysPresentText }}</td>
                                </tr>
                                <tr>
                                    <td>13. General Conduct</td>
                                    <td>{{ $conductText }}</td>
                                </tr>
                                <tr>
                                    <td>14. Date of application for certificate</td>
                                    <td>{{ $applicationDateText }}</td>
                                </tr>
                                <tr>
                                    <td>15. Date of issue of certificate</td>
                                    <td>{{ $issueDateText }}</td>
                                </tr>
                                <tr>
                                    <td>16. Reason for leaving school</td>
                                    <td>{{ strtoupper($certificate->reason ?? 'OWN REQUEST') }}</td>
                                </tr>
                                <tr>
                                    <td>17. Any other remarks</td>
                                    <td>{{ strtoupper($certificate->remarks ?? 'NIL') }}</td>
                                </tr>
                            </table>
                            <div class="leaving-signatures">
                                <div class="sign-col principal-col">
                                    <div class="sign-line"></div>
                                    <div class="sign-role">{{ $principalTitle }}</div>
                                </div>
                                <div class="sign-col teacher-col">
                                    <div class="sign-line"></div>
                                    <div class="sign-role">Class Teacher</div>
                                </div>

                            </div>
                        </div>
                    @else
                        <div class="bonafide-doc bonafide-template">

                            <div class="bf-title-wrap">
                                <h2 class="bf-title">Bonafide Certificate</h2>
                            </div>

                            <div class="bf-meta-row">
                                <div>Date : {{ $issueDateText }}</div>
                                <div>Certificate No : {{ $refNo }}</div>
                            </div>
                            <hr>

                            <p class="bf-line">
                                This is to certify that <strong>{{ $studentName }}</strong>, roll number
                                <strong>{{ $rollNo }}</strong>, is a bonafide student of
                                <strong>{{ $className }} {{ $sectionName }}</strong>, studying in
                                <strong>{{ $yearName }}</strong>.
                            </p>

                            <p class="bf-line">
                                As per school admission records, the student is regularly enrolled and attending academic
                                sessions conducted by the institution.
                            </p>
                            <p class="bf-line">
                                During the above-mentioned period, the student's conduct and discipline in school have been
                                found satisfactory.
                            </p>
                            <p class="bf-line">
                                This certificate is issued on the request of the student/guardian for
                                <strong>{{ $purposeText }}</strong>.
                            </p>
                            <p class="bf-line">
                                This certificate is valid only with the signature and seal of the issuing authority of the
                                school.
                            </p>

                            <div class="bf-sign-row">
                                <div class="">

                                </div>
                                <div class="bf-signature">
                                    ..............................
                                    <div>Signature</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($certificate->remarks))
                        <p class="lead doc-body">Remarks: <strong>{{ $certificate->remarks }}</strong></p>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/certificate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resize/certificate-compact.css') }}">
@endpush

@push('scripts')
    @if (request('print') && $certificate->status === 'approved')
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
@endpush
