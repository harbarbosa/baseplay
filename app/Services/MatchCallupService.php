<?php

namespace App\Services;

use App\Models\MatchCallupModel;
use App\Models\AthleteModel;
use App\Models\EventParticipantModel;
use CodeIgniter\I18n\Time;

class MatchCallupService
{
    protected MatchCallupModel $callups;
    protected AthleteModel $athletes;
    protected EventParticipantModel $eventParticipants;

    public function __construct()
    {
        $this->callups = new MatchCallupModel();
        $this->athletes = new AthleteModel();
        $this->eventParticipants = new EventParticipantModel();
    }

    public function listByMatch(int $matchId): array
    {
        return $this->callups->builder()
            ->select('match_callups.*, athletes.first_name, athletes.last_name')
            ->join('athletes', 'athletes.id = match_callups.athlete_id', 'left')
            ->where('match_callups.match_id', $matchId)
            ->orderBy('match_callups.id', 'ASC')
            ->get()->getResultArray();
    }

    public function addParticipant(int $matchId, int $athleteId, string $status = 'invited'): int
    {
        $existing = $this->callups
            ->where('match_id', $matchId)
            ->where('athlete_id', $athleteId)
            ->first();

        if ($existing) {
            return (int) $existing['id'];
        }

        return (int) $this->callups->insert([
            'match_id' => $matchId,
            'athlete_id' => $athleteId,
            'callup_status' => $status,
            'is_starting' => 0,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function addParticipantsBulk(int $matchId, array $athleteIds): int
    {
        $count = 0;
        foreach ($athleteIds as $athleteId) {
            if ($athleteId <= 0) {
                continue;
            }
            $this->addParticipant($matchId, (int) $athleteId);
            $count++;
        }

        return $count;
    }

    public function addFromCategory(int $matchId, int $categoryId): int
    {
        $athletes = $this->athletes->where('category_id', $categoryId)
            ->where('deleted_at', null)
            ->findAll();

        $count = 0;
        foreach ($athletes as $athlete) {
            $this->addParticipant($matchId, (int) $athlete['id']);
            $count++;
        }

        return $count;
    }

    public function addFromEventParticipants(int $matchId, int $eventId): int
    {
        $participants = $this->eventParticipants->where('event_id', $eventId)->findAll();
        $count = 0;
        foreach ($participants as $participant) {
            $this->addParticipant($matchId, (int) $participant['athlete_id'], $participant['invitation_status'] ?? 'invited');
            $count++;
        }

        return $count;
    }

    public function update(int $id, string $status, int $isStarting = 0): bool
    {
        return $this->callups->update($id, [
            'callup_status' => $status,
            'is_starting' => $isStarting,
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->callups->delete($id);
    }

    public function find(int $id): array
    {
        return $this->callups->find($id);
    }

    public function isCalledUp(int $matchId, int $athleteId): bool
    {
        return (bool) $this->callups
            ->where('match_id', $matchId)
            ->where('athlete_id', $athleteId)
            ->first();
    }
}