<?php

namespace App\Controllers\Api;

use App\Services\MatchCallupService;
use App\Services\MatchService;

class MatchCallupsController extends BaseApiController
{
    protected MatchCallupService $callups;
    protected MatchService $matches;

    public function __construct()
    {
        $this->callups = new MatchCallupService();
        $this->matches = new MatchService();
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
        if ($response = $this->ensurePermission('matches.view')) {
            return $response;
        }

        $match = $this->matches->find($matchId);
        if (!$match) {
            return $this->fail('Jogo não encontrado.', 404);
        }

        return $this->ok($this->callups->listByMatch($matchId));
    }

    public function store(int $matchId)
    {
        if ($response = $this->ensurePermission('matches.update')) {
            return $response;
        }

        $match = $this->matches->find($matchId);
        if (!$match) {
            return $this->fail('Jogo não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $ids = $payload['athlete_ids']  [];
        $single = $payload['athlete_id'] ?? null;

        if ($single) {
            $ids[] = $single;
        }

        $count = $this->callups->addParticipantsBulk($matchId, $ids);
        return $this->ok(['count' => $count], 'Convocados adicionados.');
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('matches.update')) {
            return $response;
        }

        $callup = $this->callups->find($id);
        if (!$callup) {
            return $this->fail('Convocação não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $status = $payload['callup_status'] ?? $callup['callup_status'];
        $isStarting = (int) ($payload['is_starting'] ?? $callup['is_starting']);

        $this->callups->update($id, $status, $isStarting);
        return $this->ok(['id' => $id], 'Convocação atualizada.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('matches.update')) {
            return $response;
        }

        $callup = $this->callups->find($id);
        if (!$callup) {
            return $this->fail('Convocação não encontrada.', 404);
        }

        $this->callups->delete($id);
        return $this->ok(['id' => $id], 'Convocação removida.');
    }
}