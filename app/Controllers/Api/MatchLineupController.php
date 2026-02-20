<?php

namespace App\Controllers\Api;

use App\Services\MatchLineupService;
use App\Services\MatchCallupService;

class MatchLineupController extends BaseApiController
{
    protected MatchLineupService $lineups;
    protected MatchCallupService $callups;

    public function __construct()
    {
        $this->lineups = new MatchLineupService();
        $this->callups = new MatchCallupService();
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
        if ($response = $this->ensurePermission('match_lineup.manage')) {
            return $response;
        }

        return $this->ok($this->lineups->listByMatch($matchId));
    }

    public function store(int $matchId)
    {
        if ($response = $this->ensurePermission('match_lineup.manage')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $athleteId = (int) ($payload['athlete_id'] ?? 0);

        if ($athleteId <= 0 || !$this->callups->isCalledUp($matchId, $athleteId)) {
            return $this->fail('Atleta não convocado.', 422);
        }

        $id = $this->lineups->upsert($matchId, $athleteId, $payload);
        return $this->ok(['id' => $id], 'Escalação atualizada.');
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('match_lineup.manage')) {
            return $response;
        }

        $lineup = $this->lineups->find($id);
        if (!$lineup) {
            return $this->fail('Escalação não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $this->lineups->update($id, $payload);
        return $this->ok(['id' => $id], 'Escalação atualizada.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('match_lineup.manage')) {
            return $response;
        }

        $lineup = $this->lineups->find($id);
        if (!$lineup) {
            return $this->fail('Escalação não encontrada.', 404);
        }

        $this->lineups->delete($id);
        return $this->ok(['id' => $id], 'Escalação removida.');
    }
}