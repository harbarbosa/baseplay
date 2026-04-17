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

        $callupId = (int) $this->callups->insert([
            'match_id' => $matchId,
            'athlete_id' => $athleteId,
            'callup_status' => $status,
            'is_starting' => 0,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);

        $this->syncTravelEventParticipant($matchId, $athleteId, $status);

        return $callupId;
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
        $updated = $this->callups->update($id, [
            'callup_status' => $status,
            'is_starting' => $isStarting,
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
        if ($updated) {
            $callup = $this->callups->find($id);
            if ($callup) {
                $this->syncTravelEventParticipant((int) $callup['match_id'], (int) $callup['athlete_id'], $status);
            }
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $callup = $this->callups->find($id);
        if (!$callup) {
            return false;
        }

        $deleted = $this->callups->delete($id);
        if ($deleted) {
            $this->removeTravelEventParticipant((int) $callup['match_id'], (int) $callup['athlete_id']);
        }

        return $deleted;
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

    protected function syncTravelEventParticipant(int $matchId, int $athleteId, string $status = 'invited'): void
    {
        $travelEventId = $this->getTravelEventId($matchId);
        if (!$travelEventId) {
            return;
        }

        $existing = $this->eventParticipants
            ->where('event_id', $travelEventId)
            ->where('athlete_id', $athleteId)
            ->first();

        if ($existing) {
            $this->eventParticipants->update((int) $existing['id'], [
                'invitation_status' => $status,
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
            return;
        }

        $this->eventParticipants->insert([
            'event_id' => $travelEventId,
            'athlete_id' => $athleteId,
            'invitation_status' => $status,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    protected function removeTravelEventParticipant(int $matchId, int $athleteId): void
    {
        $travelEventId = $this->getTravelEventId($matchId);
        if (!$travelEventId) {
            return;
        }

        $this->eventParticipants
            ->where('event_id', $travelEventId)
            ->where('athlete_id', $athleteId)
            ->delete();
    }

    protected function getTravelEventId(int $matchId): ?int
    {
        $row = db_connect()->table('matches')
            ->select('travel_event_id')
            ->where('id', $matchId)
            ->get()
            ->getRowArray();

        if (!$row || empty($row['travel_event_id'])) {
            return null;
        }

        return (int) $row['travel_event_id'];
    }
}
