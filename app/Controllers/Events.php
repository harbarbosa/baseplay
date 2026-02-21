<?php

namespace App\Controllers;

use App\Services\EventService;
use App\Services\EventParticipantService;
use App\Services\AttendanceService;
use App\Services\TeamService;
use App\Services\CategoryService;
use App\Services\AthleteService;
use Config\Services;

class Events extends BaseController
{
    protected EventService $events;
    protected EventParticipantService $participants;
    protected AttendanceService $attendance;
    protected TeamService $teams;
    protected CategoryService $categories;
    protected AthleteService $athletes;

    public function __construct()
    {
        $this->events = new EventService();
        $this->participants = new EventParticipantService();
        $this->attendance = new AttendanceService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
        $this->athletes = new AthleteService();
    }

    public function index()
    {
        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'type' => $this->request->getGet('type'),
            'status' => $this->request->getGet('status'),
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
        ];

        $filters['team_id'] = $this->pickScopedTeamId((int) ($filters['team_id'] ?? 0));

        $viewMode = $this->request->getGet('view') ?: 'list';
        $result = $this->events->list($filters, 20, 'events');

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam(!empty($filters['team_id']) ? (int) $filters['team_id'] : null, true);

        $eventsByDate = [];
        foreach ($result['items'] as $event) {
            $dateKey = substr($event['start_datetime'], 0, 10);
            $eventsByDate[$dateKey][] = $event;
        }

        return view('events/index', [
            'title' => 'Agenda',
            'events' => $result['items'],
            'eventsByDate' => $eventsByDate,
            'pager' => $result['pager'],
            'filters' => $filters,
            'teams' => $teams,
            'categories' => $categories,
            'types' => $this->eventTypes(),
            'viewMode' => $viewMode,
        ]);
    }

    public function create()
    {
        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));
        if ($this->scopedTeamIds !== [] && !$teamId) {
            return redirect()->to('/events')->with('error', 'Acesso negado.');
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true);

        return view('events/create', [
            'title' => 'Novo evento',
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
            'types' => $this->eventTypes(),
        ]);
    }

    public function store()
    {
        $payload = $this->request->getPost();
        $payload['start_datetime'] = $this->normalizeDateTime($payload['start_datetime'] ?? null);
        $payload['end_datetime'] = $this->normalizeDateTime($payload['end_datetime'] ?? null);

        if ($this->scopedTeamIds !== []) {
            $payload['team_id'] = $this->pickScopedTeamId((int) ($payload['team_id'] ?? 0));
        }
        if ($this->scopedTeamIds !== [] && empty($payload['team_id'])) {
            return redirect()->back()->withInput()->with('error', 'Equipe invalida.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->eventCreate, config('Validation')->eventCreate_errors);

        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['start_datetime'] ?? null, $payload['end_datetime'] ?? null)) {
            return redirect()->back()->withInput()->with('error', 'A data final deve ser maior ou igual a data inicial.');
        }

        $eventId = $this->events->create($payload, (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'event_created', ['event_id' => $eventId]);

        return redirect()->to('/events/' . $eventId)->with('success', 'Evento criado com sucesso.');
    }

    public function show(int $id)
    {
        $event = $this->events->findWithRelations($id);
        if (!$event) {
            return redirect()->to('/events')->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        $participants = $this->participants->listByEvent($id);
        $attendance = $this->attendance->listByEvent($id);
        $attendanceMap = [];
        foreach ($attendance as $item) {
            $attendanceMap[$item['athlete_id']] = $item;
        }

        $athletes = $this->athletes->listByCategory((int) $event['category_id']);

        return view('events/show', [
            'title' => 'Detalhe do evento',
            'event' => $event,
            'participants' => $participants,
            'attendanceMap' => $attendanceMap,
            'athletes' => $athletes,
            'types' => $this->eventTypes(),
        ]);
    }

    public function edit(int $id)
    {
        $event = $this->events->find($id);
        if (!$event) {
            return redirect()->to('/events')->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));
        if ($teamId <= 0) {
            $teamId = (int) $event['team_id'];
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true);

        return view('events/edit', [
            'title' => 'Editar evento',
            'event' => $event,
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
            'types' => $this->eventTypes(),
        ]);
    }

    public function update(int $id)
    {
        $event = $this->events->find($id);
        if (!$event) {
            return redirect()->to('/events')->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        $payload = $this->request->getPost();
        $payload['start_datetime'] = $this->normalizeDateTime($payload['start_datetime'] ?? null);
        $payload['end_datetime'] = $this->normalizeDateTime($payload['end_datetime'] ?? null);

        if ($this->scopedTeamIds !== []) {
            $payload['team_id'] = $this->pickScopedTeamId((int) ($payload['team_id'] ?? 0));
        }
        if ($this->scopedTeamIds !== [] && empty($payload['team_id'])) {
            return redirect()->back()->withInput()->with('error', 'Equipe invalida.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->eventUpdate, config('Validation')->eventCreate_errors);

        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['start_datetime'] ?? null, $payload['end_datetime'] ?? null)) {
            return redirect()->back()->withInput()->with('error', 'A data final deve ser maior ou igual a data inicial.');
        }

        $this->events->update($id, $payload, (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'event_updated', ['event_id' => $id]);

        return redirect()->to('/events/' . $id)->with('success', 'Evento atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $event = $this->events->find($id);
        if (!$event) {
            return redirect()->to('/events')->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        return view('events/delete', ['title' => 'Excluir evento', 'event' => $event]);
    }

    public function delete(int $id)
    {
        $event = $this->events->find($id);
        if (!$event) {
            return redirect()->to('/events')->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        $this->events->delete($id);
        Services::audit()->log(session('user_id'), 'event_deleted', ['event_id' => $id]);

        return redirect()->to('/events')->with('success', 'Evento removido.');
    }

    public function addParticipant(int $eventId)
    {
        $event = $this->events->find($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        $athleteIds = $this->request->getPost('athlete_ids');
        if (is_array($athleteIds) && $athleteIds !== []) {
            $normalized = array_map(static fn($id) => (int) $id, $athleteIds);
            $count = $this->participants->addParticipantsBulk($eventId, $normalized);
            return redirect()->back()->with('success', $count . ' atletas convocados.');
        }

        $athleteId = (int) $this->request->getPost('athlete_id');
        if ($athleteId <= 0) {
            return redirect()->back()->with('error', 'Selecione um atleta.');
        }

        $this->participants->addParticipant($eventId, $athleteId, 'invited');
        return redirect()->back()->with('success', 'Atleta convocado.');
    }

    public function addParticipantsCategory(int $eventId)
    {
        $event = $this->events->find($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        $categoryId = (int) ($event['category_id'] ?? 0);
        if ($categoryId <= 0) {
            return redirect()->back()->with('error', 'Categoria invalida para o evento.');
        }

        $count = $this->participants->addFromCategory($eventId, $categoryId);
        return redirect()->back()->with('success', $count . ' atletas convocados.');
    }

    public function updateParticipant(int $id)
    {
        $participant = $this->participants->find($id);
        if (!$participant) {
            return redirect()->back()->with('error', 'Convocado nao encontrado.');
        }

        $event = $this->events->find((int) $participant['event_id']);
        if ($event && ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events'))) {
            return $response;
        }

        $status = $this->request->getPost('invitation_status') ?: 'invited';
        if ($status === 'confirmed' && $this->events->isCancelled((int) $participant['event_id'])) {
            return redirect()->back()->with('error', 'Nao e possivel confirmar convite em evento cancelado.');
        }

        $this->participants->update($id, $status, $this->request->getPost('notes'));
        return redirect()->back()->with('success', 'Convite atualizado.');
    }

    public function deleteParticipant(int $id)
    {
        $participant = $this->participants->find($id);
        if (!$participant) {
            return redirect()->back()->with('error', 'Convocado nao encontrado.');
        }

        $event = $this->events->find((int) $participant['event_id']);
        if ($event && ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events'))) {
            return $response;
        }

        $this->participants->delete($id);
        return redirect()->back()->with('success', 'Convocado removido.');
    }

    public function markAttendance(int $eventId)
    {
        $event = $this->events->find($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'Evento nao encontrado.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $event['team_id'], '/events')) {
            return $response;
        }

        $athleteId = (int) $this->request->getPost('athlete_id');
        $status = $this->request->getPost('status');

        if ($athleteId <= 0 || !$status) {
            return redirect()->back()->with('error', 'Dados invalidos.');
        }

        if (!$this->participants->isParticipant($eventId, $athleteId)) {
            return redirect()->back()->with('error', 'Atleta nao esta convocado para este evento.');
        }

        $this->attendance->upsert($eventId, $athleteId, $status, $this->request->getPost('notes'));
        return redirect()->back()->with('success', 'Presenca registrada.');
    }

    protected function validateDateRange(?string $start, ?string $end): bool
    {
        if (!$start || !$end) {
            return true;
        }

        return strtotime($end) >= strtotime($start);
    }

    protected function normalizeDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        return $value;
    }

    protected function eventTypes(): array
    {
        return [
            'TRAINING' => 'Treino',
            'MATCH' => 'Jogo',
            'MEETING' => 'Reuniao',
            'EVALUATION' => 'Avaliacao',
            'TRAVEL' => 'Viagem',
        ];
    }
}
