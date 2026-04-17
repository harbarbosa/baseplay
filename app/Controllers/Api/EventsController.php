<?php

namespace App\Controllers\Api;

use App\Services\AttendanceService;
use App\Services\EventParticipantService;
use App\Services\EventService;
use App\Services\MatchCallupService;
use App\Models\MatchModel;
use App\Models\MatchReportModel;
use App\Models\MatchTacticalBoardModel;
use App\Models\TrainingSessionModel;
use App\Models\TrainingPlanModel;
use App\Models\TrainingPlanBlockModel;
use App\Models\ExerciseModel;
use App\Models\ExerciseTacticalBoardModel;
use App\Models\UserModel;
use App\Models\GuardianModel;

class EventsController extends BaseApiController
{
    protected EventService $events;
    protected EventParticipantService $participants;
    protected AttendanceService $attendance;
    protected MatchCallupService $callups;
    protected MatchModel $matches;
    protected MatchReportModel $matchReports;
    protected MatchTacticalBoardModel $matchBoards;
    protected TrainingSessionModel $sessions;
    protected TrainingPlanModel $plans;
    protected TrainingPlanBlockModel $planBlocks;
    protected ExerciseModel $exercises;
    protected ExerciseTacticalBoardModel $exerciseBoards;

    public function __construct()
    {
        $this->events = new EventService();
        $this->participants = new EventParticipantService();
        $this->attendance = new AttendanceService();
        $this->callups = new MatchCallupService();
        $this->matches = new MatchModel();
        $this->matchReports = new MatchReportModel();
        $this->matchBoards = new MatchTacticalBoardModel();
        $this->sessions = new TrainingSessionModel();
        $this->plans = new TrainingPlanModel();
        $this->planBlocks = new TrainingPlanBlockModel();
        $this->exercises = new ExerciseModel();
        $this->exerciseBoards = new ExerciseTacticalBoardModel();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ])->setStatusCode($code);
    }

    protected function fail(string $message, int $code = 400, $errors = null)
    {
        return service('response')->setJSON([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ])->setStatusCode($code);
    }

    public function index()
    {
        if ($response = $this->ensurePermission('events.view')) {
            return $response;
        }

        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'type' => $this->request->getGet('type'),
            'status' => $this->request->getGet('status'),
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $user = $this->apiUser();
        if ($user) {
            $roles = $this->apiUserRoles($user);
            if (in_array('atleta', $roles, true)) {
                $dbUser = (new UserModel())->find((int) $user['id']);
                if (!empty($dbUser['athlete_id'])) {
                    $filters['athlete_id'] = (int) $dbUser['athlete_id'];
                }
            } elseif (in_array('responsavel', $roles, true)) {
                $guardian = null;
                if (!empty($user['email'])) {
                    $guardian = (new GuardianModel())
                        ->where('deleted_at', null)
                        ->where('email', $user['email'])
                        ->first();
                }
                if (!empty($guardian['id'])) {
                    $rows = db_connect()->table('athlete_guardians')
                        ->select('athlete_id')
                        ->where('guardian_id', (int) $guardian['id'])
                        ->get()
                        ->getResultArray();
                    $filters['athlete_ids'] = array_values(array_filter(array_map(
                        static fn(array $row): int => (int) $row['athlete_id'],
                        $rows
                    )));
                }
            }
        }

        $result = $this->events->list($filters, $perPage, 'events_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('events_api'),
                'pageCount' => $result['pager']->getPageCount('events_api'),
                'perPage' => $result['pager']->getPerPage('events_api'),
                'total' => $result['pager']->getTotal('events_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('events.view')) {
            return $response;
        }

        $event = $this->events->findWithRelations($id);
        if (!$event) {
            return $this->fail('Evento nao encontrado.', 404);
        }

        if (($event['type'] ?? '') === 'MATCH') {
            $match = $this->matches->where('event_id', $id)->first();
            if ($match) {
                $report = $this->matchReports->where('match_id', (int) $match['id'])->first();
                $linkedBoards = $this->matchBoards
                    ->select('match_tactical_boards.tactical_board_id, tactical_boards.title')
                    ->join('tactical_boards', 'tactical_boards.id = match_tactical_boards.tactical_board_id', 'inner')
                    ->where('match_tactical_boards.match_id', (int) $match['id'])
                    ->orderBy('tactical_boards.updated_at', 'DESC')
                    ->findAll();

                $event['match'] = [
                    'id' => (int) $match['id'],
                    'opponent_name' => $match['opponent_name'] ?? null,
                    'competition_name' => $match['competition_name'] ?? null,
                    'round_name' => $match['round_name'] ?? null,
                    'location' => $match['location'] ?? null,
                    'status' => $match['status'] ?? null,
                    'score_for' => $match['score_for'] ?? null,
                    'score_against' => $match['score_against'] ?? null,
                    'report' => [
                        'summary' => $report['summary'] ?? null,
                        'strengths' => $report['strengths'] ?? null,
                        'coach_notes' => $report['coach_notes'] ?? null,
                    ],
                    'tactical_boards' => array_map(
                        static fn(array $board): array => [
                            'id' => (int) ($board['tactical_board_id'] ?? 0),
                            'title' => $board['title'] ?? '',
                        ],
                        $linkedBoards
                    ),
                ];
            }
        }

        if (($event['type'] ?? '') === 'TRAINING') {
            $session = $this->sessions->where('event_id', $id)->first();
            if ($session) {
                $planTitle = null;
                if (!empty($session['training_plan_id'])) {
                    $plan = $this->plans->find((int) $session['training_plan_id']);
                    $planTitle = $plan['title'] ?? null;
                }

                $blocks = [];
                $exerciseIds = [];
                if (!empty($session['training_plan_id'])) {
                    $blocks = $this->planBlocks
                        ->where('training_plan_id', (int) $session['training_plan_id'])
                        ->orderBy('order_index', 'ASC')
                        ->findAll();
                    $exerciseIds = array_values(array_filter(array_map(
                        static fn(array $row): int => (int) ($row['exercise_id'] ?? 0),
                        $blocks
                    )));
                }

                $exerciseMap = [];
                if ($exerciseIds !== []) {
                    $rows = $this->exercises->whereIn('id', $exerciseIds)->findAll();
                    foreach ($rows as $row) {
                        $exerciseMap[(int) $row['id']] = $row;
                    }
                }

                $tacticalBoards = [];
                if ($exerciseIds !== []) {
                    $boardRows = $this->exerciseBoards
                        ->select('exercise_tactical_boards.exercise_id, tactical_boards.id AS board_id, tactical_boards.title')
                        ->join('tactical_boards', 'tactical_boards.id = exercise_tactical_boards.tactical_board_id', 'inner')
                        ->whereIn('exercise_tactical_boards.exercise_id', $exerciseIds)
                        ->where('tactical_boards.deleted_at', null)
                        ->findAll();
                    $seen = [];
                    foreach ($boardRows as $row) {
                        $boardId = (int) ($row['board_id'] ?? 0);
                        if ($boardId <= 0 || isset($seen[$boardId])) {
                            continue;
                        }
                        $seen[$boardId] = true;
                        $tacticalBoards[] = [
                            'id' => $boardId,
                            'title' => $row['title'] ?? '',
                        ];
                    }
                }

                $event['training'] = [
                    'id' => (int) $session['id'],
                    'title' => $session['title'] ?? null,
                    'session_date' => $session['session_date'] ?? null,
                    'start_datetime' => $session['start_datetime'] ?? null,
                    'end_datetime' => $session['end_datetime'] ?? null,
                    'location' => $session['location'] ?? null,
                    'notes' => $session['general_notes'] ?? null,
                    'plan_title' => $planTitle,
                    'blocks' => array_map(static function (array $block) use ($exerciseMap): array {
                        $exerciseId = (int) ($block['exercise_id'] ?? 0);
                        $exerciseTitle = $exerciseId > 0 && isset($exerciseMap[$exerciseId])
                            ? ($exerciseMap[$exerciseId]['title'] ?? '')
                            : '';
                        return [
                            'id' => (int) ($block['id'] ?? 0),
                            'block_type' => $block['block_type'] ?? null,
                            'title' => $block['title'] ?? null,
                            'duration_min' => $block['duration_min'] ?? null,
                            'exercise_id' => $exerciseId ?: null,
                            'exercise_title' => $exerciseTitle,
                            'instructions' => $block['instructions'] ?? null,
                            'order_index' => $block['order_index'] ?? null,
                        ];
                    }, $blocks),
                    'tactical_boards' => $tacticalBoards,
                ];
            }
        }

        return $this->ok($event);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('events.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->eventCreate, config('Validation')->eventCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validacao falhou.', 422, $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['start_datetime'] ?? null, $payload['end_datetime'] ?? null)) {
            return $this->fail('A data final deve ser maior ou igual a data inicial.', 422);
        }

        $user = $this->apiUser();
        $eventId = $this->events->create($payload, $user ? (int) $user['id'] : null);

        return $this->ok(['id' => $eventId], 'Evento criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('events.update')) {
            return $response;
        }

        $event = $this->events->find($id);
        if (!$event) {
            return $this->fail('Evento nao encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->eventUpdate, config('Validation')->eventCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validacao falhou.', 422, $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['start_datetime'] ?? null, $payload['end_datetime'] ?? null)) {
            return $this->fail('A data final deve ser maior ou igual a data inicial.', 422);
        }

        $user = $this->apiUser();
        $this->events->update($id, $payload, $user ? (int) $user['id'] : null);

        return $this->ok(['id' => $id], 'Evento atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('events.delete')) {
            return $response;
        }

        $event = $this->events->find($id);
        if (!$event) {
            return $this->fail('Evento nao encontrado.', 404);
        }

        $this->events->delete($id);

        return $this->ok(['id' => $id], 'Evento removido.');
    }

    public function participants(int $eventId)
    {
        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $roles = $this->apiUserRoles($user);
        $isAthleteOrGuardian = in_array('atleta', $roles, true)
            || in_array('responsavel', $roles, true);
        $allowed = $isAthleteOrGuardian
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'invitations.manage')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'athletes.view')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'guardians.view');

        if (!$allowed) {
            return $this->fail('Forbidden', 403);
        }

        $event = $this->events->find($eventId);
        if (!$event) {
            return $this->fail('Evento nao encontrado.', 404);
        }

        if (($event['type'] ?? '') === 'MATCH') {
            $match = $this->matches->where('event_id', $eventId)->first();
            if (!$match) {
                return $this->ok([]);
            }

            $callups = $this->callups->listByMatch((int) $match['id']);
            $items = array_map(static function (array $callup) use ($eventId): array {
                return [
                    'id' => (int) ($callup['id'] ?? 0),
                    'event_id' => (int) $eventId,
                    'athlete_id' => (int) ($callup['athlete_id'] ?? 0),
                    'first_name' => $callup['first_name'] ?? '',
                    'last_name' => $callup['last_name'] ?? '',
                    'invitation_status' => $callup['callup_status'] ?? 'invited',
                ];
            }, $callups);

            return $this->ok($items);
        }

        $items = $this->participants->listByEvent($eventId);
        return $this->ok($items);
    }

    public function confirm(int $eventId)
    {
        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $allowed = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'invitations.manage')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'athletes.view')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'guardians.view');

        if (!$allowed) {
            return $this->fail('Forbidden', 403);
        }

        $event = $this->events->find($eventId);
        if (!$event) {
            return $this->fail('Evento nao encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $athleteId = (int) ($payload['athlete_id'] ?? 0);
        $status = $payload['invitation_status'] ?? 'pending';

        if ($athleteId <= 0) {
            return $this->fail('Atleta invalido.', 422);
        }

        if ($status === 'confirmed' && $event['status'] === 'cancelled') {
            return $this->fail('Nao e possivel confirmar convite em evento cancelado.', 422);
        }

        $participantId = $this->participants->addParticipant($eventId, $athleteId, $status);

        return $this->ok(['id' => $participantId], 'Convite atualizado.');
    }

    public function attendance(int $eventId)
    {
        if ($response = $this->ensurePermission('attendance.manage')) {
            return $response;
        }

        $event = $this->events->find($eventId);
        if (!$event) {
            return $this->fail('Evento nao encontrado.', 404);
        }

        $items = $this->attendance->listByEvent($eventId);

        return $this->ok($items);
    }

    protected function validateDateRange(?string $start, ?string $end): bool
    {
        if (!$start || !$end) {
            return true;
        }

        return strtotime($end) >= strtotime($start);
    }
}
