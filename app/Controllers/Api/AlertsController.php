<?php

namespace App\Controllers\Api;

use App\Services\AlertService;

class AlertsController extends BaseApiController
{
    protected AlertService $alerts;

    public function __construct()
    {
        $this->alerts = new AlertService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ])->setStatusCode($code);
    }

    protected function fail(string $message, int $code = 400, $errors = null)
    {
        return service('response')->setJSON([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ])->setStatusCode($code);
    }

    public function index()
    {
        if ($response = $this->ensurePermission('alerts.view')) {
            return $response;
        }

        $filters = [
            'is_read' => $this->request->getGet('is_read'),
            'type' => $this->request->getGet('type'),
            'severity' => $this->request->getGet('severity'),
        ];

        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $result = $this->alerts->list($filters, $perPage, 'alerts_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('alerts_api'),
                'pageCount' => $result['pager']->getPageCount('alerts_api'),
                'perPage' => $result['pager']->getPerPage('alerts_api'),
                'total' => $result['pager']->getTotal('alerts_api'),
            ],
            'unread_count' => $this->alerts->unreadCount(),
        ]);
    }

    public function read(int $id)
    {
        if ($response = $this->ensurePermission('alerts.view')) {
            return $response;
        }

        $alert = $this->alerts->find($id);
        if (!$alert) {
            return $this->fail('Alerta nao encontrado.', 404);
        }

        $this->alerts->markRead($id);

        return $this->ok(['id' => $id], 'Alerta marcado como lido.');
    }
}