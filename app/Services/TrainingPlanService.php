<?php

namespace App\Services;

use App\Models\TrainingPlanModel;
use App\Models\TrainingPlanBlockModel;
use CodeIgniter\I18n\Time;

class TrainingPlanService
{
    protected TrainingPlanModel $plans;
    protected TrainingPlanBlockModel $blocks;

    public function __construct()
    {
        $this->plans = new TrainingPlanModel();
        $this->blocks = new TrainingPlanBlockModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'training_plans'): array
    {
        $model = $this->plans
            ->select('training_plans.*, teams.name AS team_name, categories.name AS category_name')
            ->join('teams', 'teams.id = training_plans.team_id', 'left')
            ->join('categories', 'categories.id = training_plans.category_id', 'left')
            ->where('training_plans.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $model = $model->where('training_plans.team_id', (int) $filters['team_id']);
        }

        if (!empty($filters['category_id'])) {
            $model = $model->where('training_plans.category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['planned_date_from'])) {
            $model = $model->where('training_plans.planned_date >=', $filters['planned_date_from']);
        }

        if (!empty($filters['planned_date_to'])) {
            $model = $model->where('training_plans.planned_date <=', $filters['planned_date_to']);
        }

        if (!empty($filters['status'])) {
            $model = $model->where('training_plans.status', $filters['status']);
        }

        $items = $model->orderBy('training_plans.planned_date', 'DESC')->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $items, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        return $this->plans->find($id) ?: null;
    }

    public function findWithRelations(int $id): ?array
    {
        $builder = $this->plans->builder();
        $builder->select('training_plans.*, teams.name AS team_name, categories.name AS category_name');
        $builder->join('teams', 'teams.id = training_plans.team_id', 'left');
        $builder->join('categories', 'categories.id = training_plans.category_id', 'left');
        $builder->where('training_plans.id', $id);
        $builder->where('training_plans.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function listBlocks(int $planId): array
    {
        return $this->blocks->where('training_plan_id', $planId)
            ->orderBy('order_index', 'ASC')
            ->findAll();
    }

    public function create(array $data, int $userId): int
    {
        $payload = [
            'team_id' => (int) $data['team_id'],
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'goal' => $data['goal'] ?? null,
            'planned_date' => $data['planned_date'] ?? null,
            'total_duration_min' => $data['total_duration_min'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->plans->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'team_id' => (int) $data['team_id'],
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'goal' => $data['goal'] ?? null,
            'planned_date' => $data['planned_date'] ?? null,
            'total_duration_min' => $data['total_duration_min'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return $this->plans->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->plans->delete($id);
    }

    public function recalcTotalDuration(int $planId): int
    {
        $blocks = $this->listBlocks($planId);
        $total = 0;
        foreach ($blocks as $block) {
            $total += (int) $block['duration_min'];
        }

        $this->plans->update($planId, ['total_duration_min' => $total]);

        return $total;
    }
}
