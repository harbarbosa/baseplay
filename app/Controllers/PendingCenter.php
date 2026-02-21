<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Services\PendingCenterService;
use App\Services\TeamService;

class PendingCenter extends BaseController
{
    protected PendingCenterService $pending;
    protected TeamService $teams;
    protected CategoryService $categories;

    public function __construct()
    {
        $this->pending = new PendingCenterService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
    }

    public function index()
    {
        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'type' => $this->request->getGet('type'),
        ];

        $filters['team_id'] = $this->pickScopedTeamId((int) ($filters['team_id'] ?? 0));

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam(!empty($filters['team_id']) ? (int) $filters['team_id'] : null, true);

        return view('pending_center/index', [
            'title' => 'Central de Pendencias',
            'filters' => $filters,
            'data' => $this->pending->getData($filters),
            'teams' => $teams,
            'categories' => $categories,
            'breadcrumbs' => [
                ['label' => 'Ferramentas'],
                ['label' => 'Central de Pendencias'],
            ],
        ]);
    }
}
