<?php

namespace App\Services;

use App\Models\EventParticipantModel;
use App\Models\AthleteModel;
use CodeIgniter\I18n\Time;

class EventParticipantService
{
    protected EventParticipantModel $participants;
    protected AthleteModel $athletes;

    public function __construct()
    {
        $this->participants = new EventParticipantModel();
        $this->athletes = new AthleteModel();
    }

    public function listByEvent(int $eventId): array
    {
        return $this->participants->builder()
            ->select('event_participants.*, athletes.first_name, athletes.last_name')
            ->join('athletes', 'athletes.id = event_participants.athlete_id', 'left')
            ->where('event_participants.event_id', $eventId)
            ->orderBy('event_participants.id', 'DESC')
            ->get()->getResultArray();
    }

    public function clearByEvent(int $eventId): void
    {
        $this->participants->where('event_id', $eventId)->delete();
    }

    public function addParticipant(int $eventId, int $athleteId, string $status = 'invited', string $notes = null): int
    {
        $existing = $this->participants
            ->where('event_id', $eventId)
            ->where('athlete_id', $athleteId)
            ->first();

        if ($existing) {
            return (int) $existing['id'];
        }

        return (int) $this->participants->insert([
            'event_id' => $eventId,
            'athlete_id' => $athleteId,
            'invitation_status' => $status,
            'notes' => $notes,
            'created_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function addParticipantsBulk(int $eventId, array $athleteIds): int
    {
        $count = 0;
        foreach ($athleteIds as $athleteId) {
            if ($athleteId <= 0) {
                continue;
            }
            $this->addParticipant($eventId, (int) $athleteId);
            $count++;
        }

        return $count;
    }

    public function addFromCategory(int $eventId, int $categoryId): int
    {
        $athletes = $this->athletes->where('category_id', $categoryId)
            ->where('deleted_at', null)
            ->findAll();

        $count = 0;
        foreach ($athletes as $athlete) {
            $this->addParticipant($eventId, (int) $athlete['id']);
            $count++;
        }

        return $count;
    }

    public function addFromTeam(int $eventId, int $teamId, array $categoryIds = []): int
    {
        if ($teamId <= 0) {
            return 0;
        }

        $builder = $this->athletes->builder();
        $builder->select('athletes.id')
            ->join('categories', 'categories.id = athletes.category_id', 'left')
            ->where('athletes.deleted_at', null)
            ->where('categories.team_id', $teamId);

        if ($categoryIds !== []) {
            $ids = array_values(array_filter(array_map('intval', $categoryIds)));
            if ($ids !== []) {
                $builder->whereIn('categories.id', $ids);
            }
        }

        $athletes = $builder->get()->getResultArray();

        $count = 0;
        foreach ($athletes as $athlete) {
            $this->addParticipant($eventId, (int) $athlete['id']);
            $count++;
        }

        return $count;
    }

    public function update(int $id, string $status, string $notes = null): bool
    {
        return $this->participants->update($id, [
            'invitation_status' => $status,
            'notes' => $notes,
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->participants->delete($id);
    }

    public function find(int $id): array
    {
        return $this->participants->find($id);
    }

    public function isParticipant(int $eventId, int $athleteId): bool
    {
        return (bool) $this->participants
            ->where('event_id', $eventId)
            ->where('athlete_id', $athleteId)
            ->first();
    }
}
