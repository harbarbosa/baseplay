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

        if ($this->scopedTeamIds !== []) {
            $filters['team_ids'] = $this->scopedTeamIds;
        }

        $result = $this->guardians->list($filters, 15, 'guardians');

        return view('guardians/index', [
            'title' => 'Responsaveis',
            'guardians' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsavel nao encontrado.');
        }

        if ($response = $this->denyIfGuardianForbidden($id)) {
            return $response;
        }

        $athletes = $this->links->listByGuardian($id);

        return view('guardians/show', [
            'title' => 'Responsavel',
            'guardian' => $guardian,
            'athletes' => $athletes,
        ]);
    }

    public function create()
    {
        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));

        return view('guardians/create', [
            'title' => 'Novo responsavel',
            'teams' => $teams,
            'categories' => $this->categories->listAll($teamId > 0 ? $teamId : null),
            'athletes' => $this->athletes->listAllWithRelations($this->scopedTeamIds),
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
        return redirect()->to('/guardians/' . $guardianId)->with('success', 'Responsavel criado com sucesso.');
    }

    public function edit(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsavel nao encontrado.');
        }

        if ($response = $this->denyIfGuardianForbidden($id)) {
            return $response;
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));

        return view('guardians/edit', [
            'title' => 'Editar responsavel',
            'guardian' => $guardian,
            'teams' => $teams,
            'categories' => $this->categories->listAll($teamId > 0 ? $teamId : null),
            'athletes' => $this->athletes->listAllWithRelations($this->scopedTeamIds),
        ]);
    }

    public function update(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsavel nao encontrado.');
        }

        if ($response = $this->denyIfGuardianForbidden($id)) {
            return $response;
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
        return redirect()->to('/guardians/' . $id)->with('success', 'Responsavel atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsavel nao encontrado.');
        }

        if ($response = $this->denyIfGuardianForbidden($id)) {
            return $response;
        }

        return view('guardians/delete', ['title' => 'Excluir responsavel', 'guardian' => $guardian]);
    }

    public function delete(int $id)
    {
        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return redirect()->to('/guardians')->with('error', 'Responsavel nao encontrado.');
        }

        if ($response = $this->denyIfGuardianForbidden($id)) {
            return $response;
        }

        $this->guardians->delete($id);
        Services::audit()->log(session('user_id'), 'guardian_deleted', ['guardian_id' => $id]);

        return redirect()->to('/guardians')->with('success', 'Responsavel removido.');
    }

    protected function denyIfGuardianForbidden(int $guardianId)
    {
        if ($this->scopedTeamIds === []) {
            return null;
        }

        $row = db_connect()->table('athlete_guardians ag')
            ->select('c.team_id')
            ->join('athletes a', 'a.id = ag.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('ag.guardian_id', $guardianId)
            ->whereIn('c.team_id', $this->scopedTeamIds)
            ->get()
            ->getRowArray();

        if (!$row) {
            return redirect()->to('/guardians')->with('error', 'Acesso negado.');
        }

        return null;
    }
}
