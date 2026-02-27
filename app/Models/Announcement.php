<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'level',
        'school_id',
        'target_role',
        'role_type',
        'class_id',
        'start_date',
        'end_date',
        'created_by',
        'created_by_role',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function classRoom()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function getTargetAudienceLabelAttribute(): string
    {
        $target = (string) ($this->target_role ?: $this->role_type);

        return match ($target) {
            'teacher' => 'Teachers',
            'student' => 'Students',
            'parent' => 'Parents',
            default => 'All Users',
        };
    }

    public function getCreatorRoleLabelAttribute(): string
    {
        $role = strtolower((string) ($this->created_by_role ?? ''));
        return match ($role) {
            'admin', 'superadmin' => 'Admin',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            default => $this->inferCreatorRoleLabel(),
        };
    }

    public function getCreatorNameAttribute(): string
    {
        $creatorId = (int) ($this->created_by ?? 0);
        $role = strtolower((string) ($this->created_by_role ?? ''));

        if ($role === 'admin' || $role === 'superadmin') {
            return (string) (Admin::find($creatorId)?->admin_name ?? 'Admin');
        }

        if ($role === 'teacher') {
            return (string) (Teacher::find($creatorId)?->name ?? 'Teacher');
        }

        if ($role === 'student') {
            return (string) (Student::find($creatorId)?->student_name ?? 'Student');
        }

        if ($role === 'parent') {
            return (string) (ParentModel::find($creatorId)?->parent_name ?? 'Parent');
        }

        // Backward compatibility for old records where creator role was not stored.
        $admin = Admin::find($creatorId);
        if ($admin) {
            return (string) ($admin->admin_name ?? 'Admin');
        }
        $teacher = Teacher::find($creatorId);
        if ($teacher) {
            return (string) ($teacher->name ?? 'Teacher');
        }

        return 'Unknown User';
    }

    protected function inferCreatorRoleLabel(): string
    {
        $creatorId = (int) ($this->created_by ?? 0);
        if ($creatorId <= 0) {
            return 'User';
        }

        if (Admin::find($creatorId)) {
            return 'Admin';
        }
        if (Teacher::find($creatorId)) {
            return 'Teacher';
        }
        if (Student::find($creatorId)) {
            return 'Student';
        }
        if (ParentModel::find($creatorId)) {
            return 'Parent';
        }

        return 'User';
    }

    public function scopeActiveWindow(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    public function scopeVisibleTo(Builder $query, string $role, ?object $user): Builder
    {
        $role = strtolower(trim($role));
        $today = now()->toDateString();

        $query->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);

        if ($role === 'superadmin') {
            return $query;
        }

        if ($role === 'admin') {
            $schoolId = $user?->school_id;
            return $query->where(function (Builder $q) use ($schoolId) {
                $q->where('level', 'system')
                    ->orWhere(function (Builder $sub) use ($schoolId) {
                        $sub->whereIn('level', ['school', 'class'])
                            ->where(function (Builder $schoolMatch) use ($schoolId) {
                                if ($schoolId !== null) {
                                    $schoolMatch->where('school_id', $schoolId)->orWhereNull('school_id');
                                } else {
                                    $schoolMatch->whereNull('school_id');
                                }
                            });
                    });
            });
        }

        if ($role === 'teacher') {
            $schoolId = $user?->school_id;
            return $query->where(function (Builder $q) use ($schoolId) {
                $q->where('level', 'system')
                    ->orWhere(function (Builder $sub) use ($schoolId) {
                        $sub->where('level', 'school')
                            ->where(function (Builder $schoolMatch) use ($schoolId) {
                                if ($schoolId !== null) {
                                    $schoolMatch->where('school_id', $schoolId)->orWhereNull('school_id');
                                } else {
                                    $schoolMatch->whereNull('school_id');
                                }
                            });
                    });
            })->whereIn('target_role', ['all', 'teacher']);
        }

        if ($role === 'student') {
            $studentHasSchool = Schema::hasColumn('students', 'school_id');
            $studentSchoolId = $studentHasSchool ? ($user?->school_id) : null;
            $studentClassId = $user?->class_id;

            return $query->where(function (Builder $q) use ($studentSchoolId, $studentClassId) {
                $q->where('level', 'system')
                    ->orWhere(function (Builder $sub) use ($studentSchoolId) {
                        $sub->where('level', 'school')
                            ->where(function (Builder $schoolMatch) use ($studentSchoolId) {
                                if ($studentSchoolId !== null) {
                                    $schoolMatch->where('school_id', $studentSchoolId)->orWhereNull('school_id');
                                } else {
                                    $schoolMatch->whereNull('school_id');
                                }
                            });
                    })
                    ->orWhere(function (Builder $sub) use ($studentClassId) {
                        $sub->where('level', 'class')
                            ->where('class_id', $studentClassId);
                    });
            })->whereIn('target_role', ['all', 'student']);
        }

        if ($role === 'parent') {
            $studentColumns = ['class_id'];
            if (Schema::hasColumn('students', 'school_id')) {
                $studentColumns[] = 'school_id';
            }

            $children = Student::query()
                ->where('parent_id', $user?->id)
                ->get($studentColumns);

            $childClassIds = $children->pluck('class_id')->filter()->unique()->values();
            $childSchoolIds = Schema::hasColumn('students', 'school_id')
                ? $children->pluck('school_id')->filter()->unique()->values()
                : collect();

            return $query->where(function (Builder $q) use ($childSchoolIds, $childClassIds) {
                $q->where('level', 'system')
                    ->orWhere(function (Builder $sub) use ($childSchoolIds) {
                        $sub->where('level', 'school')
                            ->where(function (Builder $schoolMatch) use ($childSchoolIds) {
                                if ($childSchoolIds->isNotEmpty()) {
                                    $schoolMatch->whereIn('school_id', $childSchoolIds)->orWhereNull('school_id');
                                } else {
                                    $schoolMatch->whereNull('school_id');
                                }
                            });
                    })
                    ->orWhere(function (Builder $sub) use ($childClassIds) {
                        $sub->where('level', 'class');
                        if ($childClassIds->isNotEmpty()) {
                            $sub->whereIn('class_id', $childClassIds);
                        } else {
                            $sub->whereNull('class_id');
                        }
                    });
            })->whereIn('target_role', ['all', 'parent']);
        }

        return $query->whereRaw('1 = 0');
    }
}
