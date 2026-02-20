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
