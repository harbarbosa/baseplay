<?php

namespace App\Controllers\Api;

use App\Services\MatchAttachmentService;

class MatchAttachmentsController extends BaseApiController
{
    protected MatchAttachmentService $attachments;

    public function __construct()
    {
        $this->attachments = new MatchAttachmentService();
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
        if ($response = $this->ensurePermission('match_reports.manage')) {
            return $response;
        }

        return $this->ok($this->attachments->listByMatch($matchId));
    }

    public function store(int $matchId)
    {
        if ($response = $this->ensurePermission('match_reports.manage')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($payload['url'])) {
            return $this->fail('Informe o link.', 422);
        }

        $id = $this->attachments->create($matchId, [
            'url' => $payload['url'],
            'type' => 'link',
            'original_name' => $payload['original_name'] ?? null,
        ]);

        return $this->ok(['id' => $id], 'Anexo criado.', 201);
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('match_reports.manage')) {
            return $response;
        }

        $attachment = $this->attachments->find($id);
        if (!$attachment) {
            return $this->fail('Anexo não encontrado.', 404);
        }

        $this->attachments->delete($id);
        return $this->ok(['id' => $id], 'Anexo removido.');
    }
}