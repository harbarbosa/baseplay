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
        if (in_array($permission, $permissions, true)) {
            return true;
        }

        foreach ($this->expandPermissionAliases($permission) as $alias) {
            if (in_array($alias, $permissions, true)) {
                return true;
            }
        }

        return false;
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

        $roles = $this->roles->whereIn('id', $roleIds)->findAll();
        $roleNames = array_map('strtolower', array_column($roles, 'name'));
        if (in_array('admin', $roleNames, true)) {
            $allPermissions = array_column($this->permissions->findAll(), 'name');
            return $this->permissionCache[$userId] = $allPermissions;
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

    protected function expandPermissionAliases(string $permission): array
    {
        $aliases = [];
        if (str_starts_with($permission, 'tactical_board.')) {
            $aliases[] = 'tactical_boards.' . substr($permission, strlen('tactical_board.'));
        } elseif (str_starts_with($permission, 'tactical_boards.')) {
            $aliases[] = 'tactical_board.' . substr($permission, strlen('tactical_boards.'));
        }

        return $aliases;
    }
}
