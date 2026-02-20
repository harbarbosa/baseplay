<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use Config\Services;
use CodeIgniter\I18n\Time;

class Users extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->orderBy('id', 'DESC')->paginate(15, 'users');
        $pager = $userModel->pager;

        return view('admin/users/index', [
            'title' => 'Usuários',
            'users' => $users,
            'pager' => $pager,
        ]);
    }

    public function create()
    {
        $roles = (new RoleModel())->orderBy('name')->findAll();
        return view('admin/users/create', ['title' => 'Novo usuário', 'roles' => $roles]);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->userCreate);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userModel = new UserModel();
        $userId = $userModel->insert([
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'        => 'active',
            'created_at'    => Time::now()->toDateTimeString(),
            'updated_at'    => Time::now()->toDateTimeString(),
        ]);

        (new UserRoleModel())->insert([
            'user_id'    => $userId,
            'role_id'    => (int) $this->request->getPost('role_id'),
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        Services::audit()->log(session('user_id'), 'user_created', ['user_id' => $userId]);

        return redirect()->to('/admin/users')->with('success', 'Usuário criado com sucesso.');
    }
}
