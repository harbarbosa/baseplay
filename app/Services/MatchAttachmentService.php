<?php

namespace App\Services;

use App\Models\MatchAttachmentModel;
use CodeIgniter\I18n\Time;

class MatchAttachmentService
{
    protected MatchAttachmentModel $attachments;

    public function __construct()
    {
        $this->attachments = new MatchAttachmentModel();
    }

    public function listByMatch(int $matchId): array
    {
        return $this->attachments->where('match_id', $matchId)->orderBy('id', 'DESC')->findAll();
    }

    public function create(int $matchId, array $data): int
    {
        $payload = [
            'match_id' => $matchId,
            'file_path' => $data['file_path'] ?? null,
            'url' => $data['url'] ?? null,
            'original_name' => $data['original_name'] ?? null,
            'type' => $data['type'] ?? 'link',
            'created_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->attachments->insert($payload);
    }

    public function delete(int $id): bool
    {
        return $this->attachments->delete($id);
    }

    public function find(int $id): array
    {
        return $this->attachments->find($id);
    }
}