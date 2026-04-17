<?php

namespace App\Services;

use App\Models\EventModel;
use App\Services\NoticeNotificationService;
use CodeIgniter\I18n\Time;

class EventService
{
    protected EventModel $events;
    protected NoticeNotificationService $notices;

    public function __construct()
    {
        $this->events = new EventModel();
        $this->notices = new NoticeNotificationService();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'events'): array
    {
        $model = $this->events
            ->select('events.*, teams.name AS team_name, categories.name AS category_name')
            ->join('teams', 'teams.id = events.team_id', 'left')
            ->join('categories', 'categories.id = events.category_id', 'left')
            ->where('events.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $model = $model->where('events.team_id', (int) $filters['team_id']);
        }

        if (!empty($filters['category_id'])) {
            $model = $model->where('events.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if ($ids !== []) {
                $model = $model->whereIn('events.category_id', $ids);
            }
        }
        if (!empty($filters['athlete_id'])) {
            $athleteId = (int) $filters['athlete_id'];
            $model = $model
                ->join('event_participants', 'event_participants.event_id = events.id', 'left')
                ->join('matches', 'matches.event_id = events.id', 'left')
                ->join('match_callups', 'match_callups.match_id = matches.id', 'left')
                ->groupStart()
                ->where('event_participants.athlete_id', $athleteId)
                ->orWhere('match_callups.athlete_id', $athleteId)
                ->groupEnd()
                ->groupBy('events.id');
        }
        if (!empty($filters['athlete_ids']) && is_array($filters['athlete_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['athlete_ids'])));
            if ($ids !== []) {
                $model = $model
                    ->join('event_participants', 'event_participants.event_id = events.id', 'left')
                    ->join('matches', 'matches.event_id = events.id', 'left')
                    ->join('match_callups', 'match_callups.match_id = matches.id', 'left')
                    ->groupStart()
                    ->whereIn('event_participants.athlete_id', $ids)
                    ->orWhereIn('match_callups.athlete_id', $ids)
                    ->groupEnd()
                    ->groupBy('events.id');
            } else {
                $model = $model->where('events.id', 0);
            }
        }

        if (!empty($filters['exclude_types']) && empty($filters['type']) && is_array($filters['exclude_types'])) {
            $exclude = array_values(array_filter($filters['exclude_types']));
            if ($exclude !== []) {
                $model = $model->whereNotIn('events.type', $exclude);
            }
        }

        if (!empty($filters['type'])) {
            $model = $model->where('events.type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $model = $model->where('events.status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $model = $model->where('events.start_datetime >=', $filters['from_date'] . ' 00:00:00');
        }

        if (!empty($filters['to_date'])) {
            $model = $model->where('events.start_datetime <=', $filters['to_date'] . ' 23:59:59');
        }

        $model = $model->orderBy('events.start_datetime', 'ASC');

        $items = $model->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $items, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        return $this->events->find($id);
    }

    public function findWithRelations(int $id): ?array
    {
        $builder = $this->events->builder();
        $builder->select('events.*, teams.name AS team_name, categories.name AS category_name');
        $builder->join('teams', 'teams.id = events.team_id', 'left');
        $builder->join('categories', 'categories.id = events.category_id', 'left');
        $builder->where('events.id', $id);
        $builder->where('events.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function create(array $data, ?int $userId = null): int
    {
        $payload = [
            'team_id'        => (int) $data['team_id'],
            'category_id'    => (int) $data['category_id'],
            'type'           => $data['type'],
            'title'          => $data['title'],
            'description'    => $data['description'] ?? null,
            'start_datetime' => $data['start_datetime'],
            'end_datetime'   => $data['end_datetime'] ?? null,
            'location'       => $data['location'] ?? null,
            'status'         => $data['status'] ?? 'scheduled',
            'created_by'     => $userId,
            'created_at'     => Time::now()->toDateTimeString(),
            'updated_at'     => Time::now()->toDateTimeString(),
        ];

        $eventId = (int) $this->events->insert($payload);
        $this->notices->eventCreated($eventId, $userId);

        return $eventId;
    }

    public function update(int $id, array $data, ?int $userId = null): bool
    {
        $payload = [
            'team_id'        => (int) $data['team_id'],
            'category_id'    => (int) $data['category_id'],
            'type'           => $data['type'],
            'title'          => $data['title'],
            'description'    => $data['description'] ?? null,
            'start_datetime' => $data['start_datetime'],
            'end_datetime'   => $data['end_datetime'] ?? null,
            'location'       => $data['location'] ?? null,
            'status'         => $data['status'] ?? 'scheduled',
            'updated_at'     => Time::now()->toDateTimeString(),
        ];

        $updated = $this->events->update($id, $payload);
        if ($updated) {
            $this->notices->eventUpdated($id, $userId);
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        return $this->events->delete($id);
    }

    public function isCancelled(int $id): bool
    {
        $event = $this->events->find($id);
        return $event && $event['status'] === 'cancelled';
    }
}
