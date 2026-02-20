<?php

namespace App\Controllers;

use App\Services\TeamService;
use App\Services\CategoryService;
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
            return redirect()->to('/teams')->with('error', 'Equipe não encontrada.');
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
        return view('teams/create', ['title' => 'Nova equipe']);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->teamCreate, config('Validation')->teamCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $teamId = $this->teams->create($this->request->getPost());
        Services::audit()->log(session('user_id'), 'team_created', ['team_id' => $teamId]);

        return redirect()->to('/teams')->with('success', 'Equipe criada com sucesso.');
    }

    public function edit(int $id)
    {
        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe não encontrada.');
        }

        return view('teams/edit', ['title' => 'Editar equipe', 'team' => $team]);
    }

    public function update(int $id)
    {
        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe não encontrada.');
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
        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe não encontrada.');
        }

        return view('teams/delete', ['title' => 'Excluir equipe', 'team' => $team]);
    }

    public function delete(int $id)
    {
        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe não encontrada.');
        }

        $this->teams->delete($id);
        Services::audit()->log(session('user_id'), 'team_deleted', ['team_id' => $id]);

        return redirect()->to('/teams')->with('success', 'Equipe removida.');
    }
}
