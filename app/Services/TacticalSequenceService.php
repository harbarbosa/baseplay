<?php

namespace App\Services;

use App\Models\TacticalSequenceModel;
use App\Models\TacticalBoardModel;
use CodeIgniter\I18n\Time;

class TacticalSequenceService
{
    protected TacticalSequenceModel $sequences;
    protected TacticalBoardModel $boards;

    public function __construct()
    {
        $this->sequences = new TacticalSequenceModel();
        $this->boards = new TacticalBoardModel();
    }

    public function listByBoard(int $boardId): array
    {
        return $this->sequences
            ->where('tactical_board_id', $boardId)
            ->where('deleted_at', null)
            ->orderBy('updated_at', 'DESC')
            ->findAll();
    }

    public function find(int $sequenceId): ?array
    {
        return $this->sequences
            ->where('id', $sequenceId)
            ->where('deleted_at', null)
            ->first();
    }

    public function create(int $boardId, array $data, int $userId): int
    {
        return (int) $this->sequences->insert([
            'tactical_board_id' => $boardId,
            'title' => trim((string) ($data['title'] ?? 'Nova sequência')),
            'description' => $data['description'] ?? null,
            'fps' => $this->normalizeFps($data['fps'] ?? 2),
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function update(int $sequenceId, array $data): bool
    {
        $payload = [];
        if (array_key_exists('title', $data)) {
            $payload['title'] = trim((string) $data['title']);
        }
        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }
        if (array_key_exists('fps', $data)) {
            $payload['fps'] = $this->normalizeFps($data['fps']);
        }
        if ($payload === []) {
            return true;
        }
        $payload['updated_at'] = Time::now()->toDateTimeString();

        return $this->sequences->update($sequenceId, $payload);
    }

    public function delete(int $sequenceId): bool
    {
        return $this->sequences->delete($sequenceId);
    }

    public function normalizeFps($fps): int
    {
        $value = (int) $fps;
        if ($value < 1) {
            $value = 1;
        }
        if ($value > 10) {
            $value = 10;
        }
        return $value;
    }
}

