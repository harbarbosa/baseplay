<?php

namespace App\Controllers\Api;

use App\Services\DashboardService;

class DashboardController extends BaseApiController
{
    protected DashboardService $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ])->setStatusCode($code);
    }

    public function admin()
    {
        if ($response = $this->ensurePermission('admin.access')) {
            return $response;
        }

        return $this->ok($this->dashboard->admin());
    }

    public function trainer()
    {
        if ($response = $this->ensurePermission('training_plans.view')) {
            return $response;
        }

        $user = $this->apiUser();
        return $this->ok($this->dashboard->trainer((int) $user['id']));
    }

    public function assistant()
    {
        if ($response = $this->ensurePermission('attendance.manage')) {
            return $response;
        }

        $user = $this->apiUser();
        return $this->ok($this->dashboard->assistant((int) $user['id']));
    }

    public function athlete()
    {
        if ($response = $this->ensurePermission('dashboard.view')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->error('Não autenticado', 401);
        }

        return $this->ok($this->dashboard->athlete((int) $user['id']));
    }
}
