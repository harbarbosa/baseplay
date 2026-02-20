<?php

namespace App\Services;

use App\Models\TrainingSessionAthleteModel;
use CodeIgniter\I18n\Time;

class TrainingSessionAthleteService
{
    protected TrainingSessionAthleteModel $items;

    public function __construct()
    {
        $this->items = new TrainingSessionAthleteModel();
    }

    public function find(int $id): ?array
    {
        return $this->items->find($id) ?: null;
    }

    public function createOrUpdate(array $data): int
    {
        $existing = $this->items
            ->where('training_session_id', (int) $data['training_session_id'])
            ->where('athlete_id', (int) $data['athlete_id'])
            ->first();

        $payload = [
            'training_session_id' => (int) $data['training_session_id'],
            'athlete_id' => (int) $data['athlete_id'],
            'attendance_status' => $data['attendance_status'] ?? 'present',
            'performance_note' => $data['performance_note'] ?? null,
            'rating' => (($data['rating'] ?? '') !== '') ? (int) $data['rating'] : null,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        if ($existing) {
            $this->items->update((int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        $payload['created_at'] = Time::now()->toDateTimeString();
        return (int) $this->items->insert($payload);
    }

    public function delete(int $id): bool
    {
        return $this->items->delete($id);
    }
}
