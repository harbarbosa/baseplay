<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return service('response')->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized',
            ])->setStatusCode(401);
        }

        $token = trim(substr($authHeader, 7));
        $user = Services::auth()->validateApiToken($token);
        if (!$user) {
            return service('response')->setJSON([
                'status'  => 'error',
                'message' => 'Invalid or expired token',
            ])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
