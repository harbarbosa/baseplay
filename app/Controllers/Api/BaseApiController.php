<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;

class BaseApiController extends Controller
{
    protected array $apiRolesCache = [];

    protected function apiUser(): array
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($authHeader, 7));
        return \Config\Services::auth()->validateApiToken($token);
    }

    protected function ensurePermission(string $permission)
    {
        $user = $this->apiUser();
        if (!$user) {
            return service('response')->setJSON([
                'success' => false,
                'message' => 'Não autenticado',
                'data' => null,
                'errors' => null,
            ])->setStatusCode(401);
        }

        $allowed = \Config\Services::rbac()->userHasPermission((int) $user['id'], $permission);
        if (!$allowed) {
            return service('response')->setJSON([
                'success' => false,
                'message' => 'Acesso negado',
                'data' => null,
                'errors' => null,
            ])->setStatusCode(403);
        }

        return null;
    }

    protected function apiUserRoles(?array $user = null): array
    {
        $user = $user ?? $this->apiUser();
        if (!$user) {
            return [];
        }

        $userId = (int) ($user['id'] ?? 0);
        if ($userId <= 0) {
            return [];
        }

        if (isset($this->apiRolesCache[$userId])) {
            return $this->apiRolesCache[$userId];
        }

        $roles = \Config\Services::rbac()->getUserRoleNames($userId);
        return $this->apiRolesCache[$userId] = array_map(
            static fn(string $role): string => mb_strtolower(trim($role)),
            $roles
        );
    }

    protected function apiUserHasRole(string $role, ?array $user = null): bool
    {
        $needle = mb_strtolower(trim($role));
        foreach ($this->apiUserRoles($user) as $userRole) {
            if ($userRole === $needle) {
                return true;
            }

            // Compatibilidade com escrita de "responsável" sem acento.
            if ($needle === 'responsavel' && in_array($userRole, ['responsável', 'responsã¡vel'], true)) {
                return true;
            }
        }

        return false;
    }

    protected function success($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ])->setStatusCode($code);
    }

    protected function error(string $message, int $code = 400, $errors = null)
    {
        return service('response')->setJSON([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ])->setStatusCode($code);
    }
}
