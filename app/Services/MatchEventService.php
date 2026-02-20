<?php

namespace App\Services;

use App\Models\MatchEventModel;
use App\Models\MatchModel;
use CodeIgniter\I18n\Time;

class MatchEventService
{
    protected MatchEventModel $events;
    protected MatchModel $matches;

    public function __construct()
    {
        $this->events = new MatchEventModel();
        $this->matches = new MatchModel();
    }

    public function listByMatch(int $matchId): array
    {
        return $this->events->builder()
            ->select('match_events.*, athletes.first_name, athletes.last_name, related.first_name AS related_first_name, related.last_name AS related_last_name')
            ->join('athletes', 'athletes.id = match_events.athlete_id', 'left')
            ->join('athletes AS related', 'related.id = match_events.related_athlete_id', 'left')
            ->where('match_events.match_id', $matchId)
            ->orderBy('match_events.minute', 'ASC')
            ->orderBy('match_events.id', 'ASC')
            ->get()->getResultArray();
    }

    public function create(int $matchId, array $data): int
    {
        $payload = [
            'match_id' => $matchId,
            'athlete_id' => !empty($data['athlete_id']) ? (int) $data['athlete_id'] : null,
            'event_type' => $data['event_type'],
            'minute' => $data['minute'] ?? null,
            'related_athlete_id' => !empty($data['related_athlete_id']) ? (int) $data['related_athlete_id'] : null,
            'notes' => $data['notes'] ?? null,
            'created_at' => Time::now()->toDateTimeString(),
        ];

        $id = (int) $this->events->insert($payload);
        $this->recalculateScore($matchId);
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $event = $this->events->find($id);
        if (!$event) {
            return false;
        }

        $payload = [
            'athlete_id' => !empty($data['athlete_id']) ? (int) $data['athlete_id'] : null,
            'event_type' => $data['event_type'] ?? $event['event_type'],
            'minute' => $data['minute'] ?? null,
            'related_athlete_id' => !empty($data['related_athlete_id']) ? (int) $data['related_athlete_id'] : null,
            'notes' => $data['notes'] ?? null,
        ];

        $result = $this->events->update($id, $payload);
        $this->recalculateScore((int) $event['match_id']);
        return $result;
    }

    public function delete(int $id): bool
    {
        $event = $this->events->find($id);
        if (!$event) {
            return false;
        }

        $result = $this->events->delete($id);
        $this->recalculateScore((int) $event['match_id']);
        return $result;
    }

    public function find(int $id): array
    {
        return $this->events->find($id);
    }

    protected function recalculateScore(int $matchId): void
    {
        $goals = $this->events
            ->where('match_id', $matchId)
            ->where('event_type', 'goal')
            ->countAllResults();

        $this->matches->update($matchId, ['score_for' => $goals]);
    }
}