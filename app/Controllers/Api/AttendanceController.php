<?php

namespace App\Controllers\Api;

use App\Services\AttendanceService;
use App\Services\EventParticipantService;
use App\Services\EventService;

class AttendanceController extends BaseApiController
{
    protected AttendanceService $attendance;
    protected EventParticipantService $participants;
    protected EventService $events;

    public function __construct()
    {
        $this->attendance = new AttendanceService();
        $this->participants = new EventParticipantService();
        $this->events = new EventService();
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

    public function index(int $eventId)
    {
        if ($response = $this->ensurePermission('attendance.manage')) {
            return $response;
        }

        $event = $this->events->find($eventId);
        if (!$event) {
            return $this->fail('Evento nf¯,¿,½o encontrado.', 404);
        }

        $items = $this->attendance->listByEvent($eventId);
        return $this->ok($items);
    }

    public function store(int $eventId)
    {
        if ($response = $this->ensurePermission('attendance.manage')) {
            return $response;
        }

        $event = $this->events->find($eventId);
        if (!$event) {
            return $this->fail('Evento nf¯,¿,½o encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $payload['event_id'] = $eventId;
        $validation = service('validation');
        $validation->setRules(config('Validation')->attendanceCreate);

        if (!$validation->run($payload)) {
            return $this->fail('Validaf¯,¿,½f¯,¿,½o falhou.', 422, $validation->getErrors());
        }

        $athleteId = (int) $payload['athlete_id'];
        if (!$this->participants->isParticipant($eventId, $athleteId)) {
            return $this->fail('Atleta nf¯,¿,½o estf¯,¿,½ convocado para este evento.', 422);
        }

        $attendanceId = $this->attendance->upsert($eventId, $athleteId, $payload['status'], $payload['notes'] ?? null);
        return $this->ok(['id' => $attendanceId], 'Presenf¯,¿,½a registrada.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('attendance.manage')) {
            return $response;
        }

        $record = $this->attendance->find($id);
        if (!$record) {
            return $this->fail('Presenf¯,¿,½a nf¯,¿,½o encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $status = $payload['status'] ?? null;
        if (!$status || !in_array($status, ['present', 'late', 'absent', 'justified'], true)) {
            return $this->fail('Status invf¯,¿,½lido.', 422);
        }

        $this->attendance->update($id, $status, $payload['notes'] ?? null);
        return $this->ok(['id' => $id], 'Presenf¯,¿,½a atualizada.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('attendance.manage')) {
            return $response;
        }

        $record = $this->attendance->find($id);
        if (!$record) {
            return $this->fail('Presenf¯,¿,½a nf¯,¿,½o encontrada.', 404);
        }

        $this->attendance->delete($id);
        return $this->ok(['id' => $id], 'Presenf¯,¿,½a removida.');
    }
}
