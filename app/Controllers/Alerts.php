<?php

namespace App\Controllers;

use App\Services\AlertService;

class Alerts extends BaseController
{
    protected AlertService $alerts;

    public function __construct()
    {
        $this->alerts = new AlertService();
    }

    public function index()
    {
        $filters = [
            'is_read' => $this->request->getGet('is_read'),
            'type' => $this->request->getGet('type'),
            'severity' => $this->request->getGet('severity'),
        ];

        $result = $this->alerts->list($filters, 20, 'alerts');

        return view('alerts/index', [
            'title' => 'Alertas',
            'items' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'unreadCount' => $this->alerts->unreadCount(),
        ]);
    }

    public function read(int $id)
    {
        $alert = $this->alerts->find($id);
        if (!$alert) {
            return redirect()->to('/alerts')->with('error', 'Alerta nao encontrado.');
        }

        $this->alerts->markRead($id);

        return redirect()->back()->with('success', 'Alerta marcado como lido.');
    }
}