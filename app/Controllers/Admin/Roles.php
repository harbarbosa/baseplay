<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use CodeIgniter\I18n\Time;
use Config\Services;

class Roles extends BaseController
{
    public function index()
    {
        $roles = (new RoleModel())->orderBy('id', 'DESC')->findAll();

        return view('admin/roles/index', [
            'title' => 'Papéis',
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        return redirect()->to('/admin/roles')->with('error', 'Criação de papel desativada.');
    }

    public function store()
    {
        return redirect()->to('/admin/roles')->with('error', 'Criação de papel desativada.');
    }
}
