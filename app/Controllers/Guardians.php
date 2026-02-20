<?php

namespace App\Controllers;

use App\Services\AthleteGuardianService;
use App\Services\AthleteService;
use App\Services\CategoryService;
use App\Services\GuardianService;
use App\Services\TeamService;
use Config\Services;

class Guardians extends BaseController
{
    protected GuardianService $guardians;
    protected AthleteGuardianService $links;
    protected TeamService $teams;
    protected CategoryService $categories;
    protected AthleteService $athletes;

    public function __construct()
    {
        $this->guardians = new GuardianService();
        $this->links = new AthleteGuardianService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
        $this->athletes = new AthleteService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        $result = $this->guardians->list($filters, 15, 'guardians');

        return view('guardians/index', [
            'title' => 'Responsáveis',
            'guardians' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsável não encontrado.');
        }

        $athletes = $this->links->listByGuardian($id);

        return view('guardians/show', [
            'title' => 'Responsável',
            'guardian' => $guardian,
            'athletes' => $athletes,
        ]);
    }

    public function create()
    {
        return view('guardians/create', [
            'title' => 'Novo responsável',
            'teams' => $this->teams->list([], 200, 'teams_filter')['items'],
            'categories' => $this->categories->listAll(),
            'athletes' => $this->athletes->listAllWithRelations(),
        ]);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->guardianCreate, config('Validation')->guardianCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $guardianId = $this->guardians->create($this->request->getPost());

        $athleteId = (int) $this->request->getPost('athlete_id');
        if ($athleteId > 0) {
            $isPrimary = (int) ($this->request->getPost('link_is_primary') ?? 0);
            $notes = $this->request->getPost('link_notes');
            $this->links->link($athleteId, $guardianId, $isPrimary, $notes);
        }

        Services::audit()->log(session('user_id'), 'guardian_created', ['guardian_id' => $guardianId]);
        return redirect()->to('/guardians/' . $guardianId)->with('success', 'Responsável criado com sucesso.');
    }

    public function edit(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsável não encontrado.');
        }

        return view('guardians/edit', [
            'title' => 'Editar responsável',
            'guardian' => $guardian,
            'teams' => $this->teams->list([], 200, 'teams_filter')['items'],
            'categories' => $this->categories->listAll(),
            'athletes' => $this->athletes->listAllWithRelations(),
        ]);
    }

    public function update(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsável não encontrado.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->guardianUpdate, config('Validation')->guardianCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->guardians->update($id, $this->request->getPost());

        $athleteId = (int) $this->request->getPost('athlete_id');
        if ($athleteId > 0) {
            $isPrimary = (int) ($this->request->getPost('link_is_primary') ?? 0);
            $notes = $this->request->getPost('link_notes');
            $this->links->link($athleteId, $id, $isPrimary, $notes);
        }

        Services::audit()->log(session('user_id'), 'guardian_updated', ['guardian_id' => $id]);
        return redirect()->to('/guardians/' . $id)->with('success', 'Responsável atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsável não encontrado.');
        }

        return view('guardians/delete', ['title' => 'Excluir responsável', 'guardian' => $guardian]);
    }

    public function delete(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsável não encontrado.');
        }

        $this->guardians->delete($id);
        Services::audit()->log(session('user_id'), 'guardian_deleted', ['guardian_id' => $id]);

        return redirect()->to('/guardians')->with('success', 'Responsável removido.');
    }
}
