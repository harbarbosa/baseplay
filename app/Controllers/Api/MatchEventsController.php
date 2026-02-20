<?php

namespace App\Controllers\Api;

use App\Services\MatchEventService;

class MatchEventsController extends BaseApiController
{
    protected MatchEventService $events;

    public function __construct()
    {
        $this->events = new MatchEventService();
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

    public function index(int $matchId)
    {
        if ($response = $this->ensurePermission('match_stats.manage')) {
            return $response;
        }

        return $this->ok($this->events->listByMatch($matchId));
    }

    public function store(int $matchId)
    {
        if ($response = $this->ensurePermission('match_stats.manage')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($payload['event_type'])) {
            return $this->fail('Informe o tipo do evento.', 422);
        }

        if (($payload['event_type'] ?? '') === 'goal' && empty($payload['athlete_id'])) {
            return $this->fail('Informe o atleta para gol.', 422);
        }

        $id = $this->events->create($matchId, $payload);
        return $this->ok(['id' => $id], 'Evento criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('match_stats.manage')) {
            return $response;
        }

        $event = $this->events->find($id);
        if (!$event) {
            return $this->fail('Evento não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $this->events->update($id, $payload);
        return $this->ok(['id' => $id], 'Evento atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('match_stats.manage')) {
            return $response;
        }

        $event = $this->events->find($id);
        if (!$event) {
            return $this->fail('Evento não encontrado.', 404);
        }

        $this->events->delete($id);
        return $this->ok(['id' => $id], 'Evento removido.');
    }
}