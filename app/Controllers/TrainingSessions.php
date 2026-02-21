<?php

namespace App\Controllers;

use App\Services\TrainingSessionService;
use App\Services\TrainingSessionAthleteService;
use App\Services\TrainingPlanService;
use App\Services\TeamService;
use App\Services\CategoryService;
use App\Services\AthleteService;
use App\Services\EventService;
use Config\Services;

class TrainingSessions extends BaseController
{
    protected TrainingSessionService $sessions;
    protected TrainingSessionAthleteService $sessionAthletes;
    protected TrainingPlanService $plans;
    protected TeamService $teams;
    protected CategoryService $categories;
    protected AthleteService $athletes;
    protected EventService $events;

    public function __construct()
    {
        $this->sessions = new TrainingSessionService();
        $this->sessionAthletes = new TrainingSessionAthleteService();
        $this->plans = new TrainingPlanService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
        $this->athletes = new AthleteService();
        $this->events = new EventService();
    }

    public function index()
    {
        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $filters['team_id'] = $this->pickScopedTeamId((int) ($filters['team_id'] ?? 0));

        $result = $this->sessions->list($filters, 15, 'training_sessions');
        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll($filters['team_id'] ? (int) $filters['team_id'] : null);

        return view('training_sessions/index', [
            'title' => 'Sessoes realizadas',
            'sessions' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function show(int $id)
    {
        $session = $this->sessions->findWithRelations($id);
        if (!$session) {
            return redirect()->to('/training-sessions')->with('error', 'Sessao nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $session['team_id'], '/training-sessions')) {
            return $response;
        }

        $athletes = $this->sessions->listAthletes($id);

        return view('training_sessions/show', [
            'title' => 'Sessao',
            'session' => $session,
            'athletes' => $athletes,
        ]);
    }

    public function create()
    {
        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));
        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll($teamId > 0 ? $teamId : null);
        $plans = $this->plans->list([], 200, 'training_plans_select')['items'];

        return view('training_sessions/create', [
            'title' => 'Nova sessao',
            'teams' => $teams,
            'categories' => $categories,
            'plans' => $plans,
        ]);
    }

    public function createFromEvent(int $eventId)
    {
        $event = $this->events->find($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/training-sessions')) {
            return $response;
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll((int) $event['team_id']);
        $plans = $this->plans->list([], 200, 'training_plans_select')['items'];

        return view('training_sessions/create', [
            'title' => 'Nova sessao (evento)',
            'teams' => $teams,
            'categories' => $categories,
            'plans' => $plans,
            'event' => $event,
        ]);
    }

    public function store()
    {
        $payload = $this->request->getPost();
        if ($this->scopedTeamIds !== [] && !empty($payload['team_id']) && !in_array((int) $payload['team_id'], $this->scopedTeamIds, true)) {
            return redirect()->back()->withInput()->with('error', 'Equipe invalida.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingSessionCreate, config('Validation')->trainingSessionCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $id = $this->sessions->create($payload, (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'training_session_created', ['training_session_id' => $id]);

        return redirect()->to('/training-sessions/' . $id)->with('success', 'Sessao criada.');
    }

    public function edit(int $id)
    {
        $session = $this->sessions->find($id);
        if (!$session) {
            return redirect()->to('/training-sessions')->with('error', 'Sessao nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $session['team_id'], '/training-sessions')) {
            return $response;
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll((int) $session['team_id']);
        $plans = $this->plans->list([], 200, 'training_plans_select')['items'];

        return view('training_sessions/edit', [
            'title' => 'Editar sessao',
            'session' => $session,
            'teams' => $teams,
            'categories' => $categories,
            'plans' => $plans,
        ]);
    }

    public function update(int $id)
    {
        $session = $this->sessions->find($id);
        if (!$session) {
            return redirect()->to('/training-sessions')->with('error', 'Sessao nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $session['team_id'], '/training-sessions')) {
            return $response;
        }

        $payload = $this->request->getPost();
        if ($this->scopedTeamIds !== [] && !empty($payload['team_id']) && !in_array((int) $payload['team_id'], $this->scopedTeamIds, true)) {
            return redirect()->back()->withInput()->with('error', 'Equipe invalida.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingSessionCreate, config('Validation')->trainingSessionCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->sessions->update($id, $payload);
        Services::audit()->log(session('user_id'), 'training_session_updated', ['training_session_id' => $id]);

        return redirect()->to('/training-sessions/' . $id)->with('success', 'Sessao atualizada.');
    }

    public function deleteConfirm(int $id)
    {
        $session = $this->sessions->find($id);
        if (!$session) {
            return redirect()->to('/training-sessions')->with('error', 'Sessao nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $session['team_id'], '/training-sessions')) {
            return $response;
        }

        return view('training_sessions/delete', ['title' => 'Excluir sessao', 'session' => $session]);
    }

    public function delete(int $id)
    {
        $session = $this->sessions->find($id);
        if (!$session) {
            return redirect()->to('/training-sessions')->with('error', 'Sessao nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $session['team_id'], '/training-sessions')) {
            return $response;
        }

        $this->sessions->delete($id);
        Services::audit()->log(session('user_id'), 'training_session_deleted', ['training_session_id' => $id]);

        return redirect()->to('/training-sessions')->with('success', 'Sessao removida.');
    }

    public function fieldMode(int $id)
    {
        $session = $this->sessions->findWithRelations($id);
        if (!$session) {
            return redirect()->to('/training-sessions')->with('error', 'Sessao nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $session['team_id'], '/training-sessions')) {
            return $response;
        }

        $athletes = $this->sessions->listAthletes($id);
        if (empty($athletes)) {
            $athletes = $this->athletes->listByCategory((int) $session['category_id']);
        }

        return view('training_sessions/field', [
            'title' => 'Modo campo',
            'session' => $session,
            'athletes' => $athletes,
        ]);
    }

    public function saveAthlete(int $sessionId)
    {
        $session = $this->sessions->find($sessionId);
        if (!$session) {
            return redirect()->back()->with('error', 'Sessao nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $session['team_id'], '/training-sessions')) {
            return $response;
        }

        $data = $this->request->getPost();
        $data['training_session_id'] = $sessionId;
        $athleteId = (int) ($data['athlete_id'] ?? 0);

        log_message('debug', 'training_sessions.saveAthlete payload: {payload}', [
            'payload' => json_encode([
                'session_id' => $sessionId,
                'athlete_id' => $athleteId,
                'attendance_status' => $data['attendance_status'] ?? null,
                'rating' => $data['rating'] ?? null,
                'training_session_id' => $data['training_session_id'] ?? null,
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingSessionAthleteCreate, config('Validation')->trainingSessionAthleteCreate_errors);

        if (!$validation->run($data)) {
            $errors = $validation->getErrors();
            log_message('error', 'training_sessions.saveAthlete validation failed: {errors}', [
                'errors' => json_encode($errors, JSON_UNESCAPED_UNICODE),
            ]);

            if (!$this->canPersistSessionAthlete($sessionId, $athleteId)) {
                return redirect()->back()->withInput()->with('errors', $errors);
            }

            log_message('warning', 'training_sessions.saveAthlete bypass validation for session={session} athlete={athlete}', [
                'session' => $sessionId,
                'athlete' => $athleteId,
            ]);
        }

        $this->sessionAthletes->createOrUpdate($data);
        return redirect()->back()->with('success', 'Registro atualizado.');
    }

    protected function canPersistSessionAthlete(int $sessionId, int $athleteId): bool
    {
        if ($sessionId <= 0 || $athleteId <= 0) {
            return false;
        }

        $db = db_connect();
        $sessionExists = $db->table('training_sessions')
            ->where('id', $sessionId)
            ->where('deleted_at', null)
            ->countAllResults();
        if ($sessionExists !== 1) {
            return false;
        }

        $athleteExists = $db->table('athletes')
            ->where('id', $athleteId)
            ->where('deleted_at', null)
            ->countAllResults();

        return $athleteExists === 1;
    }
}
