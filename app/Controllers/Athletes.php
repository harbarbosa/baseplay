<?php

namespace App\Controllers;

use App\Services\AthleteService;
use App\Services\CategoryService;
use App\Services\TeamService;
use App\Services\GuardianService;
use App\Services\AthleteGuardianService;
use App\Services\AthleteSummaryService;
use Config\Services;

class Athletes extends BaseController
{
    protected AthleteService $athletes;
    protected CategoryService $categories;
    protected TeamService $teams;
    protected GuardianService $guardians;
    protected AthleteGuardianService $links;
    protected AthleteSummaryService $summary;

    public function __construct()
    {
        $this->athletes = new AthleteService();
        $this->categories = new CategoryService();
        $this->teams = new TeamService();
        $this->guardians = new GuardianService();
        $this->links = new AthleteGuardianService();
        $this->summary = new AthleteSummaryService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'status' => $this->request->getGet('status'),
        ];

        $filters['team_id'] = $this->pickScopedTeamId((int) ($filters['team_id'] ?? 0));

        $result = $this->athletes->list($filters, 15, 'athletes');
        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll(!empty($filters['team_id']) ? (int) $filters['team_id'] : null);

        return view('athletes/index', [
            'title' => 'Atletas',
            'athletes' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));
        if ($this->scopedTeamIds !== [] && !$teamId) {
            return redirect()->to('/athletes')->with('error', 'Acesso negado.');
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = [];
        if ($teamId > 0) {
            $this->categories->ensureStandardCategories($teamId, 10, 20);
            $categories = $this->categories->listDistinctByTeam($teamId, true);
        }

        return view('athletes/create', [
            'title' => 'Novo atleta',
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
        ]);
    }

    public function store()
    {
        $categoryId = (int) $this->request->getPost('category_id');
        if ($this->scopedTeamIds !== []) {
            $category = $this->categories->find($categoryId);
            if (!$category || !in_array((int) $category['team_id'], $this->scopedTeamIds, true)) {
                return redirect()->back()->withInput()->with('error', 'Categoria invalida para sua equipe.');
            }
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->athleteCreate, config('Validation')->athleteCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if ($this->isFutureDate($this->request->getPost('birth_date'))) {
            return redirect()->back()->withInput()->with('error', 'A data de nascimento nao pode ser futura.');
        }

        $athleteId = $this->athletes->create($this->request->getPost());
        Services::audit()->log(session('user_id'), 'athlete_created', ['athlete_id' => $athleteId]);

        return redirect()->to('/athletes/' . $athleteId)->with('success', 'Atleta criado com sucesso.');
    }

    public function show(int $id)
    {
        $athlete = $this->athletes->findWithRelations($id);
        if (!$athlete) {
            return redirect()->to('/athletes')->with('error', 'Atleta nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) ($athlete['team_id'] ?? 0), '/athletes')) {
            return $response;
        }

        $guardians = $this->links->listByAthlete($id);
        $guardiansList = $this->guardians->listAllActive();
        return view('athletes/show', [
            'title' => 'Perfil do atleta',
            'athlete' => $athlete,
            'guardians' => $guardians,
            'guardiansList' => $guardiansList,
            'lastActivity' => $this->summary->getLastActivity($id),
        ]);
    }

    public function edit(int $id)
    {
        $athlete = $this->athletes->find($id);
        if (!$athlete) {
            return redirect()->to('/athletes')->with('error', 'Atleta nao encontrado.');
        }

        $currentCategory = $this->categories->find((int) $athlete['category_id']);
        $teamId = $currentCategory ? (int) $currentCategory['team_id'] : 0;
        if ($response = $this->denyIfTeamForbidden($teamId, '/athletes')) {
            return $response;
        }

        $requestedTeamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));
        if ($requestedTeamId > 0) {
            $teamId = $requestedTeamId;
        }

        if ($teamId > 0) {
            $this->categories->ensureStandardCategories($teamId, 10, 20);
        }
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true);

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];

        return view('athletes/edit', [
            'title' => 'Editar atleta',
            'athlete' => $athlete,
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
        ]);
    }

    public function update(int $id)
    {
        $athlete = $this->athletes->find($id);
        if (!$athlete) {
            return redirect()->to('/athletes')->with('error', 'Atleta nao encontrado.');
        }

        $currentCategory = $this->categories->find((int) $athlete['category_id']);
        $teamId = $currentCategory ? (int) $currentCategory['team_id'] : 0;
        if ($response = $this->denyIfTeamForbidden($teamId, '/athletes')) {
            return $response;
        }

        $categoryId = (int) $this->request->getPost('category_id');
        if ($this->scopedTeamIds !== []) {
            $category = $this->categories->find($categoryId);
            if (!$category || !in_array((int) $category['team_id'], $this->scopedTeamIds, true)) {
                return redirect()->back()->withInput()->with('error', 'Categoria invalida para sua equipe.');
            }
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->athleteUpdate, config('Validation')->athleteCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if ($this->isFutureDate($this->request->getPost('birth_date'))) {
            return redirect()->back()->withInput()->with('error', 'A data de nascimento nao pode ser futura.');
        }

        $this->athletes->update($id, $this->request->getPost());
        Services::audit()->log(session('user_id'), 'athlete_updated', ['athlete_id' => $id]);

        return redirect()->to('/athletes/' . $id)->with('success', 'Atleta atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $athlete = $this->athletes->find($id);
        if (!$athlete) {
            return redirect()->to('/athletes')->with('error', 'Atleta nao encontrado.');
        }

        $currentCategory = $this->categories->find((int) $athlete['category_id']);
        $teamId = $currentCategory ? (int) $currentCategory['team_id'] : 0;
        if ($response = $this->denyIfTeamForbidden($teamId, '/athletes')) {
            return $response;
        }

        return view('athletes/delete', ['title' => 'Excluir atleta', 'athlete' => $athlete]);
    }

    public function delete(int $id)
    {
        $athlete = $this->athletes->find($id);
        if (!$athlete) {
            return redirect()->to('/athletes')->with('error', 'Atleta nao encontrado.');
        }

        $currentCategory = $this->categories->find((int) $athlete['category_id']);
        $teamId = $currentCategory ? (int) $currentCategory['team_id'] : 0;
        if ($response = $this->denyIfTeamForbidden($teamId, '/athletes')) {
            return $response;
        }

        $this->athletes->delete($id);
        Services::audit()->log(session('user_id'), 'athlete_deleted', ['athlete_id' => $id]);

        return redirect()->to('/athletes')->with('success', 'Atleta removido.');
    }

    public function linkGuardian(int $athleteId)
    {
        $athlete = $this->athletes->findWithRelations($athleteId);
        if (!$athlete) {
            return redirect()->to('/athletes')->with('error', 'Atleta nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) ($athlete['team_id'] ?? 0), '/athletes')) {
            return $response;
        }

        $guardianId = (int) $this->request->getPost('guardian_id');
        $isPrimary = (int) $this->request->getPost('is_primary');
        $notes = $this->request->getPost('notes');

        if ($guardianId <= 0) {
            return redirect()->back()->with('error', 'Selecione um responsavel.');
        }

        $this->links->link($athleteId, $guardianId, $isPrimary, $notes);
        Services::audit()->log(session('user_id'), 'athlete_guardian_linked', ['athlete_id' => $athleteId]);

        return redirect()->back()->with('success', 'Responsavel vinculado.');
    }

    public function createGuardianAndLink(int $athleteId)
    {
        $athlete = $this->athletes->findWithRelations($athleteId);
        if (!$athlete) {
            return redirect()->to('/athletes')->with('error', 'Atleta nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) ($athlete['team_id'] ?? 0), '/athletes')) {
            return $response;
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->guardianCreate, config('Validation')->guardianCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $guardianId = $this->guardians->create($this->request->getPost());
        $this->links->link($athleteId, $guardianId, 1, '');
        Services::audit()->log(session('user_id'), 'athlete_guardian_created', ['athlete_id' => $athleteId, 'guardian_id' => $guardianId]);

        return redirect()->back()->with('success', 'Responsavel criado e vinculado.');
    }

    public function updateLink(int $id)
    {
        $link = $this->links->findLink($id);
        if (!$link) {
            return redirect()->back()->with('error', 'Vinculo nao encontrado.');
        }

        $athlete = $this->athletes->findWithRelations((int) $link['athlete_id']);
        if ($athlete && ($response = $this->denyIfTeamForbidden((int) ($athlete['team_id'] ?? 0), '/athletes'))) {
            return $response;
        }

        $isPrimary = (int) $this->request->getPost('is_primary');
        $notes = $this->request->getPost('notes');
        $this->links->updateLink($id, (int) $link['athlete_id'], $isPrimary, $notes);
        Services::audit()->log(session('user_id'), 'athlete_guardian_updated', ['link_id' => $id]);

        return redirect()->back()->with('success', 'Vinculo atualizado.');
    }

    public function unlinkGuardian(int $id)
    {
        $link = $this->links->findLink($id);
        if (!$link) {
            return redirect()->back()->with('error', 'Vinculo nao encontrado.');
        }

        $athlete = $this->athletes->findWithRelations((int) $link['athlete_id']);
        if ($athlete && ($response = $this->denyIfTeamForbidden((int) ($athlete['team_id'] ?? 0), '/athletes'))) {
            return $response;
        }

        $this->links->unlink($id);
        Services::audit()->log(session('user_id'), 'athlete_guardian_unlinked', ['link_id' => $id]);

        return redirect()->back()->with('success', 'Vinculo removido.');
    }

    protected function isFutureDate(string $date): bool
    {
        if (!$date) {
            return false;
        }

        return strtotime($date) > time();
    }
}
