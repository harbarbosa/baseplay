<?php

namespace App\Controllers;

use App\Services\TeamService;
use App\Services\CategoryService;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserTeamLinkModel;
use App\Models\RolePermissionModel;
use CodeIgniter\I18n\Time;
use Config\Services;

class Teams extends BaseController
{
    protected TeamService $teams;
    protected CategoryService $categories;

    public function __construct()
    {
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        if ($this->scopedTeamIds !== []) {
            $filters['ids'] = $this->scopedTeamIds;
        }

        $result = $this->teams->list($filters, 15, 'teams');

        return view('teams/index', [
            'title' => 'Equipes',
            'teams' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $team['id'], '/teams')) {
            return $response;
        }

        $categories = $this->categories->listByTeam($id);

        return view('teams/show', [
            'title' => 'Equipe',
            'team' => $team,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        return view('teams/create', ['title' => 'Nova equipe']);
    }

    public function store()
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $validation = service('validation');
        $rules = config('Validation')->teamCreate;
        $rules['admin_email'] = 'required|valid_email|is_unique[users.email]';
        $rules['admin_name'] = 'permit_empty|min_length[3]';
        $validation->setRules($rules, config('Validation')->teamCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->request->getPost();
        $teamId = $this->teams->create($payload);
        Services::audit()->log(session('user_id'), 'team_created', ['team_id' => $teamId]);

        $adminEmail = trim((string) ($payload['admin_email'] ?? ''));
        $adminName = trim((string) ($payload['admin_name'] ?? ''));
        if ($adminName === '') {
            $adminName = trim((string) ($payload['name'] ?? 'Equipe')) . ' Admin';
        }

        $tempPassword = bin2hex(random_bytes(4));

        $userId = (new UserModel())->insert([
            'name' => $adminName,
            'email' => $adminEmail,
            'password_hash' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'status' => 'active',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);

        $roleModel = new RoleModel();
        $role = $roleModel->where('name', 'admin_equipe')->first();
        if (!$role) {
            $roleId = $roleModel->insert([
                'name' => 'admin_equipe',
                'description' => 'Administrador de equipe',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
            $this->copyTrainerPermissionsToRole((int) $roleId);
        } else {
            $roleId = (int) $role['id'];
            $this->ensureRoleHasPermissions($roleId);
        }

        (new UserRoleModel())->insert([
            'user_id' => (int) $userId,
            'role_id' => (int) $roleId,
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        (new UserTeamLinkModel())->insert([
            'user_id' => (int) $userId,
            'team_id' => (int) $teamId,
            'role_in_team' => 'admin_equipe',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        Services::audit()->log(session('user_id'), 'team_admin_created', ['user_id' => $userId, 'team_id' => $teamId]);

        return redirect()->to('/teams')->with('success', 'Equipe criada. Admin da equipe: ' . $adminEmail . ' | Senha temporaria: ' . $tempPassword);
    }

    public function edit(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        return view('teams/edit', ['title' => 'Editar equipe', 'team' => $team]);
    }

    public function update(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        $validation = service('validation');
        $rules = config('Validation')->teamUpdate;
        $rules['name'] = str_replace('{id}', (string) $id, $rules['name']);
        $validation->setRules($rules, config('Validation')->teamCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->teams->update($id, $this->request->getPost());
        Services::audit()->log(session('user_id'), 'team_updated', ['team_id' => $id]);

        return redirect()->to('/teams/' . $id)->with('success', 'Equipe atualizada.');
    }

    public function deleteConfirm(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        return view('teams/delete', ['title' => 'Excluir equipe', 'team' => $team]);
    }

    public function delete(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        $this->teams->delete($id);
        Services::audit()->log(session('user_id'), 'team_deleted', ['team_id' => $id]);

        return redirect()->to('/teams')->with('success', 'Equipe removida.');
    }

    protected function copyTrainerPermissionsToRole(int $roleId): void
    {
        $trainer = (new RoleModel())->where('name', 'treinador')->first();
        if (!$trainer) {
            return;
        }

        $rolePermissions = new RolePermissionModel();
        $permissionIds = array_column(
            $rolePermissions->where('role_id', (int) $trainer['id'])->findAll(),
            'permission_id'
        );

        if ($permissionIds === []) {
            return;
        }

        $now = Time::now()->toDateTimeString();
        foreach ($permissionIds as $permissionId) {
            $rolePermissions->insert([
                'role_id' => $roleId,
                'permission_id' => (int) $permissionId,
                'created_at' => $now,
            ]);
        }
    }

    protected function ensureRoleHasPermissions(int $roleId): void
    {
        $rolePermissions = new RolePermissionModel();
        $exists = $rolePermissions->where('role_id', $roleId)->first();
        if ($exists) {
            return;
        }

        $this->copyTrainerPermissionsToRole($roleId);
    }
}
