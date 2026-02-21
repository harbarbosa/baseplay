<?php

namespace App\Services;

use App\Models\TeamModel;
use CodeIgniter\I18n\Time;

class TeamService
{
    protected TeamModel $teams;

    public function __construct()
    {
        $this->teams = new TeamModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'teams'): array
    {
        $model = $this->teams;

        if (!empty($filters['ids']) && is_array($filters['ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['ids'])));
            if ($ids !== []) {
                $model = $model->whereIn('id', $ids);
            }
        }

        if (!empty($filters['search'])) {
            $model = $model->groupStart()
                ->like('name', $filters['search'])
                ->orLike('short_name', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $model = $model->where('status', $filters['status']);
        }

        $model = $model->orderBy('id', 'DESC');

        $teams = $model->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $teams, 'pager' => $pager];
    }

    public function find(int $id): array
    {
        return $this->teams->find($id);
    }

    public function create(array $data): int
    {
        $payload = [
            'name'        => $data['name'],
            'short_name'  => $data['short_name'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active',
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'created_at'  => Time::now()->toDateTimeString(),
            'updated_at'  => Time::now()->toDateTimeString(),
        ];

        return (int) $this->teams->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'name'        => $data['name'],
            'short_name'  => $data['short_name'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active',
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'updated_at'  => Time::now()->toDateTimeString(),
        ];

        return $this->teams->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->teams->delete($id);
    }
}
