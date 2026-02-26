<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Certificate;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    private function currentActorId(): int
    {
        $sessionId = (int) (session('auth_id') ?? 0);
        if ($sessionId > 0) {
            return $sessionId;
        }

        return (int) (Auth::id() ?? 0);
    }

    private function currentStudentId(): int
    {
        $user = Auth::user();
        if ($user && isset($user->id)) {
            return (int) $user->id;
        }

        return (int) (session('auth_id') ?? 0);
    }

    private function hasAnyRole(array $roles): bool
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasRole')) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    private function isSuperAdminUser(): bool
    {
        return $this->hasAnyRole(['superadmin', 'super admin', 'super_admin', 'SuperAdmin', 'Super Admin']);
    }

    private function isAdminOrSuperAdminUser(): bool
    {
        return $this->isSuperAdminUser() || $this->hasAnyRole(['admin', 'Admin']);
    }

    private function canViewCertificate(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($this->isAdminOrSuperAdminUser()) {
            return true;
        }
        return $this->canPermission('certificate_view') || $this->canPermission('certificate_manage');
    }

    private function canManageCertificate(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($this->isAdminOrSuperAdminUser()) {
            return true;
        }
        return $this->canPermission('certificate_manage');
    }

    private function canApproveCertificate(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($this->isSuperAdminUser()) {
            return true;
        }
        return $this->hasAnyRole(['admin', 'Admin']);
    }

    private function isStudentUser(): bool
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('student')) {
            return true;
        }

        return strtolower((string) session('role')) === 'student' && $this->currentStudentId() > 0;
    }

    private function nextCertificateNumber(string $year): string
    {
        $prefix = 'CERT-' . $year . '-';
        $last = Certificate::query()
            ->where('certificate_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('certificate_no');

        $seq = 1;
        if (is_string($last) && Str::startsWith($last, $prefix)) {
            $tail = (int) Str::after($last, $prefix);
            $seq = $tail + 1;
        }

        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    private function activeAcademicYear(): ?AcademicYear
    {
        return AcademicYear::query()
            ->where('is_active', 1)
            ->where('is_locked', 0)
            ->first();
    }

    private function activeAcademicYearId(): ?int
    {
        return optional($this->activeAcademicYear())->id;
    }

    private function resolveConduct(?string $conduct): string
    {
        $value = trim((string) $conduct);
        if ($value === '' || strcasecmp($value, 'n/a') === 0 || strcasecmp($value, 'na') === 0 || $value === '-') {
            return 'Good';
        }
        return $value;
    }

    private function resolveWorkingDaysPresent(Certificate $certificate): string
    {
        $explicit = data_get($certificate, 'working_days_present') ?? data_get($certificate, 'days_present');
        $explicitText = trim((string) $explicit);
        if ($explicitText !== '' && strcasecmp($explicitText, 'n/a') !== 0 && strcasecmp($explicitText, 'na') !== 0) {
            return $explicitText;
        }

        $query = Attendance::query()
            ->where('student_id', $certificate->student_id)
            ->where('status', 'present');

        $startDate = optional($certificate->academicYear)->start_date;
        $endDate = optional($certificate->academicYear)->end_date;
        if (!empty($startDate)) {
            $query->whereDate('date', '>=', Carbon::parse($startDate)->toDateString());
        }
        if (!empty($endDate)) {
            $query->whereDate('date', '<=', Carbon::parse($endDate)->toDateString());
        }

        return (string) ((int) $query->count());
    }

    private function resolveNextClass(Certificate $certificate): string
    {
        $explicit = data_get($certificate, 'next_class') ?? data_get($certificate, 'promoted_to_class');
        $explicitText = trim((string) $explicit);
        if ($explicitText !== '' && strcasecmp($explicitText, 'n/a') !== 0 && strcasecmp($explicitText, 'na') !== 0) {
            return $explicitText;
        }

        $className = trim((string) data_get($certificate, 'student.class.name'));
        if ($className === '') {
            return 'Not Applicable';
        }

        if (preg_match('/(\d+)(?!.*\d)/', $className, $matches)) {
            $current = (int) $matches[1];
            $next = $current + 1;
            return preg_replace('/(\d+)(?!.*\d)/', (string) $next, $className) ?? ('Class ' . $next);
        }

        return $className . ' (Next)';
    }

    private function decorateCertificate(Certificate $certificate): Certificate
    {
        $certificate->setAttribute('resolved_working_days_present', $this->resolveWorkingDaysPresent($certificate));
        $certificate->setAttribute('resolved_conduct', $this->resolveConduct($certificate->conduct));
        $certificate->setAttribute('resolved_next_class', $this->resolveNextClass($certificate));
        return $certificate;
    }

    private function enforceActiveAcademicYearOrFail(): int
    {
        $yearId = (int) ($this->activeAcademicYearId() ?? 0);
        if ($yearId <= 0) {
            abort(422, 'No active academic year found.');
        }
        return $yearId;
    }

    private function canPermission(string $permission): bool
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }
        return $user->hasPermission($permission);
    }

    public function index()
    {
        if (!$this->canViewCertificate()) {
            abort(403, 'Unauthorized access');
        }

        $yearId = $this->enforceActiveAcademicYearOrFail();
        $classes = Classes::with(['sections' => function ($q) {
                $q->where('status', 1)->orderBy('name');
            }])
            ->where('academic_year_id', $yearId)
            ->where('status', 1)
            ->orderBy('name')
            ->get();
        $statusFilter = request()->input('status', 'pending');
        $certificates = Certificate::with(['student.class', 'student.section', 'student.parent', 'academicYear'])
            ->where('academic_year_id', $yearId)
            ->when(in_array($statusFilter, ['pending', 'approved', 'rejected'], true), function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->latest()
            ->get();
        $canApprove = $this->canApproveCertificate();
        return view('certificate.index', compact('certificates', 'classes', 'canApprove', 'statusFilter'));
    }

    public function create()
    {
        if (!$this->canManageCertificate()) {
            abort(403, 'Unauthorized access');
        }

        $activeYear = $this->activeAcademicYear();
        $yearId = (int) optional($activeYear)->id;
        if ($yearId <= 0) {
            abort(422, 'No active academic year found.');
        }
        $students = Student::with(['class', 'section', 'academicYear'])
            ->where('academic_year_id', $yearId)
            ->orderBy('student_name')
            ->get();
        $academicYears = AcademicYear::where('id', $yearId)->get();

        return view('certificate.create', compact('students', 'academicYears', 'activeYear'));
    }

    public function store(Request $request)
    {
        if (!$this->canManageCertificate()) {
            abort(403, 'Unauthorized access');
        }
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'certificate_type' => 'required|in:bonafide,leaving',
            'reason' => 'required_if:certificate_type,leaving|nullable|string|max:255',
            'conduct' => 'required_if:certificate_type,leaving|nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ], [
            'reason.required_if' => 'Reason is required for leaving certificate.',
            'conduct.required_if' => 'Conduct is required for leaving certificate.',
        ]);
        $activeYear = $this->activeAcademicYear();
        $activeYearId = (int) optional($activeYear)->id;
        if ($activeYearId <= 0 || !$activeYear) {
            abort(422, 'No active academic year found.');
        }
        $yearId = $activeYearId;
        $student = Student::findOrFail($request->student_id);
        if ((int) $student->academic_year_id !== $yearId) {
            return back()->withErrors(['student_id' => 'Selected student does not belong to academic year of selected issue date.'])->withInput();
        }
        $cert = Certificate::create([
            'student_id' => $request->student_id,
            'certificate_no' => null,
            'certificate_type' => $request->certificate_type,
            'class_id' => $student->class_id,
            'academic_year_id' => $yearId,
            'issue_date' => null,
            'reason' => $request->reason,
            'conduct' => $request->conduct,
            'remarks' => $request->remarks,
            'status' => 'pending',
            'approved_by' => null,
            'created_by' => $this->currentActorId(),
        ]);

        return redirect()->route('certificate.show', $cert->id)
            ->with('success', 'Certificate request created.');
    }

    public function show($id)
    {
        if (!$this->canViewCertificate()) {
            abort(403, 'Unauthorized access');
        }

        $certificate = Certificate::with(['student.class.teacher', 'student.section', 'student.parent', 'academicYear'])
            ->findOrFail($id);
        $certificate = $this->decorateCertificate($certificate);
        $canApprove = $this->canApproveCertificate();
        return view('certificate.show', compact('certificate', 'canApprove'));
    }

    public function studentIndex()
    {
        if (!$this->isStudentUser()) {
            abort(403, 'Unauthorized access');
        }
        $studentId = $this->currentStudentId();

        $certificates = Certificate::with(['academicYear'])
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('certificate.student', compact('certificates'));
    }

    public function studentCreate()
    {
        if (!$this->isStudentUser()) {
            abort(403, 'Unauthorized access');
        }
        $student = Student::findOrFail($this->currentStudentId());
        $activeYear = $this->activeAcademicYear();
        if (!$activeYear) {
            abort(422, 'No active academic year found.');
        }
        if ((int) ($student->academic_year_id ?? 0) !== (int) $activeYear->id) {
            abort(422, 'Student does not belong to active academic year.');
        }
        return view('certificate.student-request', compact('student', 'activeYear'));
    }

    public function studentStore(Request $request)
    {
        if (!$this->isStudentUser()) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'certificate_type' => 'required|in:bonafide,leaving',
            'reason' => 'required|string|max:255',
        ]);

        $student = Student::findOrFail($this->currentStudentId());
        $activeYear = $this->activeAcademicYear();
        $activeYearId = (int) optional($activeYear)->id;
        if ($activeYearId <= 0 || !$activeYear) {
            abort(422, 'No active academic year found.');
        }
        if ((int) ($student->academic_year_id ?? 0) !== $activeYearId) {
            return back()->withErrors(['certificate_type' => 'You are not mapped to active academic year.'])->withInput();
        }

        Certificate::create([
            'student_id' => (int) $student->id,
            'certificate_no' => null,
            'certificate_type' => $request->certificate_type,
            'class_id' => $student->class_id,
            'academic_year_id' => $activeYearId,
            'issue_date' => null,
            'reason' => $request->reason,
            'conduct' => null,
            'remarks' => null,
            'status' => 'pending',
            'approved_by' => null,
            'created_by' => $this->currentActorId(),
        ]);

        return redirect()->route('student.certificate.index')->with('success', 'Certificate request submitted.');
    }

    public function approve($id)
    {
        if (!$this->canApproveCertificate()) {
            abort(403, 'Unauthorized access');
        }
        $certificate = Certificate::findOrFail($id);
        if ($certificate->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }
        $issueDate = now()->toDateString();
        $certificate->update([
            'status' => 'approved',
            'approved_by' => $this->currentActorId(),
            'issue_date' => $issueDate,
            'certificate_no' => $certificate->certificate_no ?: $this->nextCertificateNumber(Carbon::parse($issueDate)->format('Y')),
        ]);
        return back()->with('success', 'Certificate approved.');
    }

    public function reject($id)
    {
        if (!$this->canApproveCertificate()) {
            abort(403, 'Unauthorized access');
        }
        $certificate = Certificate::findOrFail($id);
        if ($certificate->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }
        $certificate->update([
            'status' => 'rejected',
            'approved_by' => null,
        ]);
        return back()->with('success', 'Certificate rejected.');
    }

    public function download($id)
    {
        if (!$this->canViewCertificate()) {
            abort(403, 'Unauthorized access');
        }
        $certificate = Certificate::with(['student.class.teacher', 'student.section', 'student.parent', 'academicYear'])->findOrFail($id);
        $certificate = $this->decorateCertificate($certificate);
        if ($certificate->status !== 'approved') {
            return back()->with('error', 'Only approved certificates can be downloaded.');
        }

        if (!app()->bound('dompdf.wrapper')) {
            return back()->with('error', 'PDF package is not installed. Please install barryvdh/laravel-dompdf.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('certificate.pdf', ['certificate' => $certificate]);
        return $pdf->download($certificate->certificate_no . '.pdf');
    }

    public function studentShow($id)
    {
        if (!$this->isStudentUser()) {
            abort(403, 'Unauthorized access');
        }
        $studentId = $this->currentStudentId();

        $certificate = Certificate::with(['student.class.teacher', 'student.section', 'student.parent', 'academicYear'])
            ->where('id', $id)
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->firstOrFail();
        $certificate = $this->decorateCertificate($certificate);

        return view('certificate.student-show', compact('certificate'));
    }

    public function studentDownload($id)
    {
        if (!$this->isStudentUser()) {
            abort(403, 'Unauthorized access');
        }
        $studentId = $this->currentStudentId();
        $certificate = Certificate::with(['student.class.teacher', 'student.section', 'student.parent', 'academicYear'])
            ->where('id', $id)
            ->where('student_id', $studentId)
            ->firstOrFail();
        $certificate = $this->decorateCertificate($certificate);

        if ($certificate->status !== 'approved') {
            return back()->with('error', 'Only approved certificates can be downloaded.');
        }
        if (!app()->bound('dompdf.wrapper')) {
            return back()->with('error', 'PDF package is not installed. Please install barryvdh/laravel-dompdf.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('certificate.pdf', ['certificate' => $certificate]);
        return $pdf->download($certificate->certificate_no . '.pdf');
    }

    public function verify(string $certificateNo)
    {
        $certificate = Certificate::with(['student.class', 'student.section', 'student.parent', 'academicYear'])
            ->where('certificate_no', $certificateNo)
            ->first();

        return view('certificate.verify', compact('certificate', 'certificateNo'));
    }
}
