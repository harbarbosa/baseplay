<?php

namespace App\Controllers;

use App\Services\EventService;
use App\Services\EventParticipantService;
use App\Services\AttendanceService;
use App\Services\TeamService;
use App\Services\CategoryService;
use App\Services\AthleteService;
use App\Services\EventRecipientService;
use App\Models\RoleModel;
use Config\Services;

class Events extends BaseController
{
    protected EventService $events;
    protected EventParticipantService $participants;
    protected AttendanceService $attendance;
    protected TeamService $teams;
    protected CategoryService $categories;
    protected AthleteService $athletes;
    protected EventRecipientService $recipients;

    public function __construct()
    {
        $this->events = new EventService();
        $this->participants = new EventParticipantService();
        $this->attendance = new AttendanceService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
        $this->athletes = new AthleteService();
        $this->recipients = new EventRecipientService();
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
        if (empty($filters['type'])) {
            $filters['exclude_types'] = ['TRAINING', 'MATCH'];
        }
        $categoryId = (int) ($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $filters['category_id'] = $this->pickScopedCategoryId($categoryId);
        }
        if ($this->scopedCategoryIds !== []) {
            $filters['category_ids'] = $this->scopedCategoryIds;
        }

        $viewMode = $this->request->getGet('view') ?: 'list';
        $result = $this->events->list($filters, 20, 'events');

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam(!empty($filters['team_id']) ? (int) $filters['team_id'] : null, true, $this->scopedCategoryIds);

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
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true, $this->scopedCategoryIds);
        $athleteTeamIds = $teamId > 0 ? [$teamId] : ($this->scopedTeamIds !== [] ? $this->scopedTeamIds : []);
        $athletes = $this->athletes->listAllWithRelations($athleteTeamIds, $this->scopedCategoryIds);
        $guardians = $this->listGuardiansForEvent($teamId);
        $staff = $this->listStaffForEvent($teamId);

        return view('events/create', [
            'title' => 'Novo evento',
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
            'types' => $this->eventTypes(),
            'athletes' => $athletes,
            'guardians' => $guardians,
            'staff' => $staff,
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
        if ($this->scopedCategoryIds !== []) {
            $categoryId = (int) ($payload['category_id'] ?? 0);
            if (!in_array($categoryId, $this->scopedCategoryIds, true)) {
                return redirect()->back()->withInput()->with('error', 'Categoria fora do seu escopo.');
            }
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

        $allCategory = (int) ($this->request->getPost('participants_all_category') ?? 0) === 1;
        $allTeam = (int) ($this->request->getPost('participants_all_team') ?? 0) === 1;
        $selectedAthletes = $this->request->getPost('participant_athlete_ids');
        if (!is_array($selectedAthletes)) {
            $selectedAthletes = $selectedAthletes ? [$selectedAthletes] : [];
        }
        $selectedAthletes = array_values(array_filter(array_map('intval', $selectedAthletes)));

        if ($eventId > 0) {
            $categoryId = (int) ($payload['category_id'] ?? 0);
            $teamId = (int) ($payload['team_id'] ?? 0);
            $scopeCategoryIds = $this->scopedCategoryIds !== [] ? $this->scopedCategoryIds : [];

            if ($allCategory && $categoryId > 0) {
                $this->participants->addFromCategory($eventId, $categoryId);
            }

            if ($allTeam && $teamId > 0) {
                $this->participants->addFromTeam($eventId, $teamId, $scopeCategoryIds);
            }

            if ($selectedAthletes !== []) {
                $allowedAthletes = $this->athletes->listAllWithRelations(
                    $teamId > 0 ? [$teamId] : [],
                    $scopeCategoryIds !== [] ? $scopeCategoryIds : ($categoryId > 0 ? [$categoryId] : [])
                );
                $allowedIds = array_map('intval', array_column($allowedAthletes, 'id'));
                $invalid = array_diff($selectedAthletes, $allowedIds);
                if ($invalid !== []) {
                    return redirect()->back()->withInput()->with('error', 'Existem atletas selecionados fora do escopo.');
                }

                $this->participants->addParticipantsBulk($eventId, $selectedAthletes);
            }

            $allGuardiansCategory = (int) ($this->request->getPost('participants_guardians_all_category') ?? 0) === 1;
            $allGuardiansTeam = (int) ($this->request->getPost('participants_guardians_all_team') ?? 0) === 1;
            $selectedGuardians = $this->request->getPost('participant_guardian_ids');
            if (!is_array($selectedGuardians)) {
                $selectedGuardians = $selectedGuardians ? [$selectedGuardians] : [];
            }
            $selectedGuardians = array_values(array_filter(array_map('intval', $selectedGuardians)));

            if ($allGuardiansCategory && $categoryId > 0) {
                $this->recipients->addGuardiansByCategory($eventId, $categoryId);
            }
            if ($allGuardiansTeam && $teamId > 0) {
                $this->recipients->addGuardiansByTeam($eventId, $teamId, $scopeCategoryIds);
            }
            if ($selectedGuardians !== []) {
                $this->recipients->addRecipientsBulk($eventId, 'guardian', $selectedGuardians);
            }

            $allStaffTeam = (int) ($this->request->getPost('participants_staff_all_team') ?? 0) === 1;
            $selectedStaff = $this->request->getPost('participant_staff_ids');
            if (!is_array($selectedStaff)) {
                $selectedStaff = $selectedStaff ? [$selectedStaff] : [];
            }
            $selectedStaff = array_values(array_filter(array_map('intval', $selectedStaff)));

            if ($allStaffTeam && $teamId > 0) {
                $this->recipients->addStaffByTeam($eventId, $teamId, $this->staffRoleNames());
            }
            if ($selectedStaff !== []) {
                $this->recipients->addRecipientsBulk($eventId, 'staff', $selectedStaff);
            }
        }

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
        if ($response = $this->denyIfCategoryForbidden((int) ($event['category_id'] ?? 0), '/events')) {
            return $response;
        }

        return view('events/show', [
            'title' => 'Detalhe do evento',
            'event' => $event,
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
        if ($response = $this->denyIfCategoryForbidden((int) ($event['category_id'] ?? 0), '/events')) {
            return $response;
        }

        $teamId = $this->pickScopedTeamId((int) $this->request->getGet('team_id'));
        if ($teamId <= 0) {
            $teamId = (int) $event['team_id'];
        }

        $teamFilters = $this->scopedTeamIds !== [] ? ['ids' => $this->scopedTeamIds] : [];
        $teams = $this->teams->list($teamFilters, 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true, $this->scopedCategoryIds);
        $athleteTeamIds = $teamId > 0 ? [$teamId] : ($this->scopedTeamIds !== [] ? $this->scopedTeamIds : []);
        $athletes = $this->athletes->listAllWithRelations($athleteTeamIds, $this->scopedCategoryIds);
        $guardians = $this->listGuardiansForEvent($teamId);
        $staff = $this->listStaffForEvent($teamId);
        $selectedAthleteIds = array_map('strval', array_column($this->participants->listByEvent($id), 'athlete_id'));
        $recipientRows = $this->recipients->listByEvent($id);
        $selectedGuardianIds = array_map('strval', array_column(array_filter($recipientRows, static function ($row) {
            return ($row['recipient_type'] ?? '') === 'guardian';
        }), 'recipient_id'));
        $selectedStaffIds = array_map('strval', array_column(array_filter($recipientRows, static function ($row) {
            return ($row['recipient_type'] ?? '') === 'staff';
        }), 'recipient_id'));

        return view('events/edit', [
            'title' => 'Editar evento',
            'event' => $event,
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
            'types' => $this->eventTypes(),
            'athletes' => $athletes,
            'guardians' => $guardians,
            'staff' => $staff,
            'selectedAthleteIds' => $selectedAthleteIds,
            'selectedGuardianIds' => $selectedGuardianIds,
            'selectedStaffIds' => $selectedStaffIds,
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
        if ($this->scopedCategoryIds !== []) {
            $categoryId = (int) ($payload['category_id'] ?? 0);
            if (!in_array($categoryId, $this->scopedCategoryIds, true)) {
                return redirect()->back()->withInput()->with('error', 'Categoria fora do seu escopo.');
            }
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

        $eventId = $id;
        $this->participants->clearByEvent($eventId);
        $this->recipients->clearByEvent($eventId);

        $allCategory = (int) ($this->request->getPost('participants_all_category') ?? 0) === 1;
        $allTeam = (int) ($this->request->getPost('participants_all_team') ?? 0) === 1;
        $selectedAthletes = $this->request->getPost('participant_athlete_ids');
        if (!is_array($selectedAthletes)) {
            $selectedAthletes = $selectedAthletes ? [$selectedAthletes] : [];
        }
        $selectedAthletes = array_values(array_filter(array_map('intval', $selectedAthletes)));

        if ($eventId > 0) {
            $categoryId = (int) ($payload['category_id'] ?? 0);
            $teamId = (int) ($payload['team_id'] ?? 0);
            $scopeCategoryIds = $this->scopedCategoryIds !== [] ? $this->scopedCategoryIds : [];

            if ($allCategory && $categoryId > 0) {
                $this->participants->addFromCategory($eventId, $categoryId);
            }

            if ($allTeam && $teamId > 0) {
                $this->participants->addFromTeam($eventId, $teamId, $scopeCategoryIds);
            }

            if ($selectedAthletes !== []) {
                $allowedAthletes = $this->athletes->listAllWithRelations(
                    $teamId > 0 ? [$teamId] : [],
                    $scopeCategoryIds !== [] ? $scopeCategoryIds : ($categoryId > 0 ? [$categoryId] : [])
                );
                $allowedIds = array_map('intval', array_column($allowedAthletes, 'id'));
                $invalid = array_diff($selectedAthletes, $allowedIds);
                if ($invalid !== []) {
                    return redirect()->back()->withInput()->with('error', 'Existem atletas selecionados fora do escopo.');
                }

                $this->participants->addParticipantsBulk($eventId, $selectedAthletes);
            }

            $allGuardiansCategory = (int) ($this->request->getPost('participants_guardians_all_category') ?? 0) === 1;
            $allGuardiansTeam = (int) ($this->request->getPost('participants_guardians_all_team') ?? 0) === 1;
            $selectedGuardians = $this->request->getPost('participant_guardian_ids');
            if (!is_array($selectedGuardians)) {
                $selectedGuardians = $selectedGuardians ? [$selectedGuardians] : [];
            }
            $selectedGuardians = array_values(array_filter(array_map('intval', $selectedGuardians)));

            if ($allGuardiansCategory && $categoryId > 0) {
                $this->recipients->addGuardiansByCategory($eventId, $categoryId);
            }
            if ($allGuardiansTeam && $teamId > 0) {
                $this->recipients->addGuardiansByTeam($eventId, $teamId, $scopeCategoryIds);
            }
            if ($selectedGuardians !== []) {
                $this->recipients->addRecipientsBulk($eventId, 'guardian', $selectedGuardians);
            }

            $allStaffTeam = (int) ($this->request->getPost('participants_staff_all_team') ?? 0) === 1;
            $selectedStaff = $this->request->getPost('participant_staff_ids');
            if (!is_array($selectedStaff)) {
                $selectedStaff = $selectedStaff ? [$selectedStaff] : [];
            }
            $selectedStaff = array_values(array_filter(array_map('intval', $selectedStaff)));

            if ($allStaffTeam && $teamId > 0) {
                $this->recipients->addStaffByTeam($eventId, $teamId, $this->staffRoleNames());
            }
            if ($selectedStaff !== []) {
                $this->recipients->addRecipientsBulk($eventId, 'staff', $selectedStaff);
            }
        }

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
            'MEETING' => 'ReuniÃ£o',
            'EVALUATION' => 'AvaliaÃ§Ã£o',
            'TRAVEL' => 'Viagem',
        ];
    }

    protected function listGuardiansForEvent(int $teamId): array
    {
        $builder = db_connect()->table('guardians g')
            ->select('g.id, g.full_name, g.email, g.phone, MIN(c.team_id) AS team_id')
            ->join('athlete_guardians ag', 'ag.guardian_id = g.id', 'left')
            ->join('athletes a', 'a.id = ag.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('g.deleted_at', null)
            ->groupBy('g.id');

        if ($teamId > 0) {
            $builder->where('c.team_id', $teamId);
        } elseif ($this->scopedTeamIds !== []) {
            $builder->whereIn('c.team_id', $this->scopedTeamIds);
        }

        if ($this->scopedCategoryIds !== []) {
            $builder->whereIn('c.id', $this->scopedCategoryIds);
        }

        return $builder->orderBy('g.full_name', 'ASC')->get()->getResultArray();
    }

    protected function listStaffForEvent(int $teamId): array
    {
        $roleNames = $this->staffRoleNames();
        $roleIds = [];
        if ($roleNames !== []) {
            $roles = (new RoleModel())->whereIn('name', $roleNames)->findAll();
            $roleIds = array_map('intval', array_column($roles, 'id'));
        }

        if ($roleIds === []) {
            return [];
        }

        $builder = db_connect()->table('users u')
            ->select('u.id, u.name, u.email, MIN(utl.team_id) AS team_id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'inner')
            ->join('user_team_links utl', 'utl.user_id = u.id', 'left')
            ->where('u.deleted_at', null)
            ->whereIn('ur.role_id', $roleIds)
            ->groupBy('u.id');

        if ($teamId > 0) {
            $builder->where('utl.team_id', $teamId);
        } elseif ($this->scopedTeamIds !== []) {
            $builder->whereIn('utl.team_id', $this->scopedTeamIds);
        }

        return $builder->orderBy('u.name', 'ASC')->get()->getResultArray();
    }

    protected function staffRoleNames(): array
    {
        return ['treinador', 'auxiliar', 'preparador'];
    }
}
