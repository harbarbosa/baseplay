<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\PermissionModel;
use App\Models\PasswordResetModel;
use CodeIgniter\I18n\Time;

class AuthService
{
    protected UserModel $users;
    protected UserRoleModel $userRoles;
    protected RoleModel $roles;
    protected RolePermissionModel $rolePermissions;
    protected PermissionModel $permissions;
    protected PasswordResetModel $passwordResets;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->userRoles = new UserRoleModel();
        $this->roles = new RoleModel();
        $this->rolePermissions = new RolePermissionModel();
        $this->permissions = new PermissionModel();
        $this->passwordResets = new PasswordResetModel();
    }

    public function attemptLogin(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);
        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $this->users->update($user['id'], [
            'last_login_at' => Time::now()->toDateTimeString(),
        ]);

        $session = session();
        $session->regenerate();
        $session->set([
            'user_id'    => $user['id'],
            'user_name'  => $user['name'],
            'user_email' => $user['email'],
        ]);

        return true;
    }

    public function logout(): void
    {
        session()->remove(['user_id', 'user_name', 'user_email']);
        session()->destroy();
    }

    public function isLoggedIn(): bool
    {
        return (bool) session('user_id');
    }

    public function user(): ?array
    {
        $userId = session('user_id');
        if (!$userId) {
            return null;
        }

        return $this->users->find($userId);
    }

    public function generateApiToken(int $userId, int $hoursValid = 24): string
    {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = Time::now()->addHours($hoursValid)->toDateTimeString();

        $this->users->update($userId, [
            'api_token_hash'       => $tokenHash,
            'api_token_expires_at' => $expiresAt,
        ]);

        return $rawToken;
    }

    public function validateApiToken(string $rawToken): ?array
    {
        $tokenHash = hash('sha256', $rawToken);
        $user = $this->users
            ->where('api_token_hash', $tokenHash)
            ->where('api_token_expires_at >=', Time::now()->toDateTimeString())
            ->where('status', 'active')
            ->first();

        return $user ?: null;
    }

    public function createPasswordReset(string $email): ?string
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return null;
        }

        $rawToken = bin2hex(random_bytes(24));
        $tokenHash = hash('sha256', $rawToken);

        $this->passwordResets->insert([
            'user_id'    => $user['id'],
            'email'      => $email,
            'token'      => $tokenHash,
            'expires_at' => Time::now()->addHours(2)->toDateTimeString(),
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        return $rawToken;
    }

    public function resetPassword(string $rawToken, string $newPassword): bool
    {
        $tokenHash = hash('sha256', $rawToken);
        $reset = $this->passwordResets
            ->where('token', $tokenHash)
            ->where('used_at IS NULL', null, false)
            ->where('expires_at >=', Time::now()->toDateTimeString())
            ->orderBy('id', 'DESC')
            ->first();

        if (!$reset) {
            return false;
        }

        $user = $this->users->find($reset['user_id']);
        if (!$user) {
            return false;
        }

        $this->users->update($user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        $this->passwordResets->update($reset['id'], [
            'used_at' => Time::now()->toDateTimeString(),
        ]);

        return true;
    }
}
