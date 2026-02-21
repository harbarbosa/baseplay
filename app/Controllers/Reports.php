<?php

namespace App\Controllers;

use App\Services\AthleteService;
use App\Services\CategoryService;
use App\Services\ExportService;
use App\Services\ReportService;
use App\Services\TeamService;

class Reports extends BaseController
{
    protected ReportService $reports;
    protected ExportService $export;
    protected TeamService $teams;
    protected CategoryService $categories;
    protected AthleteService $athletes;

    public function __construct()
    {
        $this->reports = new ReportService();
        $this->export = new ExportService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
        $this->athletes = new AthleteService();
    }

    public function attendance()
    {
        $filters = $this->commonFilters();
        $result = $this->reports->attendance($filters);
        return $this->renderReport('Relatorio de Presenca', 'reports/attendance', $filters, $result);
    }

    public function trainings()
    {
        $filters = $this->commonFilters();
        $result = $this->reports->trainings($filters);
        return $this->renderReport('Relatorio de Treinos', 'reports/trainings', $filters, $result);
    }

    public function matches()
    {
        $filters = $this->commonFilters();
        $result = $this->reports->matches($filters);
        return $this->renderReport('Relatorio de Jogos', 'reports/matches', $filters, $result);
    }

    public function documents()
    {
        $filters = $this->commonFilters();
        $filters['expiring_in_days'] = $this->request->getGet('expiring_in_days');
        $result = $this->reports->documents($filters);
        return $this->renderReport('Relatorio de Documentos', 'reports/documents', $filters, $result);
    }

    public function athlete(int $id)
    {
        $filters = $this->commonFilters();
        $result = $this->reports->athlete($id, $filters);
        return $this->renderReport('Relatorio do Atleta', 'reports/athlete', $filters, $result, $id);
    }

    protected function renderReport(string $title, string $view, array $filters, array $result, ?int $athleteId = null)
    {
        $format = $this->request->getGet('format');
        if ($format === 'pdf') {
            return $this->export->toPdf($this->filename($title, 'pdf'), $title, $result['headers'], $result['rows']);
        }
        if ($format === 'xlsx') {
            return $this->export->toXlsx($this->filename($title, 'xlsx'), $result['headers'], $result['rows']);
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll(!empty($filters['team_id']) ? (int) $filters['team_id'] : null);
        $athletesResult = $this->athletes->list(['team_id' => $filters['team_id']], 200, 'athletes_filter');
        $athletes = $athletesResult['items'] ?? [];

        return view($view, [
            'title' => $title,
            'filters' => $filters,
            'headers' => $result['headers'],
            'rows' => $result['rows'],
            'summary' => $result['summary'] ?? null,
            'teams' => $teams,
            'categories' => $categories,
            'athletes' => $athletes,
            'athleteId' => $athleteId,
        ]);
    }

    protected function commonFilters(): array
    {
        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'athlete_id' => $this->request->getGet('athlete_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'status' => $this->request->getGet('status'),
            'competition_name' => $this->request->getGet('competition_name'),
        ];

        $filters['team_id'] = $this->pickScopedTeamId((int) ($filters['team_id'] ?? 0));
        return $filters;
    }

    protected function filename(string $title, string $ext): string
    {
        $safe = preg_replace('/[^a-z0-9_\-]/i', '_', strtolower($title));
        return $safe . '.' . $ext;
    }
}
