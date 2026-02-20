<?php

namespace App\Services;

use App\Models\TrainingPlanBlockModel;
use CodeIgniter\I18n\Time;

class TrainingPlanBlockService
{
    protected TrainingPlanBlockModel $blocks;

    public function __construct()
    {
        $this->blocks = new TrainingPlanBlockModel();
    }

    public function find(int $id): ?array
    {
        return $this->blocks->find($id) ?: null;
    }

    public function create(array $data): int
    {
        $payload = [
            'training_plan_id' => (int) $data['training_plan_id'],
            'block_type' => $data['block_type'] ?? 'other',
            'title' => $data['title'],
            'duration_min' => (int) $data['duration_min'],
            'exercise_id' => !empty($data['exercise_id']) ? (int) $data['exercise_id'] : null,
            'instructions' => $data['instructions'] ?? null,
            'order_index' => (int) $data['order_index'],
            'media_url' => $data['media_url'] ?? null,
            'media_path' => $data['media_path'] ?? null,
            'media_name' => $data['media_name'] ?? null,
            'media_mime' => $data['media_mime'] ?? null,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->blocks->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'block_type' => $data['block_type'] ?? 'other',
            'title' => $data['title'],
            'duration_min' => (int) $data['duration_min'],
            'exercise_id' => !empty($data['exercise_id']) ? (int) $data['exercise_id'] : null,
            'instructions' => $data['instructions'] ?? null,
            'order_index' => (int) $data['order_index'],
            'media_url' => $data['media_url'] ?? null,
            'media_path' => $data['media_path'] ?? null,
            'media_name' => $data['media_name'] ?? null,
            'media_mime' => $data['media_mime'] ?? null,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return $this->blocks->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->blocks->delete($id);
    }
}
