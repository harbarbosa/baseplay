<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserRoleModel;
use App\Models\TeamModel;
use CodeIgniter\I18n\Time;

class Roles extends BaseController
{
    public function index()
    {
        $roleModel = new RoleModel();
        if ($this->scopedTeamIds !== []) {
            $roleModel->groupStart()
                ->where('team_id', null)
                ->orWhereIn('team_id', $this->scopedTeamIds)
                ->groupEnd();
        }

        $roles = $roleModel->orderBy('id', 'DESC')->findAll();

        return view('admin/roles/index', [
            'title' => 'Papeis',
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = (new PermissionModel())->orderBy('name')->findAll();

        $teamOptions = [];
        $selectedTeamId = null;
        $showTeamSelect = $this->scopedTeamIds === [];

        if ($showTeamSelect) {
            $teamOptions = (new TeamModel())->orderBy('name')->findAll();
            $selectedTeamId = old('team_id') !== null && old('team_id') !== '' ? (int) old('team_id') : null;
        } else {
            $teamOptions = (new TeamModel())->whereIn('id', $this->scopedTeamIds)->orderBy('name')->findAll();
            $selectedTeamId = $this->scopedTeamIds[0] ?? null;
        }

        return view('admin/roles/create', [
            'title' => 'Novo papel',
            'permissions' => $permissions,
            'teams' => $teamOptions,
            'showTeamSelect' => $showTeamSelect,
            'selectedTeamId' => $selectedTeamId,
        ]);
    }

    public function store()
    {
        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        $rolePermissionModel = new RolePermissionModel();

        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));

        if ($name === '') {
            return redirect()->back()->with('error', 'Nome do papel e obrigatorio.')->withInput();
        }

        if (strtolower($name) === 'admin') {
            return redirect()->back()->with('error', 'O papel admin e reservado.')->withInput();
        }

        $teamId = null;
        if ($this->scopedTeamIds !== []) {
            $teamId = $this->scopedTeamIds[0] ?? null;
            if (!$teamId) {
                return redirect()->back()->with('error', 'Nenhuma equipe vinculada.')->withInput();
            }
        } else {
            $postedTeamId = $this->request->getPost('team_id');
            if ($postedTeamId !== null && $postedTeamId !== '') {
                $teamId = (int) $postedTeamId;
                $team = (new TeamModel())->find($teamId);
                if (!$team) {
                    return redirect()->back()->with('error', 'Equipe invalida.')->withInput();
                }
            }
        }

        $existsQuery = $roleModel->where('name', $name);
        if ($teamId === null) {
            $existsQuery->where('team_id', null);
        } else {
            $existsQuery->where('team_id', $teamId);
        }

        if ($existsQuery->first()) {
            return redirect()->back()->with('error', 'Ja existe um papel com esse nome.')->withInput();
        }

        $roleId = (int) $roleModel->insert([
            'name' => $name,
            'description' => $description,
            'team_id' => $teamId,
        ], true);

        $permissionIds = $this->request->getPost('permissions') ?? [];
        $permissionIds = array_values(array_unique(array_filter(array_map('intval', (array) $permissionIds))));

        if ($permissionIds) {
            $validIds = array_column(
                $permissionModel->select('id')->whereIn('id', $permissionIds)->findAll(),
                'id'
            );
            $now = Time::now()->toDateTimeString();
            foreach ($validIds as $permissionId) {
                $rolePermissionModel->insert([
                    'role_id' => $roleId,
                    'permission_id' => (int) $permissionId,
                    'created_at' => $now,
                ]);
            }
        }

        return redirect()->to('/admin/roles')->with('success', 'Papel criado com sucesso.');
    }

    public function edit($id)
    {
        $roleModel = new RoleModel();
        $role = $roleModel->find($id);

        if (!$role) {
            return redirect()->to('/admin/roles')->with('error', 'Papel nao encontrado.');
        }

        if (strtolower((string) $role['name']) === 'admin') {
            return redirect()->to('/admin/roles')->with('error', 'O papel admin nao pode ser editado.');
        }

        if ($this->scopedTeamIds !== []) {
            if (empty($role['team_id']) || !in_array((int) $role['team_id'], $this->scopedTeamIds, true)) {
                return redirect()->to('/admin/roles')->with('error', 'Sem permissao para editar este papel.');
            }
        }

        $permissions = (new PermissionModel())->orderBy('name')->findAll();
        $assigned = array_column(
            (new RolePermissionModel())->where('role_id', (int) $id)->findAll(),
            'permission_id'
        );

        $teamOptions = [];
        $selectedTeamId = null;
        $showTeamSelect = $this->scopedTeamIds === [];

        if ($showTeamSelect) {
            $teamOptions = (new TeamModel())->orderBy('name')->findAll();
            $selectedTeamId = $role['team_id'] !== null ? (int) $role['team_id'] : null;
        } else {
            $teamOptions = (new TeamModel())->whereIn('id', $this->scopedTeamIds)->orderBy('name')->findAll();
            $selectedTeamId = $role['team_id'] !== null ? (int) $role['team_id'] : null;
        }

        return view('admin/roles/edit', [
            'title' => 'Editar papel',
            'role' => $role,
            'permissions' => $permissions,
            'assigned' => $assigned,
            'teams' => $teamOptions,
            'showTeamSelect' => $showTeamSelect,
            'selectedTeamId' => $selectedTeamId,
        ]);
    }

    public function update($id)
    {
        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        $rolePermissionModel = new RolePermissionModel();

        $role = $roleModel->find($id);
        if (!$role) {
            return redirect()->to('/admin/roles')->with('error', 'Papel nao encontrado.');
        }

        if (strtolower((string) $role['name']) === 'admin') {
            return redirect()->to('/admin/roles')->with('error', 'O papel admin nao pode ser editado.');
        }

        if ($this->scopedTeamIds !== []) {
            if (empty($role['team_id']) || !in_array((int) $role['team_id'], $this->scopedTeamIds, true)) {
                return redirect()->to('/admin/roles')->with('error', 'Sem permissao para editar este papel.');
            }
        }

        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));

        if ($name === '') {
            return redirect()->back()->with('error', 'Nome do papel e obrigatorio.')->withInput();
        }

        $teamId = $role['team_id'];
        if ($this->scopedTeamIds === []) {
            $postedTeamId = $this->request->getPost('team_id');
            if ($postedTeamId !== null && $postedTeamId !== '') {
                $teamId = (int) $postedTeamId;
                $team = (new TeamModel())->find($teamId);
                if (!$team) {
                    return redirect()->back()->with('error', 'Equipe invalida.')->withInput();
                }
            } else {
                $teamId = null;
            }
        }

        $existsQuery = $roleModel->where('name', $name)->where('id !=', (int) $id);
        if ($teamId === null) {
            $existsQuery->where('team_id', null);
        } else {
            $existsQuery->where('team_id', $teamId);
        }

        if ($existsQuery->first()) {
            return redirect()->back()->with('error', 'Ja existe um papel com esse nome.')->withInput();
        }

        $roleModel->update((int) $id, [
            'name' => $name,
            'description' => $description,
            'team_id' => $teamId,
        ]);

        $rolePermissionModel->where('role_id', (int) $id)->delete();

        $permissionIds = $this->request->getPost('permissions') ?? [];
        $permissionIds = array_values(array_unique(array_filter(array_map('intval', (array) $permissionIds))));

        if ($permissionIds) {
            $validIds = array_column(
                $permissionModel->select('id')->whereIn('id', $permissionIds)->findAll(),
                'id'
            );
            $now = Time::now()->toDateTimeString();
            foreach ($validIds as $permissionId) {
                $rolePermissionModel->insert([
                    'role_id' => (int) $id,
                    'permission_id' => (int) $permissionId,
                    'created_at' => $now,
                ]);
            }
        }

        return redirect()->to('/admin/roles')->with('success', 'Papel atualizado com sucesso.');
    }

    public function delete($id)
    {
        $roleModel = new RoleModel();
        $role = $roleModel->find($id);

        if (!$role) {
            return redirect()->to('/admin/roles')->with('error', 'Papel nao encontrado.');
        }

        if (strtolower((string) $role['name']) === 'admin') {
            return redirect()->to('/admin/roles')->with('error', 'O papel admin nao pode ser removido.');
        }

        if ($this->scopedTeamIds !== []) {
            if (empty($role['team_id']) || !in_array((int) $role['team_id'], $this->scopedTeamIds, true)) {
                return redirect()->to('/admin/roles')->with('error', 'Sem permissao para remover este papel.');
            }
        }

        $userRole = (new UserRoleModel())->where('role_id', (int) $id)->first();
        if ($userRole) {
            return redirect()->to('/admin/roles')->with('error', 'Nao e possivel remover um papel em uso.');
        }

        (new RolePermissionModel())->where('role_id', (int) $id)->delete();
        $roleModel->delete((int) $id);

        return redirect()->to('/admin/roles')->with('success', 'Papel removido com sucesso.');
    }
}
