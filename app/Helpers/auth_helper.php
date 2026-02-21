<?php

use Config\Services;

if (!function_exists('current_user')) {
    function current_user(): array
    {
        return Services::auth()->user();
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return Services::auth()->isLoggedIn();
    }
}

if (!function_exists('user_roles')) {
    function user_roles(): array
    {
        $userId = session('user_id');
        if (!$userId) {
            return [];
        }

        return Services::rbac()->getUserRoleNames($userId);
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        $userId = session('user_id');
        if (!$userId) {
            return false;
        }

        return Services::rbac()->userHasPermission($userId, $permission);
    }
}

if (!function_exists('current_team')) {
    function current_team(): ?array
    {
        static $cached = false;
        if ($cached !== false) {
            return $cached;
        }

        $userId = (int) session('user_id');
        if ($userId <= 0) {
            return $cached = null;
        }

        if (function_exists('has_permission') && has_permission('admin.access')) {
            return $cached = null;
        }

        $row = db_connect()->table('user_team_links utl')
            ->select('t.*')
            ->join('teams t', 't.id = utl.team_id', 'inner')
            ->where('utl.user_id', $userId)
            ->where('t.deleted_at', null)
            ->orderBy('utl.id', 'ASC')
            ->get()
            ->getRowArray();

        return $cached = ($row ?: null);
    }
}
