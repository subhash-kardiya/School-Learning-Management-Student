@extends('layouts.admin')

@section('title', 'Create Homework')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/resize/homework-compact.css') }}">
@endpush

@section('content')
    @php
        $activeYearId = old('academic_year_id', $selectedAcademicYearId ?? '');
        $selectedYearName = $selectedYearName ?? '';
    @endphp
    <div class="container-fluid py-4 hw-page">
        <div class="card hw-card">
            <div class="hw-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1 text-white">Create Homework</h5>
                    <p class="mb-0 text-white-50 small">Draft save karo athva direct publish karo.</p>
                </div>
                <span class="badge bg-light text-primary fw-semibold px-3 py-2">Teacher Panel</span>
            </div>
            <div class="card-body p-4 hw-form">
                @if ($errors->any())
                    <div class="alert alert-danger">Please fix validation errors.</div>
                @endif

                <form action="{{ route('teacher.homework.store') }}" method="POST">
                    @csrf
                    <div class="hw-step mb-3">
                        <div class="hw-step-title"><span class="hw-step-badge">1</span>Academic Details</div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" class="form-control" value="{{ $selectedYearName }}" readonly>
                                <input type="hidden" name="academic_year_id" value="{{ $activeYearId }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Class</label>
                                <select name="class_id" id="class_id" class="form-select" required>
                                    <option value="">Select Class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}"
                                            {{ (string) old('class_id') === (string) $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Section</label>
                                <select name="section_id" id="section_id" class="form-select" required disabled>
                                    <option value="">Select Section</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" data-class-id="{{ $section->class_id }}"
                                            {{ (string) old('section_id') === (string) $section->id ? 'selected' : '' }}>
                                            {{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" id="subject_id" class="form-select" required disabled>
                                    <option value="">Select Subject</option>
                                    @foreach ($mappedSubjectOptions as $option)
                                        <option value="{{ $option['subject_id'] }}" data-class-id="{{ $option['class_id'] }}"
                                            data-section-id="{{ $option['section_id'] }}"
                                            {{ (string) old('subject_id') === (string) $option['subject_id'] ? 'selected' : '' }}>
                                            {{ $option['subject_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="hw-step mb-3">
                        <div class="hw-step-title"><span class="hw-step-badge">2</span>Homework Details</div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}"
                                    required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="hw-step">
                        <div class="hw-step-title"><span class="hw-step-badge">3</span>Action</div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" name="action_type" value="draft"
                                class="btn btn-outline-primary px-4">Save Draft</button>
                            <button type="submit" name="action_type" value="publish" class="btn btn-primary px-4">Publish
                                Homework</button>
                            <a href="{{ route('teacher.homework.list') }}" class="btn btn-light border px-4">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const classSelect = document.getElementById('class_id');
            const sectionSelect = document.getElementById('section_id');
            const subjectSelect = document.getElementById('subject_id');
            if (!classSelect || !sectionSelect || !subjectSelect) return;

            function filterSectionsByClass() {
                const cid = classSelect.value;
                sectionSelect.disabled = !cid;
                let visibleCount = 0;
                let firstVisibleValue = '';
                Array.from(sectionSelect.options).forEach((opt, idx) => {
                    if (idx === 0) return;
                    const optClass = opt.getAttribute('data-class-id');
                    const show = !!cid && String(optClass) === String(cid);
                    opt.hidden = !show;
                    if (show) {
                        visibleCount += 1;
                        if (!firstVisibleValue) firstVisibleValue = opt.value;
                    }
                });

                const selected = sectionSelect.value;
                const selectedOption = selected ? sectionSelect.querySelector('option[value="' + selected + '"]') : null;
                if (!selectedOption || selectedOption.hidden) {
                    sectionSelect.value = visibleCount === 1 ? firstVisibleValue : '';
                }
            }

            function filterSubjectsByClassAndSection(forceAutoSelect = false) {
                const cid = classSelect.value;
                const sid = sectionSelect.value;
                subjectSelect.disabled = !(cid && sid);
                let visibleCount = 0;
                let firstVisibleValue = '';

                Array.from(subjectSelect.options).forEach((opt, idx) => {
                    if (idx === 0) return;
                    const optClass = opt.getAttribute('data-class-id');
                    const optSection = opt.getAttribute('data-section-id');
                    const show = !!cid && !!sid && String(optClass) === String(cid) && String(optSection) === String(sid);
                    opt.hidden = !show;
                    if (show) {
                        visibleCount += 1;
                        if (!firstVisibleValue) firstVisibleValue = opt.value;
                    }
                });

                const selected = subjectSelect.value;
                const selectedOption = selected ? subjectSelect.querySelector('option[value="' + selected + '"]') : null;
                const selectedInvalid = !selectedOption || selectedOption.hidden;

                if (sid && (forceAutoSelect || selectedInvalid)) {
                    subjectSelect.value = visibleCount > 0 ? firstVisibleValue : '';
                } else if (!sid) {
                    subjectSelect.value = '';
                }
            }

            classSelect.addEventListener('change', function() {
                filterSectionsByClass();
                filterSubjectsByClassAndSection(true);
            });

            sectionSelect.addEventListener('change', function() {
                filterSubjectsByClassAndSection(true);
            });

            filterSectionsByClass();
            filterSubjectsByClassAndSection(true);
        })();
    </script>
@endpush
