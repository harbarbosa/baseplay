<?php

namespace App\Services;

use App\Models\RolePermissionModel;
use App\Models\PermissionModel;
use App\Models\UserRoleModel;
use App\Models\RoleModel;

class RbacService
{
    protected RolePermissionModel $rolePermissions;
    protected PermissionModel $permissions;
    protected UserRoleModel $userRoles;
    protected RoleModel $roles;
    protected array $permissionCache = [];
    protected array $roleCache = [];

    public function __construct()
    {
        $this->rolePermissions = new RolePermissionModel();
        $this->permissions = new PermissionModel();
        $this->userRoles = new UserRoleModel();
        $this->roles = new RoleModel();
    }

    public function userHasPermission(int $userId, string $permission): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permission, $permissions, true);
    }

    public function getUserPermissions(int $userId): array
    {
        if (isset($this->permissionCache[$userId])) {
            return $this->permissionCache[$userId];
        }

        $roleIds = array_column(
            $this->userRoles->where('user_id', $userId)->findAll(),
            'role_id'
        );

        if (!$roleIds) {
            return $this->permissionCache[$userId] = [];
        }

        $permissionIds = array_column(
            $this->rolePermissions->whereIn('role_id', $roleIds)->findAll(),
            'permission_id'
        );

        if (!$permissionIds) {
            return $this->permissionCache[$userId] = [];
        }

        $permissions = array_column(
            $this->permissions->whereIn('id', $permissionIds)->findAll(),
            'name'
        );

        return $this->permissionCache[$userId] = $permissions;
    }

    public function getUserRoleNames(int $userId): array
    {
        if (isset($this->roleCache[$userId])) {
            return $this->roleCache[$userId];
        }

        $roleIds = array_column(
            $this->userRoles->where('user_id', $userId)->findAll(),
            'role_id'
        );

        if (!$roleIds) {
            return $this->roleCache[$userId] = [];
        }

        $roles = array_column(
            $this->roles->whereIn('id', $roleIds)->findAll(),
            'name'
        );

        return $this->roleCache[$userId] = $roles;
    }
}
