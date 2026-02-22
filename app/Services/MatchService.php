<?php

namespace App\Services;

use App\Models\MatchModel;
use App\Models\EventModel;
use App\Services\EventService;
use CodeIgniter\I18n\Time;

class MatchService
{
    protected MatchModel $matches;
    protected EventModel $events;
    protected EventService $eventService;

    public function __construct()
    {
        $this->matches = new MatchModel();
        $this->events = new EventModel();
        $this->eventService = new EventService();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'matches'): array
    {
        $model = $this->matches
            ->select('matches.*, teams.name AS team_name, categories.name AS category_name')
            ->join('teams', 'teams.id = matches.team_id', 'left')
            ->join('categories', 'categories.id = matches.category_id', 'left')
            ->where('matches.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $model = $model->where('matches.team_id', (int) $filters['team_id']);
        }

        if (!empty($filters['category_id'])) {
            $model = $model->where('matches.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if ($ids !== []) {
                $model = $model->whereIn('matches.category_id', $ids);
            }
        }

        if (!empty($filters['date_from'])) {
            $model = $model->where('matches.match_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $model = $model->where('matches.match_date <=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $model = $model->where('matches.status', $filters['status']);
        }

        if (!empty($filters['competition_name'])) {
            $model = $model->like('matches.competition_name', $filters['competition_name']);
        }

        $items = $model->orderBy('matches.match_date', 'DESC')->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $items, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        return $this->matches->find($id);
    }

    public function findWithRelations(int $id): ?array
    {
        $builder = $this->matches->builder();
        $builder->select('matches.*, teams.name AS team_name, categories.name AS category_name');
        $builder->join('teams', 'teams.id = matches.team_id', 'left');
        $builder->join('categories', 'categories.id = matches.category_id', 'left');
        $builder->where('matches.id', $id);
        $builder->where('matches.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function create(array $data, int $userId): int
    {
        $payload = $this->payload($data, $userId);
        $matchId = (int) $this->matches->insert($payload);
        if ($matchId <= 0) {
            return $matchId;
        }

        if (empty($payload['event_id'])) {
            $eventId = $this->createOrUpdateEventFromMatch($payload, $userId);
            if ($eventId) {
                $this->matches->update($matchId, ['event_id' => $eventId]);
            }
        }

        if (!empty($payload['travel_required'])) {
            $travelEventId = $this->createOrUpdateTravelEventFromMatch($payload, $userId, $payload['travel_event_id'] ?? null);
            if ($travelEventId) {
                $this->matches->update($matchId, ['travel_event_id' => $travelEventId]);
            }
        }

        return $matchId;
    }

    public function update(int $id, array $data): bool
    {
        $payload = $this->payload($data, null, false);
        $updated = $this->matches->update($id, $payload);
        if ($updated) {
            $eventId = $payload['event_id'] ?? null;
            if ($eventId) {
                $this->createOrUpdateEventFromMatch($payload, null, (int) $eventId);
            } else {
                $newEventId = $this->createOrUpdateEventFromMatch($payload, null);
                if ($newEventId) {
                    $this->matches->update($id, ['event_id' => $newEventId]);
                }
            }

            if (!empty($payload['travel_required'])) {
                $travelEventId = $this->createOrUpdateTravelEventFromMatch($payload, null, $payload['travel_event_id'] ?? null);
                if ($travelEventId) {
                    $this->matches->update($id, ['travel_event_id' => $travelEventId]);
                }
            } elseif (!empty($payload['travel_event_id'])) {
                $this->eventService->delete((int) $payload['travel_event_id']);
                $this->matches->update($id, ['travel_event_id' => null]);
            }
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        return $this->matches->delete($id);
    }

    public function createFromEvent(int $eventId, int $userId): ?int
    {
        $event = $this->events->find($eventId);
        if (!$event || $event['type'] !== 'MATCH') {
            return null;
        }

        $payload = [
            'team_id' => $event['team_id'],
            'category_id' => $event['category_id'],
            'event_id' => $eventId,
            'opponent_name' => $event['title'],
            'match_date' => substr($event['start_datetime'], 0, 10),
            'start_time' => substr($event['start_datetime'], 11, 5),
            'location' => $event['location'] ?? null,
            'home_away' => 'neutral',
            'status' => 'scheduled',
        ];

        return $this->create($payload, $userId);
    }

    protected function createOrUpdateEventFromMatch(array $payload, ?int $userId = null, ?int $eventId = null): ?int
    {
        $start = $this->buildMatchDateTime($payload['match_date'] ?? null, $payload['start_time'] ?? null);

        $eventPayload = [
            'team_id' => (int) ($payload['team_id'] ?? 0),
            'category_id' => (int) ($payload['category_id'] ?? 0),
            'type' => 'MATCH',
            'title' => $this->buildMatchTitle($payload),
            'description' => $payload['competition_name'] ?? null,
            'start_datetime' => $start ?? Time::now()->toDateTimeString(),
            'end_datetime' => null,
            'location' => $payload['location'] ?? null,
            'status' => 'scheduled',
        ];

        if ($eventId) {
            $this->eventService->update($eventId, $eventPayload, $userId);
            return $eventId;
        }

        return $this->eventService->create($eventPayload, $userId);
    }

    protected function buildMatchDateTime(?string $matchDate, ?string $startTime): ?string
    {
        $date = trim((string) $matchDate);
        if ($date === '') {
            return null;
        }
        $time = trim((string) $startTime);
        if ($time === '') {
            $time = '00:00';
        }
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return $date . ' ' . $time;
    }

    protected function buildMatchTitle(array $payload): string
    {
        $opponent = trim((string) ($payload['opponent_name'] ?? ''));
        if ($opponent !== '') {
            return 'Jogo vs ' . $opponent;
        }

        return 'Jogo';
    }

    protected function payload(array $data, ?int $userId, bool $includeCreated = true): array
    {
        $scoreFor = $data['score_for'] ?? null;
        $scoreAgainst = $data['score_against'] ?? null;
        if ($scoreFor === '') {
            $scoreFor = null;
        }
        if ($scoreAgainst === '') {
            $scoreAgainst = null;
        }

        $payload = [
            'team_id' => (int) $data['team_id'],
            'category_id' => (int) $data['category_id'],
            'event_id' => !empty($data['event_id']) ? (int) $data['event_id'] : null,
            'opponent_name' => $data['opponent_name'],
            'competition_name' => $data['competition_name'] ?? null,
            'round_name' => $data['round_name'] ?? null,
            'match_date' => $data['match_date'],
            'start_time' => $data['start_time'] ?? null,
            'location' => $data['location'] ?? null,
            'travel_required' => (int) ($data['travel_required'] ?? 0),
            'travel_event_id' => !empty($data['travel_event_id']) ? (int) $data['travel_event_id'] : null,
            'travel_departure_datetime' => $this->normalizeDateTime($data['travel_departure_datetime'] ?? null, $data['match_date'] ?? null),
            'travel_return_datetime' => $this->normalizeDateTime($data['travel_return_datetime'] ?? null, $data['match_date'] ?? null),
            'travel_location' => $data['travel_location'] ?? null,
            'travel_notes' => $data['travel_notes'] ?? null,
            'home_away' => $data['home_away'] ?? 'neutral',
            'status' => $data['status'] ?? 'scheduled',
            'score_for' => $scoreFor,
            'score_against' => $scoreAgainst,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        if ($includeCreated) {
            $payload['created_by'] = $userId;
            $payload['created_at'] = Time::now()->toDateTimeString();
        }

        return $payload;
    }

    protected function createOrUpdateTravelEventFromMatch(array $payload, ?int $userId = null, ?int $eventId = null): ?int
    {
        $start = $this->normalizeDateTime($payload['travel_departure_datetime'] ?? null, $payload['match_date'] ?? null);
        $end = $this->normalizeDateTime($payload['travel_return_datetime'] ?? null, $payload['match_date'] ?? null);

        $title = 'Viagem';
        $opponent = trim((string) ($payload['opponent_name'] ?? ''));
        if ($opponent !== '') {
            $title .= ' Ã¢â‚¬â€ Jogo vs ' . $opponent;
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
}
