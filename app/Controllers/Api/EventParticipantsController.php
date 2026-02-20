<?php

namespace App\Controllers\Api;

use App\Services\EventParticipantService;
use App\Services\EventService;

class EventParticipantsController extends BaseApiController
{
    protected EventParticipantService $participants;
    protected EventService $events;

    public function __construct()
    {
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

    public function store(int $eventId)
    {
        if ($response = $this->ensurePermission('invitations.manage')) {
            return $response;
        }

        $event = $this->events->find($eventId);
        if (!$event) {
            return $this->fail('Evento nf¯,¿,½o encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $athleteIds = $payload['athlete_ids'];
        $athleteId = (int) ($payload['athlete_id'] ?? 0);

        if ($athleteId > 0) {
            $athleteIds[] = $athleteId;
        }

        if (!$athleteIds) {
            return $this->fail('Informe atletas para convocaf¯,¿,½f¯,¿,½o.', 422);
        }

        $count = $this->participants->addParticipantsBulk($eventId, $athleteIds);
        return $this->ok(['count' => $count], 'Convocados adicionados.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('invitations.manage')) {
            return $response;
        }

        $participant = $this->participants->find($id);
        if (!$participant) {
            return $this->fail('Convocado nf¯,¿,½o encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $status = $payload['invitation_status'] ?? 'invited';
        $notes = $payload['notes'] ?? null;

        $event = $this->events->find((int) $participant['event_id']);
        if ($status === 'confirmed' && $event && $event['status'] === 'cancelled') {
            return $this->fail('Nf¯,¿,½o f¯,¿,½ possf¯,¿,½vel confirmar convite em evento cancelado.', 422);
        }

        $this->participants->update($id, $status, $notes);
        return $this->ok(['id' => $id], 'Convocado atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('invitations.manage')) {
            return $response;
        }

        $participant = $this->participants->find($id);
        if (!$participant) {
            return $this->fail('Convocado nf¯,¿,½o encontrado.', 404);
        }

        $this->participants->delete($id);
        return $this->ok(['id' => $id], 'Convocado removido.');
    }
}
