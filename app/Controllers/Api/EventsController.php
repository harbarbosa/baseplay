<?php

namespace App\Controllers\Api;

use App\Services\AttendanceService;
use App\Services\EventParticipantService;
use App\Services\EventService;

class EventsController extends BaseApiController
{
    protected EventService $events;
    protected EventParticipantService $participants;
    protected AttendanceService $attendance;

    public function __construct()
    {
        $this->events = new EventService();
        $this->participants = new EventParticipantService();
        $this->attendance = new AttendanceService();
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
        if ($response = $this->ensurePermission('invitations.manage')) {
            return $response;
        }

        $event = $this->events->find($eventId);
        if (!$event) {
            return $this->fail('Evento nao encontrado.', 404);
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