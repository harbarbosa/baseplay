<?php

namespace App\Controllers\Api;

use Config\Services;

class AuthController extends BaseApiController
{
    public function login()
    {
        $email = $this->request->getJSON(true)['email'] ?? $this->request->getPost('email');
        $password = $this->request->getJSON(true)['password'] ?? $this->request->getPost('password');

        if (!$email || !$password) {
            return $this->error('Email e senha são obrigatórios.', 422);
        }

        if (!Services::auth()->attemptLogin($email, $password)) {
            return $this->error('Credenciais inválidas.', 401);
        }

        $userId = session('user_id');
        $token = Services::auth()->generateApiToken($userId);
        $user = Services::auth()->user();
        $roles = Services::rbac()->getUserRoleNames($userId);
        $permissions = Services::rbac()->getUserPermissions($userId);

        Services::audit()->log($userId, 'api_login');

        return $this->success([
            'token' => $token,
            'user'  => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'roles' => $roles,
                'permissions' => $permissions,
            ],
        ], 'Login realizado.');
    }

    public function me()
    {
        $user = $this->apiUser();
        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        $userId = (int) $user['id'];
        $roles = Services::rbac()->getUserRoleNames($userId);
        $permissions = Services::rbac()->getUserPermissions($userId);

        return $this->success([
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
