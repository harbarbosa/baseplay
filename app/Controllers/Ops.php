<?php

namespace App\Controllers;

use App\Services\OpsOverviewService;

class Ops extends BaseController
{
    protected OpsOverviewService $overview;

    public function __construct()
    {
        $this->overview = new OpsOverviewService();
    }

    public function index()
    {
        $data = $this->overview->overview((int) session('user_id'));

        return view('ops/index', [
            'title' => 'Operacao overview',
            'cards' => $data['cards'],
            'upcoming' => $data['upcoming'],
        ]);
    }
}
