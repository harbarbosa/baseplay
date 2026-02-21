<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Services\TeamService;
use Config\Services;

class Categories extends BaseController
{
    protected CategoryService $categories;
    protected TeamService $teams;

    public function __construct()
    {
        $this->categories = new CategoryService();
        $this->teams = new TeamService();
    }

    public function create(int $teamId)
    {
        $teamId = (int) $this->pickScopedTeamId($teamId);
        if ($this->scopedTeamIds !== [] && !$teamId) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($teamId);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        return view('categories/create', [
            'title' => 'Nova categoria',
            'team' => $team,
        ]);
    }

    public function store(int $teamId)
    {
        $teamId = (int) $this->pickScopedTeamId($teamId);
        if ($this->scopedTeamIds !== [] && !$teamId) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($teamId);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        $validation = service('validation');
        $rules = config('Validation')->categoryCreate;
        $rules['name'] = str_replace('{team_id}', (string) $teamId, $rules['name']);
        $validation->setRules($rules, config('Validation')->categoryCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->request->getPost();
        $payload['team_id'] = $teamId;

        if (!$this->validateYearRange($payload)) {
            return redirect()->back()->withInput()->with('error', 'Ano inicial nao pode ser maior que o ano final.');
        }

        $categoryId = $this->categories->create($payload);
        Services::audit()->log(session('user_id'), 'category_created', ['category_id' => $categoryId]);

        return redirect()->to('/teams/' . $teamId)->with('success', 'Categoria criada.');
    }

    public function edit(int $id)
    {
        $category = $this->categories->find($id);
        if (!$category) {
            return redirect()->to('/teams')->with('error', 'Categoria nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $category['team_id'], '/teams')) {
            return $response;
        }

        $team = $this->teams->find((int) $category['team_id']);

        return view('categories/edit', [
            'title' => 'Editar categoria',
            'category' => $category,
            'team' => $team,
        ]);
    }

    public function update(int $id)
    {
        $category = $this->categories->find($id);
        if (!$category) {
            return redirect()->to('/teams')->with('error', 'Categoria nao encontrada.');
        }

        $teamId = (int) $category['team_id'];
        if ($response = $this->denyIfTeamForbidden($teamId, '/teams')) {
            return $response;
        }

        $validation = service('validation');
        $rules = config('Validation')->categoryUpdate;
        $rules['name'] = str_replace('{team_id}', (string) $teamId, $rules['name']);
        $rules['name'] = str_replace('{id}', (string) $id, $rules['name']);
        $validation->setRules($rules, config('Validation')->categoryCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->request->getPost();
        if (!$this->validateYearRange($payload)) {
            return redirect()->back()->withInput()->with('error', 'Ano inicial nao pode ser maior que o ano final.');
        }

        $this->categories->update($id, $payload);
        Services::audit()->log(session('user_id'), 'category_updated', ['category_id' => $id]);

        return redirect()->to('/teams/' . $teamId)->with('success', 'Categoria atualizada.');
    }

    public function deleteConfirm(int $id)
    {
        $category = $this->categories->find($id);
        if (!$category) {
            return redirect()->to('/teams')->with('error', 'Categoria nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $category['team_id'], '/teams')) {
            return $response;
        }

        return view('categories/delete', [
            'title' => 'Excluir categoria',
            'category' => $category,
        ]);
    }

    public function delete(int $id)
    {
        $category = $this->categories->find($id);
        if (!$category) {
            return redirect()->to('/teams')->with('error', 'Categoria nao encontrada.');
        }

        $teamId = (int) $category['team_id'];
        if ($response = $this->denyIfTeamForbidden($teamId, '/teams')) {
            return $response;
        }

        $this->categories->delete($id);
        Services::audit()->log(session('user_id'), 'category_deleted', ['category_id' => $id]);

        return redirect()->to('/teams/' . $teamId)->with('success', 'Categoria removida.');
    }

    protected function validateYearRange(array $payload): bool
    {
        if (!empty($payload['year_from']) && !empty($payload['year_to'])) {
            return (int) $payload['year_from'] <= (int) $payload['year_to'];
        }

        return true;
    }
}
