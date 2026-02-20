<?php

namespace App\Services;

use App\Models\TrainingSessionModel;
use App\Models\TrainingSessionAthleteModel;
use App\Models\EventModel;
use CodeIgniter\I18n\Time;

class TrainingSessionService
{
    protected TrainingSessionModel $sessions;
    protected TrainingSessionAthleteModel $sessionAthletes;
    protected EventModel $events;

    public function __construct()
    {
        $this->sessions = new TrainingSessionModel();
        $this->sessionAthletes = new TrainingSessionAthleteModel();
        $this->events = new EventModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'training_sessions'): array
    {
        $model = $this->sessions
            ->select('training_sessions.*, teams.name AS team_name, categories.name AS category_name')
            ->join('teams', 'teams.id = training_sessions.team_id', 'left')
            ->join('categories', 'categories.id = training_sessions.category_id', 'left')
            ->where('training_sessions.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $model = $model->where('training_sessions.team_id', (int) $filters['team_id']);
        }

        if (!empty($filters['category_id'])) {
            $model = $model->where('training_sessions.category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['date_from'])) {
            $model = $model->where('training_sessions.session_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $model = $model->where('training_sessions.session_date <=', $filters['date_to']);
        }

        $items = $model->orderBy('training_sessions.session_date', 'DESC')->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $items, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        return $this->sessions->find($id) ?: null;
    }

    public function findWithRelations(int $id): ?array
    {
        $builder = $this->sessions->builder();
        $builder->select('training_sessions.*, teams.name AS team_name, categories.name AS category_name');
        $builder->join('teams', 'teams.id = training_sessions.team_id', 'left');
        $builder->join('categories', 'categories.id = training_sessions.category_id', 'left');
        $builder->where('training_sessions.id', $id);
        $builder->where('training_sessions.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function listAthletes(int $sessionId): array
    {
        return $this->sessionAthletes
            ->select('training_session_athletes.*, athletes.first_name, athletes.last_name')
            ->join('athletes', 'athletes.id = training_session_athletes.athlete_id', 'left')
            ->where('training_session_athletes.training_session_id', $sessionId)
            ->orderBy('athletes.first_name', 'ASC')
            ->findAll();
    }

    public function create(array $data, int $userId): int
    {
        $payload = [
            'team_id' => (int) $data['team_id'],
            'category_id' => (int) $data['category_id'],
            'event_id' => !empty($data['event_id']) ? (int) $data['event_id'] : null,
            'training_plan_id' => !empty($data['training_plan_id']) ? (int) $data['training_plan_id'] : null,
            'title' => $data['title'],
            'session_date' => $data['session_date'],
            'start_datetime' => $data['start_datetime'] ?? null,
            'end_datetime' => $data['end_datetime'] ?? null,
            'location' => $data['location'] ?? null,
            'general_notes' => $data['general_notes'] ?? null,
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->sessions->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'team_id' => (int) $data['team_id'],
            'category_id' => (int) $data['category_id'],
            'event_id' => !empty($data['event_id']) ? (int) $data['event_id'] : null,
            'training_plan_id' => !empty($data['training_plan_id']) ? (int) $data['training_plan_id'] : null,
            'title' => $data['title'],
            'session_date' => $data['session_date'],
            'start_datetime' => $data['start_datetime'] ?? null,
            'end_datetime' => $data['end_datetime'] ?? null,
            'location' => $data['location'] ?? null,
            'general_notes' => $data['general_notes'] ?? null,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return $this->sessions->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->sessions->delete($id);
    }

    public function createFromEvent(int $eventId, int $userId): ?int
    {
        $event = $this->events->find($eventId);
        if (!$event) {
            return null;
        }

        $payload = [
            'team_id' => $event['team_id'],
            'category_id' => $event['category_id'],
            'event_id' => $eventId,
            'title' => $event['title'],
            'session_date' => substr($event['start_datetime'], 0, 10),
            'start_datetime' => $event['start_datetime'],
            'end_datetime' => $event['end_datetime'],
            'location' => $event['location'] ?? null,
            'general_notes' => null,
        ];

        return $this->create($payload, $userId);
    }
}
