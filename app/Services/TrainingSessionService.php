<?php

namespace App\Services;

use App\Models\TrainingSessionModel;
use App\Models\TrainingSessionAthleteModel;
use App\Models\EventModel;
use App\Services\EventService;
use App\Services\EventParticipantService;
use CodeIgniter\I18n\Time;

class TrainingSessionService
{
    protected TrainingSessionModel $sessions;
    protected TrainingSessionAthleteModel $sessionAthletes;
    protected EventModel $events;
    protected EventService $eventService;
    protected EventParticipantService $participants;

    public function __construct()
    {
        $this->sessions = new TrainingSessionModel();
        $this->sessionAthletes = new TrainingSessionAthleteModel();
        $this->events = new EventModel();
        $this->eventService = new EventService();
        $this->participants = new EventParticipantService();
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
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if ($ids !== []) {
                $model = $model->whereIn('training_sessions.category_id', $ids);
            }
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
            'travel_required' => (int) ($data['travel_required'] ?? 0),
            'travel_event_id' => !empty($data['travel_event_id']) ? (int) $data['travel_event_id'] : null,
            'travel_departure_datetime' => $this->normalizeDateTime($data['travel_departure_datetime'] ?? null, $data['session_date'] ?? null),
            'travel_return_datetime' => $this->normalizeDateTime($data['travel_return_datetime'] ?? null, $data['session_date'] ?? null),
            'travel_location' => $data['travel_location'] ?? null,
            'travel_notes' => $data['travel_notes'] ?? null,
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        $sessionId = (int) $this->sessions->insert($payload);
        if ($sessionId <= 0) {
            return $sessionId;
        }

        if (empty($payload['event_id'])) {
            $eventId = $this->createOrUpdateEventFromSession($payload, $userId);
            if ($eventId) {
                $this->sessions->update($sessionId, ['event_id' => $eventId]);
                $this->ensureTrainingParticipants($eventId, (int) ($payload['category_id'] ?? 0));
            }
        }

        if (!empty($payload['travel_required'])) {
            $travelEventId = $this->createOrUpdateTravelEventFromSession($payload, $userId, $payload['travel_event_id'] ?? null);
            if ($travelEventId) {
                $this->sessions->update($sessionId, ['travel_event_id' => $travelEventId]);
            }
        }

        return $sessionId;
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
            'travel_required' => (int) ($data['travel_required'] ?? 0),
            'travel_event_id' => !empty($data['travel_event_id']) ? (int) $data['travel_event_id'] : null,
            'travel_departure_datetime' => $this->normalizeDateTime($data['travel_departure_datetime'] ?? null, $data['session_date'] ?? null),
            'travel_return_datetime' => $this->normalizeDateTime($data['travel_return_datetime'] ?? null, $data['session_date'] ?? null),
            'travel_location' => $data['travel_location'] ?? null,
            'travel_notes' => $data['travel_notes'] ?? null,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        $updated = $this->sessions->update($id, $payload);
        if ($updated) {
            $eventId = $payload['event_id'] ?? null;
            if ($eventId) {
                $this->createOrUpdateEventFromSession($payload, null, (int) $eventId);
                $this->ensureTrainingParticipants((int) $eventId, (int) ($payload['category_id'] ?? 0));
            } else {
                $newEventId = $this->createOrUpdateEventFromSession($payload, null);
                if ($newEventId) {
                    $this->sessions->update($id, ['event_id' => $newEventId]);
                    $this->ensureTrainingParticipants((int) $newEventId, (int) ($payload['category_id'] ?? 0));
                }
            }

            if (!empty($payload['travel_required'])) {
                $travelEventId = $this->createOrUpdateTravelEventFromSession($payload, null, $payload['travel_event_id'] ?? null);
                if ($travelEventId) {
                    $this->sessions->update($id, ['travel_event_id' => $travelEventId]);
                }
            } elseif (!empty($payload['travel_event_id'])) {
                $this->eventService->delete((int) $payload['travel_event_id']);
                $this->sessions->update($id, ['travel_event_id' => null]);
            }
        }

        return $updated;
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

    protected function createOrUpdateEventFromSession(array $payload, ?int $userId = null, ?int $eventId = null): ?int
    {
        $start = $this->normalizeDateTime($payload['start_datetime'] ?? null, $payload['session_date'] ?? null);
        $end = $this->normalizeDateTime($payload['end_datetime'] ?? null, $payload['session_date'] ?? null);

        $eventPayload = [
            'team_id' => (int) ($payload['team_id'] ?? 0),
            'category_id' => (int) ($payload['category_id'] ?? 0),
            'type' => 'TRAINING',
            'title' => $payload['title'] ?? 'Treino',
            'description' => null,
            'start_datetime' => $start ?? Time::now()->toDateTimeString(),
            'end_datetime' => $end,
            'location' => $payload['location'] ?? null,
            'status' => 'scheduled',
        ];

        if ($eventId) {
            $this->eventService->update($eventId, $eventPayload, $userId);
            return $eventId;
        }

        return $this->eventService->create($eventPayload, $userId);
    }

    protected function createOrUpdateTravelEventFromSession(array $payload, ?int $userId = null, ?int $eventId = null): ?int
    {
        $start = $this->normalizeDateTime($payload['travel_departure_datetime'] ?? null, $payload['session_date'] ?? null);
        $end = $this->normalizeDateTime($payload['travel_return_datetime'] ?? null, $payload['session_date'] ?? null);

        $title = 'Viagem';
        if (!empty($payload['title'])) {
            $title .= ' â€” ' . $payload['title'];
        }

        $eventPayload = [
            'team_id' => (int) ($payload['team_id'] ?? 0),
            'category_id' => (int) ($payload['category_id'] ?? 0),
            'type' => 'TRAVEL',
            'title' => $title,
            'description' => $payload['travel_notes'] ?? null,
            'start_datetime' => $start ?? Time::now()->toDateTimeString(),
            'end_datetime' => $end,
            'location' => $payload['travel_location'] ?? ($payload['location'] ?? null),
            'status' => 'scheduled',
        ];

        if ($eventId) {
            $this->eventService->update($eventId, $eventPayload, $userId);
            return $eventId;
        }

        return $this->eventService->create($eventPayload, $userId);
    }

    protected function normalizeDateTime(?string $value, ?string $fallbackDate): ?string
    {
        $value = trim((string) $value);
        if ($value !== '') {
            $normalized = str_replace('T', ' ', $value);
            if (strlen($normalized) === 16) {
                return $normalized . ':00';
            }
            return $normalized;
        }

        $fallbackDate = trim((string) $fallbackDate);
        if ($fallbackDate !== '') {
            return $fallbackDate . ' 00:00:00';
        }

        return null;
    }

    protected function ensureTrainingParticipants(int $eventId, int $categoryId): void
    {
        if ($eventId <= 0 || $categoryId <= 0) {
            return;
        }

        $existing = $this->participants->listByEvent($eventId);
        if ($existing !== []) {
            return;
        }

        $this->participants->addFromCategory($eventId, $categoryId);
    }
}
