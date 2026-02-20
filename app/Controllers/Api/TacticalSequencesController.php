<?php

namespace App\Controllers\Api;

use App\Services\TacticalBoardService;
use App\Services\TacticalSequenceService;

class TacticalSequencesController extends BaseApiController
{
    protected TacticalBoardService $boards;
    protected TacticalSequenceService $sequences;

    public function __construct()
    {
        $this->boards = new TacticalBoardService();
        $this->sequences = new TacticalSequenceService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
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

    public function index(int $boardId)
    {
        if ($response = $this->ensurePermission('tactical_board.view')) {
            return $response;
        }

        $board = $this->boards->find($boardId);
        if (!$board) {
            return $this->fail('Prancheta não encontrada.', 404);
        }

        return $this->ok($this->sequences->listByBoard($boardId));
    }

    public function store(int $boardId)
    {
        if ($response = $this->ensurePermission('tactical_sequence.manage')) {
            return $response;
        }

        $board = $this->boards->find($boardId);
        if (!$board) {
            return $this->fail('Prancheta não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return $this->fail('Título obrigatório.', 422, ['title' => 'required']);
        }

        $user = $this->apiUser();
        $id = $this->sequences->create($boardId, $payload, $user ? (int) $user['id'] : 0);
        return $this->ok(['id' => $id], 'Sequência criada.', 201);
    }

    public function show(int $sequenceId)
    {
        if ($response = $this->ensurePermission('tactical_board.view')) {
            return $response;
        }

        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->fail('Sequência não encontrada.', 404);
        }

        return $this->ok($sequence);
    }

    public function update(int $sequenceId)
    {
        if ($response = $this->ensurePermission('tactical_sequence.manage')) {
            return $response;
        }

        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->fail('Sequência não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $title = trim((string) ($payload['title'] ?? ''));
        if (array_key_exists('title', $payload) && $title === '') {
            return $this->fail('Título obrigatório.', 422, ['title' => 'required']);
        }

        $this->sequences->update($sequenceId, $payload);
        return $this->ok(['id' => $sequenceId], 'Sequência atualizada.');
    }

    public function delete(int $sequenceId)
    {
        if ($response = $this->ensurePermission('tactical_sequence.manage')) {
            return $response;
        }

        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->fail('Sequência não encontrada.', 404);
        }

        $this->sequences->delete($sequenceId);
        return $this->ok(['id' => $sequenceId], 'Sequência removida.');
    }
}

