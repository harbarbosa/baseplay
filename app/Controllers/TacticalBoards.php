<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Services\TacticalBoardService;
use App\Services\TacticalBoardStateService;
use App\Services\TacticalSequenceService;
use App\Services\TacticalSequenceFrameService;
use App\Services\TeamService;
use Config\Services;

class TacticalBoards extends BaseController
{
    protected TacticalBoardService $boards;
    protected TacticalBoardStateService $states;
    protected TeamService $teams;
    protected CategoryService $categories;
    protected TacticalSequenceService $sequences;
    protected TacticalSequenceFrameService $frames;

    public function __construct()
    {
        $this->boards = new TacticalBoardService();
        $this->states = new TacticalBoardStateService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
        $this->sequences = new TacticalSequenceService();
        $this->frames = new TacticalSequenceFrameService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
        ];

        $filters['team_id'] = $this->pickScopedTeamId((int) ($filters['team_id'] ?? 0));

        $result = $this->boards->list($filters, 15, 'tactical_boards');
        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam(!empty($filters['team_id']) ? (int) $filters['team_id'] : null, true);

        return view('tactical_boards/index', [
            'title' => 'Quadro tatico',
            'boards' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));
        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true);

        return view('tactical_boards/create', [
            'title' => 'Nova prancheta',
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
        ]);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules([
            'team_id' => 'required|integer|teamExists',
            'category_id' => 'required|integer|categoryExists',
            'title' => 'required|min_length[3]|max_length[150]',
            'description' => 'permit_empty',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->request->getPost();
        if ($this->scopedTeamIds !== [] && !empty($payload['team_id']) && !in_array((int) $payload['team_id'], $this->scopedTeamIds, true)) {
            return redirect()->back()->withInput()->with('error', 'Equipe invalida.');
        }

        $boardId = $this->boards->create($payload, (int) session('user_id'));
        $this->states->saveNewVersion($boardId, $this->states->defaultStateJson(), (int) session('user_id'));

        Services::audit()->log(session('user_id'), 'tactical_board_created', ['tactical_board_id' => $boardId]);

        return redirect()->to('/tactical-boards/' . $boardId)->with('success', 'Prancheta criada com sucesso.');
    }

    public function show(int $id)
    {
        return $this->renderEditor($id);
    }

    public function save(int $id)
    {
        $board = $this->boards->find($id);
        if (!$board) {
            return redirect()->to('/tactical-boards')->with('error', 'Prancheta nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards')) {
            return $response;
        }

        $stateJson = (string) ($this->request->getPost('state_json') ?? '');
        $decoded = json_decode($stateJson, true);
        if (!is_array($decoded)) {
            return redirect()->back()->with('error', 'Estado do quadro invalido.');
        }

        $savedId = $this->states->saveNewVersion($id, $stateJson, (int) session('user_id'));
        if ($savedId <= 0) {
            return redirect()->back()->with('error', 'Nao foi possivel salvar a versao.');
        }

        Services::audit()->log(session('user_id'), 'tactical_board_saved', [
            'tactical_board_id' => $id,
            'state_id' => $savedId,
        ]);

        return redirect()->to('/tactical-boards/' . $id)->with('success', 'Versao salva.');
    }

    public function states(int $id)
    {
        $board = $this->boards->findWithRelations($id);
        if (!$board) {
            return redirect()->to('/tactical-boards')->with('error', 'Prancheta nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards')) {
            return $response;
        }

        return view('tactical_boards/states', [
            'title' => 'Versoes da prancheta',
            'board' => $board,
            'versions' => $this->states->listByBoard($id, 100),
        ]);
    }

    public function load(int $id, int $stateId)
    {
        return $this->renderEditor($id, $stateId);
    }

    public function delete(int $id)
    {
        $board = $this->boards->find($id);
        if (!$board) {
            return redirect()->to('/tactical-boards')->with('error', 'Prancheta nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards')) {
            return $response;
        }

        $this->boards->delete($id);
        Services::audit()->log(session('user_id'), 'tactical_board_deleted', ['tactical_board_id' => $id]);

        return redirect()->to('/tactical-boards')->with('success', 'Prancheta removida.');
    }

    public function duplicate(int $id)
    {
        $board = $this->boards->find($id);
        if (!$board) {
            return redirect()->to('/tactical-boards')->with('error', 'Prancheta nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards')) {
            return $response;
        }

        $newBoardId = $this->boards->duplicate($id, (int) session('user_id'));
        if ($newBoardId <= 0) {
            return redirect()->to('/tactical-boards')->with('error', 'Nao foi possivel duplicar a prancheta.');
        }

        $latestState = $this->states->getLatest($id);
        $stateJson = $latestState['state_json'] ?? $this->states->defaultStateJson();
        $this->states->saveNewVersion($newBoardId, $stateJson, (int) session('user_id'));

        Services::audit()->log(session('user_id'), 'tactical_board_duplicated', [
            'source_tactical_board_id' => $id,
            'new_tactical_board_id' => $newBoardId,
        ]);

        return redirect()->to('/tactical-boards/' . $newBoardId)->with('success', 'Prancheta duplicada.');
    }

    public function listSequencesJson(int $boardId)
    {
        $board = $this->boards->find($boardId);
        if (!$board) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prancheta nao encontrada.', 'data' => null, 'errors' => null])->setStatusCode(404);
        }

        if ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards')) {
            return $response;
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'OK',
            'data' => $this->sequences->listByBoard($boardId),
            'errors' => null,
        ]);
    }

    public function createSequenceJson(int $boardId)
    {
        $board = $this->boards->find($boardId);
        if (!$board) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prancheta nao encontrada.', 'data' => null, 'errors' => null])->setStatusCode(404);
        }

        if ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Titulo obrigatorio.', 'data' => null, 'errors' => ['title' => 'required']])->setStatusCode(422);
        }

        $id = $this->sequences->create($boardId, $payload, (int) session('user_id'));
        if ($id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nao foi possivel criar sequencia.', 'data' => null, 'errors' => null])->setStatusCode(400);
        }

        $seedFrame = [
            [
                'frame_index' => 0,
                'duration_ms' => 500,
                'frame_json' => json_decode($this->states->defaultStateJson(), true),
            ],
        ];
        $this->frames->saveAll($id, $payload['fps'] ?? 2, $seedFrame);

        return $this->response->setJSON(['success' => true, 'message' => 'Sequencia criada.', 'data' => ['id' => $id], 'errors' => null])->setStatusCode(201);
    }

    public function updateSequenceJson(int $sequenceId)
    {
        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sequencia nao encontrada.', 'data' => null, 'errors' => null])->setStatusCode(404);
        }

        $board = $this->boards->find((int) $sequence['board_id']);
        if ($board && ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards'))) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $title = trim((string) ($payload['title'] ?? ''));
        if (array_key_exists('title', $payload) && $title === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Titulo obrigatorio.', 'data' => null, 'errors' => ['title' => 'required']])->setStatusCode(422);
        }

        $ok = $this->sequences->update($sequenceId, $payload);
        if (!$ok) {
            return $this->response->setJSON(['success' => false, 'message' => 'Falha ao atualizar sequencia.', 'data' => null, 'errors' => null])->setStatusCode(400);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Sequencia atualizada.', 'data' => ['id' => $sequenceId], 'errors' => null]);
    }

    public function deleteSequenceJson(int $sequenceId)
    {
        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sequencia nao encontrada.', 'data' => null, 'errors' => null])->setStatusCode(404);
        }

        $board = $this->boards->find((int) $sequence['board_id']);
        if ($board && ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards'))) {
            return $response;
        }

        $this->sequences->delete($sequenceId);
        return $this->response->setJSON(['success' => true, 'message' => 'Sequencia removida.', 'data' => ['id' => $sequenceId], 'errors' => null]);
    }

    public function listFramesJson(int $sequenceId)
    {
        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sequencia nao encontrada.', 'data' => null, 'errors' => null])->setStatusCode(404);
        }

        $board = $this->boards->find((int) $sequence['board_id']);
        if ($board && ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards'))) {
            return $response;
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'OK',
            'data' => $this->frames->listBySequence($sequenceId),
            'errors' => null,
        ]);
    }

    public function saveAllFramesJson(int $sequenceId)
    {
        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sequencia nao encontrada.', 'data' => null, 'errors' => null])->setStatusCode(404);
        }

        $board = $this->boards->find((int) $sequence['board_id']);
        if ($board && ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards'))) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $frames = $payload['frames'] ?? [];
        $fps = $payload['fps'] ?? ($sequence['fps'] ?? 2);

        if (!is_array($frames) || count($frames) < 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Frames invalidos.', 'data' => null, 'errors' => ['frames' => 'required']])->setStatusCode(422);
        }

        $ok = $this->frames->saveAll($sequenceId, $fps, $frames);
        if (!$ok) {
            return $this->response->setJSON(['success' => false, 'message' => 'Falha ao salvar frames.', 'data' => null, 'errors' => null])->setStatusCode(400);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Frames salvos.', 'data' => ['sequence_id' => $sequenceId], 'errors' => null]);
    }

    protected function renderEditor(int $id, ?int $stateId = null)
    {
        $board = $this->boards->findWithRelations($id);
        if (!$board) {
            return redirect()->to('/tactical-boards')->with('error', 'Prancheta nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/tactical-boards')) {
            return $response;
        }

        $selectedState = null;
        if ($stateId !== null) {
            $selectedState = $this->states->findByBoard($id, $stateId);
        }
        if (!$selectedState) {
            $selectedState = $this->states->getLatest($id);
        }
        if (!$selectedState) {
            $this->states->saveNewVersion($id, $this->states->defaultStateJson(), (int) session('user_id'));
            $selectedState = $this->states->getLatest($id);
        }

        return view('tactical_boards/editor', [
            'title' => 'Editor de prancheta',
            'board' => $board,
            'currentState' => $selectedState,
            'versions' => $this->states->listByBoard($id, 20),
            'canEdit' => has_permission('tactical_board.update'),
            'sequenceManage' => has_permission('tactical_sequence.manage'),
            'sequences' => $this->sequences->listByBoard($id),
        ]);
    }
}
