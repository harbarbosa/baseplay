<?php

namespace App\Controllers;

use App\Services\DashboardService;
use Config\Services;

class Dashboard extends BaseController
{
    public function index()
    {
        $userId = (int) session('user_id');
        $roles = Services::rbac()->getUserRoleNames($userId);
        $role = $roles[0] ?? 'admin';
        $service = new DashboardService();

        switch ($role) {
            case 'admin_equipe':
            case 'treinador':
                $data = $service->trainer($userId);
                break;
            case 'auxiliar':
                $data = $service->assistant($userId);
                break;
            case 'admin':
            case 'cordenador': // compatibilidade legado
                $data = $service->admin();
                break;
            case 'atleta':
            case 'responsavel':
                $data = $service->athlete($userId);
                break;
            default:
                $data = $service->admin();
                break;
        }

        return view('dashboard/index', [
            'title' => 'Painel',
            'role' => $role,
            'data' => $data,
        ]);
    }
}
