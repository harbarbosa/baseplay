<?php

namespace App\Services;

use App\Models\TacticalBoardModel;
use CodeIgniter\I18n\Time;

class TacticalBoardService
{
    protected TacticalBoardModel $boards;

    public function __construct()
    {
        $this->boards = new TacticalBoardModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'tactical_boards'): array
    {
        $model = $this->boards
            ->select('tactical_boards.*, teams.name AS team_name, categories.name AS category_name')
            ->join('teams', 'teams.id = tactical_boards.team_id', 'left')
            ->join('categories', 'categories.id = tactical_boards.category_id', 'left')
            ->where('tactical_boards.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $model = $model->where('tactical_boards.team_id', (int) $filters['team_id']);
        }

        if (!empty($filters['category_id'])) {
            $model = $model->where('tactical_boards.category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $model = $model->like('tactical_boards.title', $filters['search']);
        }

        $items = $model->orderBy('tactical_boards.updated_at', 'DESC')->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $items, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        return $this->boards->find($id);
    }

    public function findWithRelations(int $id): ?array
    {
        $builder = $this->boards->builder();
        $builder->select('tactical_boards.*, teams.name AS team_name, categories.name AS category_name');
        $builder->join('teams', 'teams.id = tactical_boards.team_id', 'left');
        $builder->join('categories', 'categories.id = tactical_boards.category_id', 'left');
        $builder->where('tactical_boards.id', $id);
        $builder->where('tactical_boards.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function create(array $data, int $userId): int
    {
        $payload = [
            'team_id' => (int) $data['team_id'],
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => trim((string) $data['title']),
            'description' => $data['description'] ?? null,
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->boards->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'team_id' => (int) $data['team_id'],
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => trim((string) $data['title']),
            'description' => $data['description'] ?? null,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return $this->boards->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->boards->delete($id);
    }

    public function duplicate(int $id, int $userId): int
    {
        $board = $this->find($id);
        if (!$board) {
            return 0;
        }

        $payload = [
            'team_id' => (int) $board['team_id'],
            'category_id' => $this->nullableInt($board['category_id'] ?? null),
            'title' => trim((string) $board['title']) . ' (cÃ³pia)',
            'description' => $board['description'] ?? null,
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->boards->insert($payload);
    }

    protected function nullableInt($value): ?int
    {
        if ($value === null || $value === '' || (int) $value <= 0) {
            return null;
        }
        return (int) $value;
    }
}
