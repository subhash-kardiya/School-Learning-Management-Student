<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\TeacherMappingController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\HomeworkController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\ProfileController;


// =====================
// Authentication Routes
// =====================

Route::middleware(['guest.custom'])->group(function () {
    // Login
    Route::get('/login', fn() => view('auth.login'))->name('auth.login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    // Forgot Password
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('forgot.password');
    Route::post('/forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot.password.post');

    // OTP Verification
    Route::get('/verify-otp', fn() => view('auth.otp'))->name('otp.page');
    Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('verify.otp');

    // Change Password
    Route::get('/change-password', fn() => view('auth.change-password'))->name('change.password');
    Route::post('/change-password', [LoginController::class, 'changePassword'])->name('change.password.post');
});

// =====================
// Common Routes (Permission Based)
// =====================
Route::middleware(['auth.session'])->prefix('admin')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

// Academic Year CRUD (uses resource routes)
    Route::middleware(['permission:academic_year_manage'])->group(function () {
        Route::get('/academic-year', [AcademicYearController::class, 'index'])->name('academic.year.index');
        Route::post('/academic-year/store', [AcademicYearController::class, 'store'])->name('academic.year.store');
        Route::get('/academic-year/active/{id}', [AcademicYearController::class, 'setActive'])->name('academic.year.active');
        Route::get('/academic-year/lock/{id}', [AcademicYearController::class, 'lock'])->name('academic.year.lock');
        Route::get('/academic-year/unlock/{id}', [AcademicYearController::class, 'unlock'])->name('academic.year.unlock');
        Route::get('/academic-year/{id}/edit', [AcademicYearController::class, 'edit'])->name('academic.year.edit');
        Route::put('/academic-year/{id}', [AcademicYearController::class, 'update'])->name('academic.year.update');
        Route::delete('/academic-year/{id}', [AcademicYearController::class, 'destroy'])->name('academic.year.destroy');
    });


    Route::middleware(['permission:class_manage'])->group(function () {
        Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/create', [ClassController::class, 'create'])->name('classes.create');
        Route::post('/classes/store', [ClassController::class, 'store'])->name('classes.store');
        Route::get('/classes/{id}', [ClassController::class, 'show'])->name('classes.show');
        Route::get('/classes/{id}/edit', [ClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{id}', [ClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{id}', [ClassController::class, 'destroy'])->name('classes.destroy');
    });



    // Sections
    Route::middleware(['permission:section_manage'])->group(function () {
        Route::get('/section', [SectionController::class, 'index'])->name('section.index');
        Route::post('/section/store', [SectionController::class, 'store'])->name('section.store');
        Route::delete('/section/{id}', [SectionController::class, 'destroy'])->name('section.destroy');
        Route::get('/section/{id}', [SectionController::class, 'show'])->name('section.show');
        Route::get('/section/{id}/edit', [SectionController::class, 'edit'])->name('section.edit');
        Route::put('/section/{id}', [SectionController::class, 'update'])->name('section.update');
    });


    // Teachers
    Route::middleware(['permission:teacher_view'])->group(function () {
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/{id}', [TeacherController::class, 'show'])
            ->whereNumber('id')
            ->name('teachers.show');
    });
    Route::middleware(['permission:teacher_add'])->group(function () {
        Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers/store', [TeacherController::class, 'store'])->name('teachers.store');
    });
    Route::middleware(['permission:teacher_edit'])->group(function () {
        Route::get('/teachers/{id}/edit', [TeacherController::class, 'edit'])
            ->whereNumber('id')
            ->name('teachers.edit');
        Route::put('/teachers/{id}', [TeacherController::class, 'update'])
            ->whereNumber('id')
            ->name('teachers.update');
    });
    Route::middleware(['permission:teacher_delete'])->group(function () {
        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])
            ->whereNumber('id')
            ->name('teachers.destroy');
    });

    // Students
    Route::middleware(['permission:student_view'])->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/data', [StudentController::class, 'getStudents'])->name('students.data');
        Route::get('/students/{id}', [StudentController::class, 'show'])
            ->whereNumber('id')
            ->name('students.show');
        Route::get('/get-sections/{class_id}', [StudentController::class, 'getSections'])->name('get.sections');
        Route::get('/get-class-details/{class_id}', [StudentController::class, 'getClassDetails'])->name('get.class.details');
    });
    Route::middleware(['permission:student_add'])->group(function () {
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students/store', [StudentController::class, 'store'])->name('students.store');
    });
    Route::middleware(['permission:student_edit'])->group(function () {
        Route::get('/students/{id}/edit', [StudentController::class, 'edit'])
            ->whereNumber('id')
            ->name('students.edit');
        Route::put('/students/{id}', [StudentController::class, 'update'])
            ->whereNumber('id')
            ->name('students.update');
    });
    Route::middleware(['permission:student_delete'])->group(function () {
        Route::delete('/students/{id}', [StudentController::class, 'destroy'])
            ->whereNumber('id')
            ->name('students.destroy');
    });

    // Parents
    Route::middleware(['permission:parent_manage'])->group(function () {
        Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');
        Route::get('/parents/data', [ParentController::class, 'getParents'])->name('parents.data');
        Route::get('/parents/create', [ParentController::class, 'create'])->name('parents.create');
        Route::post('/parents/store', [ParentController::class, 'store'])->name('parents.store');
        Route::get('/parents/{id}', [ParentController::class, 'show'])->name('parents.show');
        Route::get('/parents/{id}/edit', [ParentController::class, 'edit'])->name('parents.edit');
        Route::put('/parents/{id}', [ParentController::class, 'update'])->name('parents.update');
        Route::delete('/parents/{id}', [ParentController::class, 'destroy'])->name('parents.destroy');
    });

    // Subjects
    Route::middleware(['permission:subject_manage'])->group(function () {
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::post('/subjects/store', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{id}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::get('/subjects/{id}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    });

// Teacher Mapping
    Route::middleware(['permission:class_manage'])->group(function () {
        Route::get('/teacher-mapping', [TeacherMappingController::class, 'index'])->name('teacher.mapping');
        // Safety: redirect any accidental GET hits to the store URL back to the index
        Route::get('/teacher-mapping/store', fn() => redirect()->route('teacher.mapping'));
        Route::post('/teacher-mapping/store', [TeacherMappingController::class, 'store'])->name('teacher.mapping.store');
        Route::delete('/teacher-mapping/{id}', [TeacherMappingController::class, 'destroy'])->name('teacher.mapping.destroy');
    });


    // Role and Permission



    Route::middleware(['permission:role_view'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware(['permission:role_add'])->group(function () {
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware(['permission:role_edit'])->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
    Route::middleware(['permission:role_delete'])->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });


    // Default redirect for admin base URL
    Route::get('/', fn() => redirect()->route('auth.login'));

    // =====================
    // Demo Routes (Timetable, Homework, Exams, Results, Communication, Certificate)
    // =====================
    Route::middleware(['role:admin|superadmin', 'permission:timetable.manage_all'])->group(function () {
        Route::get('/timetable/class', [TimetableController::class, 'classIndex'])->name('timetable.class');
        Route::get('/timetable/data', [TimetableController::class, 'data'])->name('timetable.data');
        Route::post('/timetable/settings', [TimetableController::class, 'saveSettings'])->name('timetable.settings.save');
    });
    Route::middleware(['role:admin|superadmin', 'permission:timetable.manage_all'])->group(function () {
        Route::post('/timetable/class', [TimetableController::class, 'store'])->name('timetable.class.store');
    });
    Route::middleware(['role:admin|superadmin', 'permission:timetable.manage_all'])->group(function () {
        Route::get('/timetable/{id}/edit', [TimetableController::class, 'edit'])->name('timetable.edit');
        Route::put('/timetable/{id}', [TimetableController::class, 'update'])->name('timetable.update');
    });
    Route::middleware(['role:admin|superadmin', 'permission:timetable.manage_all'])->group(function () {
        Route::delete('/timetable/{id}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
    });
    Route::middleware(['role:admin|superadmin|teacher', 'permission:timetable.view_own'])->group(function () {
        Route::get('/timetable/teacher', [TimetableController::class, 'teacherIndex'])->name('timetable.teacher');
        Route::get('/timetable/teacher/data', [TimetableController::class, 'teacherData'])->name('timetable.teacher.data');
    });

    Route::get('/homework/create', [HomeworkController::class, 'create'])->name('homework.create');
    Route::post('/homework/create', [HomeworkController::class, 'store'])->name('homework.store');
    Route::get('/homework/list', [HomeworkController::class, 'list'])->name('homework.list');
    Route::get('/homework/submission', [HomeworkController::class, 'submissions'])->name('homework.submission');
    Route::post('/homework/submission/{id}/feedback', [HomeworkController::class, 'feedback'])->name('homework.submission.feedback');

    Route::get('/exams/type', fn() => view('exams.type'))->name('exams.type');
    Route::get('/exams/schedule', fn() => view('exams.schedule'))->name('exams.schedule');
    Route::get('/exams/marks', fn() => view('exams.marks'))->name('exams.marks');

    Route::get('/results', fn() => view('results.index'))->name('results.index');

    Route::get('/communication/announcements', fn() => view('communication.announcements'))->name('communication.announcements');

    Route::get('/certificate', [CertificateController::class, 'index'])->name('certificate.index');
    Route::post('/certificate', [CertificateController::class, 'store'])->name('certificate.store');
    Route::get('/certificate/{id}', [CertificateController::class, 'show'])->name('certificate.show');

    // Attendance
    Route::get('/attendance/mark', [AttendanceController::class, 'markForm'])->name('attendance.mark');
    Route::post('/attendance/mark', [AttendanceController::class, 'markSave'])->name('attendance.mark.save');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/update', [AttendanceController::class, 'update'])->name('attendance.update');
});

// =====================
// Teacher Routes
// =====================
Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
});

// =====================
// Student Routes
// =====================
Route::middleware(['role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::middleware(['permission:timetable.view_class'])->group(function () {
        Route::get('/timetable', [TimetableController::class, 'studentIndex'])->name('student.timetable');
        Route::get('/timetable/data', [TimetableController::class, 'studentData'])->name('student.timetable.data');
    });
    Route::get('/homework', [HomeworkController::class, 'studentList'])->name('student.homework.list');
    Route::post('/homework/{homework}/submit', [HomeworkController::class, 'submit'])->name('student.homework.submit');
    Route::get('/attendance', [AttendanceController::class, 'studentView'])->name('student.attendance');
    Route::get('/certificates', [CertificateController::class, 'studentIndex'])->name('student.certificate.index');
    Route::get('/certificates/{id}', [CertificateController::class, 'studentShow'])->name('student.certificate.show');
});

// =====================
// Parent Routes
// =====================
Route::middleware(['role:parent'])->prefix('parent')->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');
    Route::middleware(['permission:timetable.view_child'])->group(function () {
        Route::get('/timetable', [TimetableController::class, 'parentIndex'])->name('parent.timetable');
        Route::get('/timetable/data', [TimetableController::class, 'parentData'])->name('parent.timetable.data');
    });
    Route::get('/attendance', [AttendanceController::class, 'parentView'])->name('parent.attendance');
});

// Logout
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

// =====================
// Default Redirect
// =====================
Route::get('/', fn() => redirect()->route('auth.login'));

// =====================
// Convenience Redirects (non-admin to admin)
// =====================
