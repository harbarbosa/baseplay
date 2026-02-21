<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserTeamLinkModel;
use App\Models\TeamModel;
use Config\Services;
use CodeIgniter\I18n\Time;

class Users extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        if ($this->scopedTeamIds !== []) {
            $userModel->select('users.*')
                ->join('user_team_links utl', 'utl.user_id = users.id', 'inner')
                ->whereIn('utl.team_id', $this->scopedTeamIds)
                ->groupBy('users.id');
        }

        $users = $userModel->orderBy('users.id', 'DESC')->paginate(15, 'users');
        $pager = $userModel->pager;

        return view('admin/users/index', [
            'title' => 'Usuários',
            'users' => $users,
            'pager' => $pager,
        ]);
    }

    public function create()
    {
        $roleModel = new RoleModel();
        if ($this->scopedTeamIds !== []) {
            $roleModel->where('LOWER(name) !=', 'admin')
                ->groupStart()
                ->where('team_id', null)
                ->orWhereIn('team_id', $this->scopedTeamIds)
                ->groupEnd();
        }

        $roles = $roleModel->orderBy('name')->findAll();
        $teamOptions = [];
        $selectedTeamId = null;
        $showTeamSelect = $this->scopedTeamIds === [];

        if ($showTeamSelect) {
            $teamOptions = (new TeamModel())->orderBy('name')->findAll();
        } else {
            $teamOptions = (new TeamModel())->whereIn('id', $this->scopedTeamIds)->orderBy('name')->findAll();
            $selectedTeamId = $this->scopedTeamIds[0] ?? null;
        }

        return view('admin/users/create', [
            'title' => 'Novo usuário',
            'roles' => $roles,
            'teams' => $teamOptions,
            'showTeamSelect' => $showTeamSelect,
            'selectedTeamId' => $selectedTeamId,
        ]);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->userCreate);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $roleId = (int) $this->request->getPost('role_id');
        $role = (new RoleModel())->find($roleId);
        if (!$role) {
            return redirect()->back()->withInput()->with('error', 'Papel inválido.');
        }

        $teamId = null;
        if ($this->scopedTeamIds !== []) {
            if (strtolower((string) $role['name']) === 'admin') {
                return redirect()->back()->withInput()->with('error', 'Papel admin não permitido.');
            }

            if (!empty($role['team_id']) && !in_array((int) $role['team_id'], $this->scopedTeamIds, true)) {
                return redirect()->back()->withInput()->with('error', 'Papel fora do escopo da equipe.');
            }

            $teamId = $this->scopedTeamIds[0] ?? null;
            if (!$teamId) {
                return redirect()->back()->withInput()->with('error', 'Nenhuma equipe vinculada ao usuário.');
            }
        } else {
            $postedTeamId = $this->request->getPost('team_id');
            if ($postedTeamId !== null && $postedTeamId !== '') {
                $teamId = (int) $postedTeamId;
                $team = (new TeamModel())->find($teamId);
                if (!$team) {
                    return redirect()->back()->withInput()->with('error', 'Equipe inválida.');
                }
            }

            if (!empty($role['team_id'])) {
                if (!$teamId || (int) $role['team_id'] !== (int) $teamId) {
                    return redirect()->back()->withInput()->with('error', 'Papel não pertence à equipe selecionada.');
                }
            }
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
            'role_id'    => $roleId,
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        if ($teamId) {
            (new UserTeamLinkModel())->insert([
                'user_id' => $userId,
                'team_id' => $teamId,
                'role_in_team' => 'member',
                'created_at' => Time::now()->toDateTimeString(),
            ]);
        }

        Services::audit()->log(session('user_id'), 'user_created', ['user_id' => $userId]);

        return redirect()->to('/admin/users')->with('success', 'Usuário criado com sucesso.');
    }
}
