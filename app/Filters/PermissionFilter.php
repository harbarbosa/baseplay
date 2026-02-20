<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!Services::auth()->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $permission = $arguments[0] ?? null;
        if (!$permission) {
            return;
        }

        $userId = session('user_id');
        if (!$userId || !Services::rbac()->userHasPermission($userId, $permission)) {
            return redirect()->to('/')->with('error', 'Sem permissão para acessar esta área.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
