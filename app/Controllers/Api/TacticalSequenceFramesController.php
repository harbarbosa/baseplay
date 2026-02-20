<?php

namespace App\Controllers\Api;

use App\Services\TacticalSequenceFrameService;
use App\Services\TacticalSequenceService;

class TacticalSequenceFramesController extends BaseApiController
{
    protected TacticalSequenceService $sequences;
    protected TacticalSequenceFrameService $frames;

    public function __construct()
    {
        $this->sequences = new TacticalSequenceService();
        $this->frames = new TacticalSequenceFrameService();
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

    public function index(int $sequenceId)
    {
        if ($response = $this->ensurePermission('tactical_board.view')) {
            return $response;
        }

        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->fail('Sequência não encontrada.', 404);
        }

        return $this->ok($this->frames->listBySequence($sequenceId));
    }

    public function store(int $sequenceId)
    {
        if ($response = $this->ensurePermission('tactical_sequence.manage')) {
            return $response;
        }

        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->fail('Sequência não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $id = $this->frames->create($sequenceId, $payload);
        if ($id <= 0) {
            return $this->fail('Frame inválido.', 422);
        }

        return $this->ok(['id' => $id], 'Frame criado.', 201);
    }

    public function update(int $frameId)
    {
        if ($response = $this->ensurePermission('tactical_sequence.manage')) {
            return $response;
        }

        $frame = $this->frames->find($frameId);
        if (!$frame) {
            return $this->fail('Frame não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $ok = $this->frames->update($frameId, $payload);
        if (!$ok) {
            return $this->fail('Dados de frame inválidos.', 422);
        }

        return $this->ok(['id' => $frameId], 'Frame atualizado.');
    }

    public function delete(int $frameId)
    {
        if ($response = $this->ensurePermission('tactical_sequence.manage')) {
            return $response;
        }

        $frame = $this->frames->find($frameId);
        if (!$frame) {
            return $this->fail('Frame não encontrado.', 404);
        }

        $ok = $this->frames->delete($frameId);
        if (!$ok) {
            return $this->fail('Falha ao excluir frame.', 400);
        }

        return $this->ok(['id' => $frameId], 'Frame removido.');
    }

    public function saveAll(int $sequenceId)
    {
        if ($response = $this->ensurePermission('tactical_sequence.manage')) {
            return $response;
        }

        $sequence = $this->sequences->find($sequenceId);
        if (!$sequence) {
            return $this->fail('Sequência não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $frames = $payload['frames'] ?? [];
        $fps = $payload['fps'] ?? ($sequence['fps'] ?? 2);

        if (!is_array($frames) || count($frames) < 1) {
            return $this->fail('Frames inválidos.', 422, ['frames' => 'required']);
        }

        $ok = $this->frames->saveAll($sequenceId, $fps, $frames);
        if (!$ok) {
            return $this->fail('Falha ao salvar sequência.', 400);
        }

        return $this->ok(['sequence_id' => $sequenceId], 'Sequência salva.');
    }
}

