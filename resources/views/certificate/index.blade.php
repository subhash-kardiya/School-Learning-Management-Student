@extends('layouts.admin')

@section('title', 'Certificate')

@section('content')
    <div class="container-fluid py-4 certificate-modern certificate-module-compact">
        <div class="hero-panel mb-4">
            <div>
                <h3 class="mb-1">Certificates</h3>
                <p class="mb-0 text-muted">Manage and print certificates</p>
            </div>

        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3">{{ session('error') }}</div>
        @endif

        <div class="card glass-card">
            <div class="card-header">
                <strong>Certificate Requests</strong>
            </div>
            <div class="px-3 py-2 border-bottom">
                <div class="d-flex justify-content-end align-items-center gap-2 flex-nowrap"
                    style="overflow-x:auto; white-space:nowrap;">
                    <span class="text-muted small mb-0">Filter Status</span>
                    <select id="certificateFilterStatus" class="form-select form-select-sm"
                        style="min-width: 150px; width:150px;">
                        <option value="pending" {{ ($statusFilter ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="approved" {{ ($statusFilter ?? '') === 'approved' ? 'selected' : '' }}>Approved
                        </option>
                        <option value="rejected" {{ ($statusFilter ?? '') === 'rejected' ? 'selected' : '' }}>Rejected
                        </option>
                        <option value="" {{ ($statusFilter ?? '') === '' ? 'selected' : '' }}>All</option>
                    </select>
                    <span class="text-muted small mb-0">Filter Class</span>
                    <select id="certificateFilterClass" class="form-select form-select-sm"
                        style="min-width: 170px; width:170px;">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <select id="certificateFilterSection" class="form-select form-select-sm"
                        style="min-width: 170px; width:170px;">
                        <option value="">All Sections</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0 certificate-request-table" id="certificateTable">
                        <thead>
                            <tr>
                                <th>Request ID</th>

                                <th>Student</th>
                                <th>Certificate</th>
                                <th>Status</th>
                                <th>Requested On</th>

                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($certificates as $c)
                                <tr data-class-id="{{ $c->student?->class_id }}"
                                    data-section-id="{{ $c->student?->section_id }}">
                                    <td>{{ $c->id }}</td>

                                    <td>
                                        <div class="fw-semibold">{{ $c->student?->student_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            Roll: {{ $c->student?->roll_no ?? 'N/A' }}
                                            | Class: {{ $c->student?->class?->name ?? 'N/A' }}
                                            | Sec: {{ $c->student?->section?->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ ucfirst($c->certificate_type) }}</div>
                                        @if (!empty($c->reason))
                                            <div class="small text-muted">Reason: {{ $c->reason }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge
                                            {{ $c->status === 'approved' ? 'bg-success' : '' }}
                                            {{ $c->status === 'pending' ? 'bg-warning text-dark' : '' }}
                                            {{ $c->status === 'rejected' ? 'bg-danger' : '' }}">
                                            {{ ucfirst($c->status) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($c->created_at)->format('d-m-Y') ?? '-' }}</td>

                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                            <a href="{{ route('certificate.show', $c->id) }}"
                                                class="btn btn-sm btn-soft">View</a>
                                            @if (($canApprove ?? false) && $c->status === 'pending')
                                                <form action="{{ route('certificate.approve', $c->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <form action="{{ route('certificate.reject', $c->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No certificates yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
    <script>
        (function() {
            $('#certificateFilterStatus').on('change', function() {
                const url = new URL(window.location.href);
                const value = this.value || '';
                if (value) {
                    url.searchParams.set('status', value);
                } else {
                    url.searchParams.delete('status');
                }
                window.location.href = url.toString();
            });

            const classSections = @json(
                $classes->mapWithKeys(function ($class) {
                    return [
                        $class->id => $class->sections->map(function ($section) {
                                return ['id' => $section->id, 'name' => $section->name];
                            })->values(),
                    ];
                }));

            function renderSectionFilter(classId) {
                const $section = $('#certificateFilterSection');
                const selected = $section.val();
                $section.empty().append('<option value="">All Sections</option>');
                if (!classId || !classSections[classId]) return;

                classSections[classId].forEach(function(item) {
                    $section.append(`<option value="${item.id}">${item.name}</option>`);
                });

                if (selected && $section.find(`option[value="${selected}"]`).length) {
                    $section.val(selected);
                }
            }

            function applyCertificateFilters() {
                const classId = String($('#certificateFilterClass').val() || '');
                const sectionId = String($('#certificateFilterSection').val() || '');

                $('#certificateTable tbody tr').each(function() {
                    const $row = $(this);
                    const rowClass = String($row.data('class-id') || '');
                    const rowSection = String($row.data('section-id') || '');

                    const classMatch = !classId || rowClass === classId;
                    const sectionMatch = !sectionId || rowSection === sectionId;
                    $row.toggle(classMatch && sectionMatch);
                });
            }

            $('#certificateFilterClass').on('change', function() {
                renderSectionFilter(this.value);
                $('#certificateFilterSection').val('');
                applyCertificateFilters();
            });
            $('#certificateFilterSection').on('change', function() {
                applyCertificateFilters();
            });

        })();
    </script>
@endpush
