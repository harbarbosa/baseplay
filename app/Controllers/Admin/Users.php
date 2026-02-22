<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserTeamLinkModel;
use App\Models\UserCategoryLinkModel;
use App\Models\TeamModel;
use App\Models\CategoryModel;
use Config\Services;
use CodeIgniter\I18n\Time;
use CodeIgniter\Exceptions\PageNotFoundException;

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
        $categoryOptions = [];

        if ($showTeamSelect) {
            $teamOptions = (new TeamModel())->orderBy('name')->findAll();
        } else {
            $teamOptions = (new TeamModel())->whereIn('id', $this->scopedTeamIds)->orderBy('name')->findAll();
            $selectedTeamId = $this->scopedTeamIds[0] ?? null;
        }

        if (!empty($teamOptions)) {
            $categoryModel = new CategoryModel();
            $categoryModel->where('deleted_at', null)->where('status', 'active');
            if ($showTeamSelect) {
                $teamIds = array_values(array_filter(array_map('intval', array_column($teamOptions, 'id'))));
                if ($teamIds !== []) {
                    $categoryModel->whereIn('team_id', $teamIds);
                }
            } elseif ($selectedTeamId) {
                $categoryModel->where('team_id', $selectedTeamId);
            }
            if ($this->scopedCategoryIds !== []) {
                $categoryModel->whereIn('id', $this->scopedCategoryIds);
            }
            $categoryOptions = $categoryModel->orderBy('name', 'ASC')->findAll();
        }

        return view('admin/users/create', [
            'title' => 'Novo usuário',
            'roles' => $roles,
            'teams' => $teamOptions,
            'showTeamSelect' => $showTeamSelect,
            'selectedTeamId' => $selectedTeamId,
            'categories' => $categoryOptions,
            'selectedCategoryIds' => [],
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

        $categoryIds = $this->request->getPost('category_ids');
        if (!is_array($categoryIds)) {
            $categoryIds = $categoryIds ? [$categoryIds] : [];
        }
        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));

        if ($categoryIds !== [] && !$teamId) {
            return redirect()->back()->withInput()->with('error', 'Selecione uma equipe para definir categorias.');
        }

        $allowedCategoryIds = [];
        if ($teamId) {
            $categoryModel = new CategoryModel();
            $categoryModel->select('id')
                ->where('team_id', $teamId)
                ->where('deleted_at', null);
            if ($this->scopedCategoryIds !== []) {
                $categoryModel->whereIn('id', $this->scopedCategoryIds);
            }
            $allowedCategoryIds = array_map('intval', array_column($categoryModel->findAll(), 'id'));
        }

        if ($categoryIds !== []) {
            $invalidCategories = array_diff($categoryIds, $allowedCategoryIds);
            if ($invalidCategories !== []) {
                return redirect()->back()->withInput()->with('error', 'Uma ou mais categorias selecionadas são inválidas.');
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

        if ($categoryIds !== []) {
            $now = Time::now()->toDateTimeString();
            $categoryLinkModel = new UserCategoryLinkModel();
            foreach ($categoryIds as $categoryId) {
                $categoryLinkModel->insert([
                    'user_id' => $userId,
                    'category_id' => $categoryId,
                    'created_at' => $now,
                ]);
            }
        }

        Services::audit()->log(session('user_id'), 'user_created', ['user_id' => $userId]);

        return redirect()->to('/admin/users')->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            throw new PageNotFoundException('Usuário não encontrado.');
        }

        $teamLinkModel = new UserTeamLinkModel();
        $userTeamLink = $teamLinkModel->where('user_id', $id)->first();
        if ($this->scopedTeamIds !== []) {
            $teamIdScope = $userTeamLink['team_id'] ?? null;
            if (!$teamIdScope || !in_array((int) $teamIdScope, $this->scopedTeamIds, true)) {
                return redirect()->to('/admin/users')->with('error', 'Usuário fora do escopo.');
            }
        }

        $roleModel = new RoleModel();
        if ($this->scopedTeamIds !== []) {
            $roleModel->where('LOWER(name) !=', 'admin')
                ->groupStart()
                ->where('team_id', null)
                ->orWhereIn('team_id', $this->scopedTeamIds)
                ->groupEnd();
        }
        $roles = $roleModel->orderBy('name')->findAll();
        $roleLink = (new UserRoleModel())->find($id);
        $selectedRoleId = $roleLink['role_id'] ?? null;

        $teamOptions = [];
        $selectedTeamId = $userTeamLink['team_id'] ?? null;
        $showTeamSelect = $this->scopedTeamIds === [];
        if ($showTeamSelect) {
            $teamOptions = (new TeamModel())->orderBy('name')->findAll();
        } else {
            $teamOptions = (new TeamModel())->whereIn('id', $this->scopedTeamIds)->orderBy('name')->findAll();
            $selectedTeamId = $this->scopedTeamIds[0] ?? $selectedTeamId;
        }

        $categoryOptions = [];
        if (!empty($teamOptions)) {
            $categoryModel = new CategoryModel();
            $categoryModel->where('deleted_at', null)->where('status', 'active');
            if ($showTeamSelect) {
                $teamIds = array_values(array_filter(array_map('intval', array_column($teamOptions, 'id'))));
                if ($teamIds !== []) {
                    $categoryModel->whereIn('team_id', $teamIds);
                }
            } elseif ($selectedTeamId) {
                $categoryModel->where('team_id', $selectedTeamId);
            }
            if ($this->scopedCategoryIds !== []) {
                $categoryModel->whereIn('id', $this->scopedCategoryIds);
            }
            $categoryOptions = $categoryModel->orderBy('name', 'ASC')->findAll();
        }

        $selectedCategoryIds = array_map(
            'intval',
            array_column((new UserCategoryLinkModel())->where('user_id', $id)->findAll(), 'category_id')
        );

        return view('admin/users/edit', [
            'title' => 'Editar usuário',
            'user' => $user,
            'roles' => $roles,
            'teams' => $teamOptions,
            'showTeamSelect' => $showTeamSelect,
            'selectedTeamId' => $selectedTeamId,
            'selectedRoleId' => $selectedRoleId,
            'categories' => $categoryOptions,
            'selectedCategoryIds' => $selectedCategoryIds,
        ]);
    }

    public function update(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            throw new PageNotFoundException('Usuário não encontrado.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Informe nome e e-mail válidos.');
        }

        $existing = $userModel->where('email', $email)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'E-mail já em uso.');
        }

        $roleId = (int) $this->request->getPost('role_id');
        $role = (new RoleModel())->find($roleId);
        if (!$role) {
            return redirect()->back()->withInput()->with('error', 'Papel inválido.');
        }

        $teamLinkModel = new UserTeamLinkModel();
        $userTeamLink = $teamLinkModel->where('user_id', $id)->first();
        $teamId = $userTeamLink['team_id'] ?? null;

        if ($this->scopedTeamIds !== []) {
            if (strtolower((string) $role['name']) === 'admin') {
                return redirect()->back()->withInput()->with('error', 'Papel admin não permitido.');
            }
            if (!empty($role['team_id']) && !in_array((int) $role['team_id'], $this->scopedTeamIds, true)) {
                return redirect()->back()->withInput()->with('error', 'Papel fora do escopo da equipe.');
            }
            $teamId = $this->scopedTeamIds[0] ?? null;
        } else {
            $postedTeamId = $this->request->getPost('team_id');
            if ($postedTeamId !== null && $postedTeamId !== '') {
                $teamId = (int) $postedTeamId;
                $team = (new TeamModel())->find($teamId);
                if (!$team) {
                    return redirect()->back()->withInput()->with('error', 'Equipe inválida.');
                }
            } else {
                $teamId = null;
            }

            if (!empty($role['team_id'])) {
                if (!$teamId || (int) $role['team_id'] !== (int) $teamId) {
                    return redirect()->back()->withInput()->with('error', 'Papel não pertence à equipe selecionada.');
                }
            }
        }

        $categoryIds = $this->request->getPost('category_ids');
        if (!is_array($categoryIds)) {
            $categoryIds = $categoryIds ? [$categoryIds] : [];
        }
        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));

        if ($categoryIds !== [] && !$teamId) {
            return redirect()->back()->withInput()->with('error', 'Selecione uma equipe para definir categorias.');
        }

        $allowedCategoryIds = [];
        if ($teamId) {
            $categoryModel = new CategoryModel();
            $categoryModel->select('id')
                ->where('team_id', $teamId)
                ->where('deleted_at', null);
            if ($this->scopedCategoryIds !== []) {
                $categoryModel->whereIn('id', $this->scopedCategoryIds);
            }
            $allowedCategoryIds = array_map('intval', array_column($categoryModel->findAll(), 'id'));
        }

        if ($categoryIds !== []) {
            $invalidCategories = array_diff($categoryIds, $allowedCategoryIds);
            if ($invalidCategories !== []) {
                return redirect()->back()->withInput()->with('error', 'Uma ou mais categorias selecionadas são inválidas.');
            }
        }

        $updatePayload = [
            'name' => $name,
            'email' => $email,
            'updated_at' => Time::now()->toDateTimeString(),
        ];
        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $updatePayload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $userModel->update($id, $updatePayload);

        $userRoleModel = new UserRoleModel();
        $existingRole = $userRoleModel->find($id);
        if ($existingRole) {
            $userRoleModel->update($id, ['role_id' => $roleId]);
        } else {
            $userRoleModel->insert([
                'user_id' => $id,
                'role_id' => $roleId,
                'created_at' => Time::now()->toDateTimeString(),
            ]);
        }

        if ($teamId) {
            if ($userTeamLink) {
                $teamLinkModel->update($userTeamLink['id'], ['team_id' => $teamId]);
            } else {
                $teamLinkModel->insert([
                    'user_id' => $id,
                    'team_id' => $teamId,
                    'role_in_team' => 'member',
                    'created_at' => Time::now()->toDateTimeString(),
                ]);
            }
        } elseif ($userTeamLink) {
            $teamLinkModel->delete($userTeamLink['id']);
        }

        $categoryLinkModel = new UserCategoryLinkModel();
        $categoryLinkModel->where('user_id', $id)->delete();
        if ($categoryIds !== []) {
            $now = Time::now()->toDateTimeString();
            foreach ($categoryIds as $categoryId) {
                $categoryLinkModel->insert([
                    'user_id' => $id,
                    'category_id' => $categoryId,
                    'created_at' => $now,
                ]);
            }
        }

        Services::audit()->log(session('user_id'), 'user_updated', ['user_id' => $id]);

        return redirect()->to('/admin/users')->with('success', 'Usuário atualizado com sucesso.');
    }
}
