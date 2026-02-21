<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserRoleModel;
use CodeIgniter\I18n\Time;

class Roles extends BaseController
{
    public function index()
    {
        $roles = (new RoleModel())->orderBy('id', 'DESC')->findAll();

        return view('admin/roles/index', [
            'title' => 'Papeis',
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = (new PermissionModel())->orderBy('name')->findAll();

        return view('admin/roles/create', [
            'title' => 'Novo papel',
            'permissions' => $permissions,
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

        if ($roleModel->where('name', $name)->first()) {
            return redirect()->back()->with('error', 'Ja existe um papel com esse nome.')->withInput();
        }

        $roleId = (int) $roleModel->insert([
            'name' => $name,
            'description' => $description,
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

        $permissions = (new PermissionModel())->orderBy('name')->findAll();
        $assigned = array_column(
            (new RolePermissionModel())->where('role_id', (int) $id)->findAll(),
            'permission_id'
        );

        return view('admin/roles/edit', [
            'title' => 'Editar papel',
            'role' => $role,
            'permissions' => $permissions,
            'assigned' => $assigned,
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

        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));

        if ($name === '') {
            return redirect()->back()->with('error', 'Nome do papel e obrigatorio.')->withInput();
        }

        $exists = $roleModel->where('name', $name)->where('id !=', (int) $id)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Ja existe um papel com esse nome.')->withInput();
        }

        $roleModel->update((int) $id, [
            'name' => $name,
            'description' => $description,
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

        $userRole = (new UserRoleModel())->where('role_id', (int) $id)->first();
        if ($userRole) {
            return redirect()->to('/admin/roles')->with('error', 'Nao e possivel remover um papel em uso.');
        }

        (new RolePermissionModel())->where('role_id', (int) $id)->delete();
        $roleModel->delete((int) $id);

        return redirect()->to('/admin/roles')->with('success', 'Papel removido com sucesso.');
    }
}
