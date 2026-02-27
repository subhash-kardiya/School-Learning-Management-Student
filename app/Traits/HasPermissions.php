<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;

trait HasPermissions
{
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($role)
    {
        if (!$this->role) {
            return false;
        }

        $current = strtolower((string) $this->role->name);
        if (is_array($role)) {
            $roles = array_map(fn($r) => strtolower((string) $r), $role);
            return in_array($current, $roles, true);
        }

        return $current === strtolower((string) $role);
    }

    public function hasPermission($slug)
    {
        if (!$this->role) {
            return false;
        }

        static $permissions = [];
        if (!isset($permissions[$this->role_id])) {
            $permissions[$this->role_id] = $this->role->permissions->pluck('slug')->toArray();
        }

        return in_array($slug, $permissions[$this->role_id]);
    }
}
