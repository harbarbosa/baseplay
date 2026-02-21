<?php

namespace App\Services;

use App\Models\CategoryModel;
use CodeIgniter\I18n\Time;

class CategoryService
{
    protected CategoryModel $categories;

    public function __construct()
    {
        $this->categories = new CategoryModel();
    }

    public function listByTeam(int $teamId): array
    {
        return $this->categories
            ->where('team_id', $teamId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function find(int $id): ?array
    {
        return $this->categories->find($id);
    }

    public function listAll(?int $teamId = null, bool $onlyActive = false): array
    {
        $model = $this->categories->where('deleted_at', null);
        if ($onlyActive) {
            $model = $model->where('status', 'active');
        }
        if ($teamId) {
            $model = $model->where('team_id', $teamId);
        }

        return $model->orderBy('name', 'ASC')->findAll();
    }

    public function listAllActive(?int $teamId = null): array
    {
        return $this->listAll($teamId, true);
    }

    public function listDistinctByTeam(?int $teamId = null, bool $onlyActive = false): array
    {
        $builder = $this->categories->builder();
        $builder->select('MIN(categories.id) AS id, categories.name');
        $builder->where('categories.deleted_at', null);
        if ($onlyActive) {
            $builder->where('categories.status', 'active');
        }
        if ($teamId) {
            $builder->where('categories.team_id', $teamId);
        }
        $builder->groupBy('categories.name');
        $builder->orderBy('categories.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function listDistinctAllByTeam(bool $onlyActive = false, array $teamIds = []): array
    {
        $builder = $this->categories->builder();
        $builder->select('MIN(categories.id) AS id, categories.team_id, categories.name');
        $builder->where('categories.deleted_at', null);
        if ($onlyActive) {
            $builder->where('categories.status', 'active');
        }
        if ($teamIds !== []) {
            $ids = array_values(array_filter(array_map('intval', $teamIds)));
            if ($ids !== []) {
                $builder->whereIn('categories.team_id', $ids);
            }
        }
        $builder->groupBy('categories.team_id, categories.name');
        $builder->orderBy('categories.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function ensureStandardCategories(int $teamId, int $from = 10, int $to = 20): void
    {
        if ($teamId <= 0) {
            return;
        }

        for ($age = $from; $age <= $to; $age++) {
            $name = 'Sub-' . $age;
            $exists = $this->categories
                ->where('team_id', $teamId)
                ->where('name', $name)
                ->where('deleted_at', null)
                ->first();

            if ($exists) {
                continue;
            }

            $this->categories->insert([
                'team_id' => $teamId,
                'name' => $name,
                'gender' => 'mixed',
                'status' => 'active',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
        }
    }

    public function create(array $data): int
    {
        $payload = [
            'team_id'       => (int) $data['team_id'],
            'name'          => $data['name'],
            'year_from'     => $data['year_from'] ?? null,
            'year_to'       => $data['year_to'] ?? null,
            'gender'        => $data['gender'] ?? 'mixed',
            'training_days' => $data['training_days'] ?? null,
            'status'        => $data['status'] ?? 'active',
            'created_at'    => Time::now()->toDateTimeString(),
            'updated_at'    => Time::now()->toDateTimeString(),
        ];

        return (int) $this->categories->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'name'          => $data['name'],
            'year_from'     => $data['year_from'] ?? null,
            'year_to'       => $data['year_to'] ?? null,
            'gender'        => $data['gender'] ?? 'mixed',
            'training_days' => $data['training_days'] ?? null,
            'status'        => $data['status'] ?? 'active',
            'updated_at'    => Time::now()->toDateTimeString(),
        ];

        return $this->categories->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->categories->delete($id);
    }
}
