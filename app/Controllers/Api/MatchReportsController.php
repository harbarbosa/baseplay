<?php

namespace App\Controllers\Api;

use App\Services\MatchReportService;

class MatchReportsController extends BaseApiController
{
    protected MatchReportService $reports;

    public function __construct()
    {
        $this->reports = new MatchReportService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
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

    public function show(int $matchId)
    {
        if ($response = $this->ensurePermission('match_reports.manage')) {
            return $response;
        }

        $report = $this->reports->findByMatch($matchId);
        return $this->ok($report);
    }

    public function store(int $matchId)
    {
        if ($response = $this->ensurePermission('match_reports.manage')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $id = $this->reports->upsert($matchId, $payload);
        return $this->ok(['id' => $id], 'Relatório salvo.');
    }
}