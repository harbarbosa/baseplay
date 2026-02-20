<?php

namespace App\Controllers\Api;

use App\Services\ReportService;
use App\Services\ExportService;

class ReportsController extends BaseApiController
{
    protected ReportService $reports;
    protected ExportService $export;

    public function __construct()
    {
        $this->reports = new ReportService();
        $this->export = new ExportService();
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

    public function attendance()
    {
        if ($response = $this->ensurePermission('reports.view')) {
            return $response;
        }

        $filters = $this->filters();
        $result = $this->reports->attendance($filters);
        return $this->formatResponse('Relatorio_presenca', $result);
    }

    public function trainings()
    {
        if ($response = $this->ensurePermission('reports.view')) {
            return $response;
        }

        $filters = $this->filters();
        $result = $this->reports->trainings($filters);
        return $this->formatResponse('Relatorio_treinos', $result);
    }

    public function matches()
    {
        if ($response = $this->ensurePermission('reports.view')) {
            return $response;
        }

        $filters = $this->filters();
        $result = $this->reports->matches($filters);
        return $this->formatResponse('Relatorio_jogos', $result);
    }

    public function documents()
    {
        if ($response = $this->ensurePermission('reports.view')) {
            return $response;
        }

        $filters = $this->filters();
        $filters['expiring_in_days'] = $this->request->getGet('expiring_in_days');
        $result = $this->reports->documents($filters);
        return $this->formatResponse('Relatorio_documentos', $result);
    }

    public function athlete(int $id)
    {
        if ($response = $this->ensurePermission('reports.view')) {
            return $response;
        }

        $filters = $this->filters();
        $result = $this->reports->athlete($id, $filters);
        return $this->formatResponse('Relatorio_atleta', $result);
    }

    protected function formatResponse(string $title, array $result)
    {
        $format = $this->request->getGet('format');
        if ($format === 'pdf') {
            return $this->export->toPdf($title . '.pdf', $title, $result['headers'], $result['rows']);
        }
        if ($format === 'xlsx') {
            return $this->export->toXlsx($title . '.xlsx', $result['headers'], $result['rows']);
        }

        return $this->ok([
            'headers' => $result['headers'],
            'rows' => $result['rows'],
            'summary' => $result['summary'] ?? null,
        ]);
    }

    protected function filters(): array
    {
        return [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'athlete_id' => $this->request->getGet('athlete_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'status' => $this->request->getGet('status'),
            'competition_name' => $this->request->getGet('competition_name'),
        ];
    }
}
