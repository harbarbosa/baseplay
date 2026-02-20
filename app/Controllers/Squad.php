<?php

namespace App\Controllers;

use App\Services\SquadOverviewService;

class Squad extends BaseController
{
    protected SquadOverviewService $overview;

    public function __construct()
    {
        $this->overview = new SquadOverviewService();
    }

    public function index()
    {
        $userId = (int) (session('user_id') ?? 0);
        $pendingPage = max(1, (int) $this->request->getGet('pending_page'));
        $lowPage = max(1, (int) $this->request->getGet('low_page'));
        $perPage = 10;

        $data = $this->overview->overview($userId, [
            'pending_page' => $pendingPage,
            'low_page' => $lowPage,
            'per_page' => $perPage,
        ]);

        return view('squad/index', [
            'title' => 'Elenco overview',
            'kpis' => $data['kpis'],
            'pending' => $data['pending'],
            'lowAttendance' => $data['low_attendance'],
            'paging' => $data['paging'],
        ]);
    }
}
