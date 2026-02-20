<?php

namespace App\Services;

use App\Models\AttendanceModel;
use CodeIgniter\I18n\Time;

class AttendanceService
{
    protected AttendanceModel $attendance;

    public function __construct()
    {
        $this->attendance = new AttendanceModel();
    }

    public function listByEvent(int $eventId): array
    {
        return $this->attendance->builder()
            ->select('attendance.*, athletes.first_name, athletes.last_name')
            ->join('athletes', 'athletes.id = attendance.athlete_id', 'left')
            ->where('attendance.event_id', $eventId)
            ->orderBy('attendance.id', 'DESC')
            ->get()->getResultArray();
    }

    public function upsert(int $eventId, int $athleteId, string $status, string $notes = null): int
    {
        $existing = $this->attendance
            ->where('event_id', $eventId)
            ->where('athlete_id', $athleteId)
            ->first();

        $payload = [
            'event_id' => $eventId,
            'athlete_id' => $athleteId,
            'status' => $status,
            'checkin_time' => Time::now()->toDateTimeString(),
            'notes' => $notes,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        if ($existing) {
            $this->attendance->update($existing['id'], $payload);
            return (int) $existing['id'];
        }

        $payload['created_at'] = Time::now()->toDateTimeString();
        return (int) $this->attendance->insert($payload);
    }

    public function update(int $id, string $status, string $notes = null): bool
    {
        return $this->attendance->update($id, [
            'status' => $status,
            'notes' => $notes,
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->attendance->delete($id);
    }

    public function find(int $id): array
    {
        return $this->attendance->find($id);
    }
}
