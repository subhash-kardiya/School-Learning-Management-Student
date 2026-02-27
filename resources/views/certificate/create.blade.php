@extends('layouts.admin')

@section('title', 'Create Certificate')

@section('content')
    <div class="container-fluid py-4 certificate-modern certificate-module-compact">
        <div class="mb-3">
            <a href="{{ route('certificate.index') }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Certificates
            </a>
        </div>

        <div class="card glass-card">
            <div class="card-header">
                <strong>Create Certificate</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('certificate.store') }}" method="POST" class="row g-3" id="certificateCreateFormEl">
                    @csrf
                    @php
                        $activeYearStart = optional($activeYear)->start_date ? \Illuminate\Support\Carbon::parse($activeYear->start_date) : null;
                        $activeYearEnd = optional($activeYear)->end_date ? \Illuminate\Support\Carbon::parse($activeYear->end_date) : null;
                    @endphp
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm">
                            Please fix the highlighted fields and try again.
                        </div>
                    @endif
                    @if ($activeYearStart && $activeYearEnd)
                        <div class="col-md-12">
                            <div class="alert alert-info border-0 mb-0">
                                <strong>Active Academic Year:</strong>
                                {{ $activeYear->name ?? 'N/A' }}
                                ({{ $activeYearStart->format('d-m-Y') }} to {{ $activeYearEnd->format('d-m-Y') }})
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror">
                            <option value="">Select Student</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->student_name }} ({{ $s->roll_no ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Certificate Type</label>
                        <select name="certificate_type" class="form-select @error('certificate_type') is-invalid @enderror">
                            <option value="">Select Type</option>
                            <option value="bonafide" {{ old('certificate_type') == 'bonafide' ? 'selected' : '' }}>Bonafide</option>
                            <option value="leaving" {{ old('certificate_type') == 'leaving' ? 'selected' : '' }}>Leaving</option>
                        </select>
                        @error('certificate_type')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-info border-0 mb-0">
                            Request will be saved as pending. Issue date and certificate number are generated on approval.
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="leavingFieldsPanel" class="certificate-leaving-panel">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Reason (Leaving only)</label>
                                    <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror"
                                        placeholder="Example: Transfer to another school"
                                        value="{{ old('reason') }}">
                                    @error('reason')
                                        <span class="text-danger d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Conduct</label>
                                    <input type="text" name="conduct" class="form-control @error('conduct') is-invalid @enderror"
                                        placeholder="Example: Good"
                                        value="{{ old('conduct') }}">
                                    @error('conduct')
                                        <span class="text-danger d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control @error('remarks') is-invalid @enderror" value="{{ old('remarks') }}">
                        @error('remarks')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <input type="hidden" name="status" value="pending">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-modern px-4">Create Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/certificate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/resize/certificate-compact.css') }}">
@endpush

@push('scripts')
<script>
    (function() {
        function toggleLeavingFields() {
            const type = $('[name="certificate_type"]').val();
            const isLeaving = type === 'leaving';
            const $panel = $('#leavingFieldsPanel');
            const $reason = $('[name="reason"]');
            const $conduct = $('[name="conduct"]');

            $panel.toggleClass('is-active', isLeaving);
            $reason.prop('required', isLeaving);
            $conduct.prop('required', isLeaving);

            if (!isLeaving) {
                $reason.removeClass('is-invalid');
                $conduct.removeClass('is-invalid');
                $reason.closest('.col-md-8').find('.client-error').remove();
                $conduct.closest('.col-md-4').find('.client-error').remove();
            }
        }

        toggleLeavingFields();
        $('[name="certificate_type"]').on('change', toggleLeavingFields);

        $('#certificateCreateFormEl').on('submit', function(e) {
            $('.client-error').remove();
            let hasError = false;
            let requiredFields = ['student_id', 'certificate_type'];
            if ($('[name="certificate_type"]').val() === 'leaving') {
                requiredFields = requiredFields.concat(['reason', 'conduct']);
            }
            requiredFields.forEach(function(name) {
                const $field = $('[name="' + name + '"]');
                if (!$field.length) return;
                if (String($field.val() || '').trim() === '') {
                    hasError = true;
                    $field.addClass('is-invalid');
                    $field.after('<span class="text-danger d-block client-error">This field is required.</span>');
                } else {
                    $field.removeClass('is-invalid');
                }
            });
            if (hasError) {
                e.preventDefault();
                return;
            }

        });
    })();
</script>
@endpush
