<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Classes;
use App\Models\TeacherMapping;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        $role = (string) session('role');
        $user = auth()->user();

        $canManageAll = in_array($role, ['admin', 'superadmin'], true)
            && $user
            && method_exists($user, 'hasPermission')
            && $user->hasPermission('notice_manage');

        $canTeacherManage = $role === 'teacher'
            && $user
            && method_exists($user, 'hasPermission')
            && ($user->hasPermission('notice_manage') || $user->hasPermission('notice_view'));

        $canCreate = $canManageAll || $canTeacherManage;

        $classQuery = Classes::query()->orderBy('name');
        if ($role === 'teacher') {
            $teacherClassIds = TeacherMapping::query()
                ->join('sections', 'sections.id', '=', 'teacher_mappings.section_id')
                ->where('teacher_mappings.teacher_id', $user?->id)
                ->pluck('sections.class_id')
                ->unique()
                ->values();
            $classQuery->whereIn('id', $teacherClassIds);
        }
        $classes = $classQuery->get();

        $announcementQuery = Announcement::query()
            ->with('classRoom')
            ->activeWindow()
            ->latest();
        if (!$canManageAll) {
            if ($role === 'teacher' && $user) {
                $announcementQuery->where(function ($query) use ($role, $user) {
                    $query->visibleTo($role, $user)
                        ->orWhere(function ($subQuery) use ($user) {
                            $subQuery->where('created_by_role', 'teacher')
                                ->where('created_by', (int) $user->id);
                        });
                });
            } else {
                $announcementQuery->visibleTo($role, $user);
            }
        }

        $announcements = $announcementQuery->paginate(10);

        // Mark announcements as seen for current user using session (single-table design).
        if ($user) {
            session([
                'announcements_last_seen_' . $role . '_' . (int) $user->id => now()->toDateTimeString(),
            ]);
        }

        return view('communication.announcements', compact(
            'announcements',
            'classes',
            'canCreate',
            'canManageAll',
            'role'
        ));
    }

    public function create()
    {
        $role = (string) session('role');
        $user = auth()->user();

        $canManageAll = in_array($role, ['admin', 'superadmin'], true)
            && $user
            && method_exists($user, 'hasPermission')
            && $user->hasPermission('notice_manage');

        $canTeacherManage = $role === 'teacher'
            && $user
            && method_exists($user, 'hasPermission')
            && ($user->hasPermission('notice_manage') || $user->hasPermission('notice_view'));

        $canCreate = $canManageAll || $canTeacherManage;
        if (!$canCreate) {
            abort(403, 'Unauthorized access');
        }

        $classQuery = Classes::query()->orderBy('name');
        if ($role === 'teacher') {
            $teacherClassIds = TeacherMapping::query()
                ->join('sections', 'sections.id', '=', 'teacher_mappings.section_id')
                ->where('teacher_mappings.teacher_id', $user?->id)
                ->pluck('sections.class_id')
                ->unique()
                ->values();
            $classQuery->whereIn('id', $teacherClassIds);
        }
        $classes = $classQuery->get();

        return view('communication.announcements-create', compact(
            'classes',
            'role'
        ));
    }

    public function edit(Announcement $announcement)
    {
        $role = (string) session('role');
        $user = auth()->user();

        $canManageAll = in_array($role, ['admin', 'superadmin'], true)
            && $user
            && method_exists($user, 'hasPermission')
            && $user->hasPermission('notice_manage');

        $canTeacherManage = $role === 'teacher'
            && $user
            && method_exists($user, 'hasPermission')
            && ($user->hasPermission('notice_manage') || $user->hasPermission('notice_view'))
            && (int) $announcement->created_by === (int) $user->id;

        if (!$canManageAll && !$canTeacherManage) {
            abort(403, 'Unauthorized access');
        }

        $classQuery = Classes::query()->orderBy('name');
        if ($role === 'teacher') {
            $teacherClassIds = TeacherMapping::query()
                ->join('sections', 'sections.id', '=', 'teacher_mappings.section_id')
                ->where('teacher_mappings.teacher_id', $user?->id)
                ->pluck('sections.class_id')
                ->unique()
                ->values();
            $classQuery->whereIn('id', $teacherClassIds);
        }
        $classes = $classQuery->get();

        return view('communication.announcements-edit', compact(
            'announcement',
            'classes',
            'role'
        ));
    }

    public function store(Request $request)
    {
        $role = (string) session('role');
        $user = auth()->user();

        $canManageAll = in_array($role, ['admin', 'superadmin'], true)
            && $user
            && method_exists($user, 'hasPermission')
            && $user->hasPermission('notice_manage');

        $canTeacherManage = $role === 'teacher'
            && $user
            && method_exists($user, 'hasPermission')
            && ($user->hasPermission('notice_manage') || $user->hasPermission('notice_view'));

        if (!$canManageAll && !$canTeacherManage) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'target_role' => ['required', Rule::in(['all', 'teacher', 'student', 'parent'])],
            'class_id' => ['nullable', 'exists:classes,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if ($role === 'superadmin') {
            $validated['level'] = 'system';
            $validated['school_id'] = null;
            $validated['class_id'] = null;
        } elseif ($role === 'admin') {
            $validated['level'] = 'school';
            $validated['school_id'] = $user?->school_id;
            $validated['class_id'] = null;
        } elseif ($canTeacherManage && !$canManageAll) {
            $teacherClassIds = TeacherMapping::query()
                ->join('sections', 'sections.id', '=', 'teacher_mappings.section_id')
                ->where('teacher_mappings.teacher_id', $user?->id)
                ->pluck('sections.class_id')
                ->unique()
                ->values();

            $validated['level'] = 'class';
            $validated['school_id'] = $user?->school_id;

            if (empty($validated['class_id']) || !$teacherClassIds->contains((int) $validated['class_id'])) {
                return back()->withErrors(['class_id' => 'Please select your assigned class only.'])->withInput();
            }

            if (!in_array((string) $validated['target_role'], ['student', 'parent'], true)) {
                return back()->withErrors(['target_role' => 'Teacher can target only Student or Parent.'])->withInput();
            }
        }

        $validated['created_by'] = $user?->id;
        $validated['created_by_role'] = $role;
        $validated['role_type'] = $validated['target_role']; // backward compatibility

        Announcement::create($validated);

        return redirect()
            ->route($role === 'teacher' ? 'teacher.communication.announcements' : 'communication.announcements')
            ->with('success', 'Announcement created successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $role = (string) session('role');
        $user = auth()->user();

        $canManageAll = in_array($role, ['admin', 'superadmin'], true)
            && $user
            && method_exists($user, 'hasPermission')
            && $user->hasPermission('notice_manage');

        $canTeacherManage = $role === 'teacher'
            && $user
            && method_exists($user, 'hasPermission')
            && ($user->hasPermission('notice_manage') || $user->hasPermission('notice_view'))
            && (int) $announcement->created_by === (int) $user->id;

        if (!$canManageAll && !$canTeacherManage) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'target_role' => ['required', Rule::in(['all', 'teacher', 'student', 'parent'])],
            'class_id' => ['nullable', 'exists:classes,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if ($role === 'superadmin') {
            $validated['level'] = 'system';
            $validated['school_id'] = null;
            $validated['class_id'] = null;
        } elseif ($role === 'admin') {
            $validated['level'] = 'school';
            $validated['school_id'] = $user?->school_id;
            $validated['class_id'] = null;
        } elseif ($canTeacherManage && !$canManageAll) {
            $teacherClassIds = TeacherMapping::query()
                ->join('sections', 'sections.id', '=', 'teacher_mappings.section_id')
                ->where('teacher_mappings.teacher_id', $user?->id)
                ->pluck('sections.class_id')
                ->unique()
                ->values();

            $validated['level'] = 'class';
            $validated['school_id'] = $user?->school_id;

            if (empty($validated['class_id']) || !$teacherClassIds->contains((int) $validated['class_id'])) {
                return back()->withErrors(['class_id' => 'Please select your assigned class only.'])->withInput();
            }

            if (!in_array((string) $validated['target_role'], ['student', 'parent'], true)) {
                return back()->withErrors(['target_role' => 'Teacher can target only Student or Parent.'])->withInput();
            }
        }

        $validated['role_type'] = $validated['target_role']; // backward compatibility
        $announcement->update($validated);

        return redirect()
            ->route($role === 'teacher' ? 'teacher.communication.announcements' : 'communication.announcements')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $role = (string) session('role');
        $user = auth()->user();

        $canManageAll = in_array($role, ['admin', 'superadmin'], true)
            && $user
            && method_exists($user, 'hasPermission')
            && $user->hasPermission('notice_manage');

        $canTeacherManage = $role === 'teacher'
            && $user
            && method_exists($user, 'hasPermission')
            && ($user->hasPermission('notice_manage') || $user->hasPermission('notice_view'))
            && (int) $announcement->created_by === (int) $user->id;

        if (!$canManageAll && !$canTeacherManage) {
            abort(403, 'Unauthorized access');
        }

        $announcement->delete();

        return back()->with('success', 'Announcement deleted successfully.');
    }
}
