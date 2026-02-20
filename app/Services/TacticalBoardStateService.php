<?php

namespace App\Services;

use App\Models\TacticalBoardStateModel;
use CodeIgniter\I18n\Time;

class TacticalBoardStateService
{
    protected TacticalBoardStateModel $states;

    public function __construct()
    {
        $this->states = new TacticalBoardStateModel();
    }

    public function getLatest(int $boardId): ?array
    {
        return $this->states
            ->where('tactical_board_id', $boardId)
            ->orderBy('version', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function listByBoard(int $boardId, int $limit = 20): array
    {
        return $this->states
            ->select('tactical_board_states.*, users.name AS created_by_name')
            ->join('users', 'users.id = tactical_board_states.created_by', 'left')
            ->where('tactical_board_id', $boardId)
            ->orderBy('version', 'DESC')
            ->findAll($limit);
    }

    public function findByBoard(int $boardId, int $stateId): ?array
    {
        return $this->states
            ->where('id', $stateId)
            ->where('tactical_board_id', $boardId)
            ->first();
    }

    public function saveNewVersion(int $boardId, string $stateJson, int $userId): int
    {
        $decoded = json_decode($stateJson, true);
        if (!is_array($decoded)) {
            return 0;
        }

        $latest = $this->getLatest($boardId);
        $nextVersion = $latest ? ((int) $latest['version'] + 1) : 1;

        return (int) $this->states->insert([
            'tactical_board_id' => $boardId,
            'state_json' => json_encode($decoded, JSON_UNESCAPED_UNICODE),
            'version' => $nextVersion,
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function defaultStateJson(): string
    {
        return json_encode([
            'field' => [
                'background' => 'soccer_field_v1',
                'aspectRatio' => 1.6,
            ],
            'items' => [],
            'meta' => [
                'notes' => '',
                'formation' => '',
            ],
        ], JSON_UNESCAPED_UNICODE);
    }
}

