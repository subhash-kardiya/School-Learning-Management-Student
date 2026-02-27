<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;

trait HasPermissions
{
    protected function resolveRoleModel(): ?Role
    {
        // Teachers now use a fixed "teacher" role instead of storing role_id.
        if ($this instanceof \App\Models\Teacher) {
            static $teacherRole = false;
            if ($teacherRole === false) {
                $teacherRole = Role::whereRaw('LOWER(name) = ?', ['teacher'])->first();
            }
            return $teacherRole ?: null;
        }

        if ($this->relationLoaded('role')) {
            return $this->getRelation('role');
        }

        return $this->role;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($role)
    {
        $resolvedRole = $this->resolveRoleModel();
        if (!$resolvedRole) {
            return false;
        }

        $current = strtolower((string) $resolvedRole->name);
        if (is_array($role)) {
            $roles = array_map(fn($r) => strtolower((string) $r), $role);
            return in_array($current, $roles, true);
        }

        return $current === strtolower((string) $role);
    }

    public function hasPermission($slug)
    {
        $resolvedRole = $this->resolveRoleModel();
        if (!$resolvedRole) {
            return false;
        }

        static $permissions = [];
        $cacheKey = $resolvedRole->id ?: strtolower((string) $resolvedRole->name);

        if (!isset($permissions[$cacheKey])) {
            $permissions[$cacheKey] = $resolvedRole->permissions->pluck('slug')->toArray();
        }

        return in_array($slug, $permissions[$cacheKey]);
    }
}
