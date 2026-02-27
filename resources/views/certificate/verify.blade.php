<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate Verification</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f6ff; color: #111827; margin: 0; padding: 24px; }
        .card { max-width: 760px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08); }
        .title { margin: 0 0 16px; }
        .ok { background: #dcfce7; color: #166534; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
        .bad { background: #fee2e2; color: #991b1b; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
        .row { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .row:last-child { border-bottom: 0; }
        .label { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
        .value { font-weight: 600; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="title">Certificate Verification</h2>
        @if ($certificate)
            <div class="ok">Certificate is valid.</div>
            <div class="row"><div class="label">Certificate No</div><div class="value">{{ $certificate->certificate_no }}</div></div>
            <div class="row"><div class="label">Student</div><div class="value">{{ $certificate->student?->student_name ?? 'N/A' }}</div></div>
            <div class="row"><div class="label">Type</div><div class="value">{{ ucfirst($certificate->certificate_type) }}</div></div>
            <div class="row"><div class="label">Status</div><div class="value">{{ ucfirst($certificate->status) }}</div></div>
            <div class="row"><div class="label">Issue Date</div><div class="value">{{ $certificate->issue_date }}</div></div>
            <div class="row"><div class="label">Academic Year</div><div class="value">{{ $certificate->academicYear?->name ?? 'N/A' }}</div></div>
        @else
            <div class="bad">Invalid certificate number: <strong>{{ $certificateNo }}</strong></div>
        @endif
    </div>
</body>
</html>
