<?php

namespace App\Services;

use App\Models\MatchModel;
use App\Models\EventModel;
use CodeIgniter\I18n\Time;

class MatchService
{
    protected MatchModel $matches;
    protected EventModel $events;

    public function __construct()
    {
        $this->matches = new MatchModel();
        $this->events = new EventModel();
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
        return (int) $this->matches->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = $this->payload($data, null, false);
        return $this->matches->update($id, $payload);
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

    protected function payload(array $data, int $userId, bool $includeCreated = true): array
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
}
