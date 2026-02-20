<?php

namespace App\Controllers\Api;

use App\Services\DocumentAlertService;

class DocumentAlertsController extends BaseApiController
{
    protected DocumentAlertService $alerts;

    public function __construct()
    {
        $this->alerts = new DocumentAlertService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ])->setStatusCode($code);
    }

    public function index()
    {
        if ($response = $this->ensurePermission('documents.view')) {
            return $response;
        }

        $data = $this->alerts->getAlerts([7, 15, 30]);
        return $this->ok($data);
    }
}