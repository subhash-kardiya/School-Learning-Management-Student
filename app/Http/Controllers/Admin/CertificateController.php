<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Certificate;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CertificateController extends Controller
{
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
        if (!$this->canPermission('certificate_manage')) {
            abort(403, 'Unauthorized access');
        }

        $students = Student::with(['class', 'section', 'academicYear'])->orderBy('student_name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();
        $certificates = Certificate::with(['student.class', 'student.section', 'academicYear'])
            ->latest()
            ->get();
        return view('certificate.index', compact('students', 'academicYears', 'certificates'));
    }

    public function store(Request $request)
    {
        if (!$this->canPermission('certificate_manage')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'certificate_type' => 'required|in:bonafide,leaving',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'issue_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'conduct' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
            'status' => 'required|in:draft,issued',
        ]);

        $cert = Certificate::create([
            'student_id' => $request->student_id,
            'certificate_no' => 'TMP-' . time(),
            'certificate_type' => $request->certificate_type,
            'academic_year_id' => $request->academic_year_id,
            'issue_date' => $request->issue_date,
            'reason' => $request->reason,
            'conduct' => $request->conduct,
            'remarks' => $request->remarks,
            'status' => $request->status,
        ]);

        $year = Carbon::parse($request->issue_date)->format('Y');
        $cert->update([
            'certificate_no' => 'CERT-' . $year . '-' . str_pad((string) $cert->id, 4, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('certificate.show', $cert->id)
            ->with('success', 'Certificate generated.');
    }

    public function show($id)
    {
        if (!$this->canPermission('certificate_manage')) {
            abort(403, 'Unauthorized access');
        }

        $certificate = Certificate::with(['student.class', 'student.section', 'academicYear'])
            ->findOrFail($id);

        return view('certificate.show', compact('certificate'));
    }

    public function studentIndex()
    {
        $user = Auth::user();
        if (
            !$user ||
            !method_exists($user, 'hasRole') ||
            !$user->hasRole('student') ||
            !method_exists($user, 'hasPermission') ||
            !$user->hasPermission('certificate_view')
        ) {
            abort(403, 'Unauthorized access');
        }

        $certificates = Certificate::with(['academicYear'])
            ->where('student_id', $user->id)
            ->where('status', 'issued')
            ->latest()
            ->get();

        return view('certificate.student', compact('certificates'));
    }

    public function studentShow($id)
    {
        $user = Auth::user();
        if (
            !$user ||
            !method_exists($user, 'hasRole') ||
            !$user->hasRole('student') ||
            !method_exists($user, 'hasPermission') ||
            !$user->hasPermission('certificate_view')
        ) {
            abort(403, 'Unauthorized access');
        }

        $certificate = Certificate::with(['student.class', 'student.section', 'academicYear'])
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->where('status', 'issued')
            ->firstOrFail();

        return view('certificate.student-show', compact('certificate'));
    }
}
