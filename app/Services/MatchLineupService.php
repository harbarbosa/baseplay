<?php

namespace App\Services;

use App\Models\MatchLineupPositionModel;
use CodeIgniter\I18n\Time;

class MatchLineupService
{
    protected MatchLineupPositionModel $lineups;

    public function __construct()
    {
        $this->lineups = new MatchLineupPositionModel();
    }

    public function listByMatch(int $matchId): array
    {
        return $this->lineups->builder()
            ->select('match_lineup_positions.*, athletes.first_name, athletes.last_name')
            ->join('athletes', 'athletes.id = match_lineup_positions.athlete_id', 'left')
            ->where('match_lineup_positions.match_id', $matchId)
            ->orderBy('match_lineup_positions.order_index', 'ASC')
            ->get()->getResultArray();
    }

    public function upsert(int $matchId, int $athleteId, array $data): int
    {
        $existing = $this->lineups
            ->where('match_id', $matchId)
            ->where('athlete_id', $athleteId)
            ->first();

        $payload = [
            'match_id' => $matchId,
            'athlete_id' => $athleteId,
            'lineup_role' => $data['lineup_role'] ?? 'starting',
            'position_code' => $data['position_code'] ?? null,
            'shirt_number' => $data['shirt_number'] ?? null,
            'x' => $data['x'] ?? null,
            'y' => $data['y'] ?? null,
            'order_index' => $data['order_index'] ?? 0,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        if ($existing) {
            $this->lineups->update((int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        $payload['created_at'] = Time::now()->toDateTimeString();
        return (int) $this->lineups->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'lineup_role' => $data['lineup_role'] ?? 'starting',
            'position_code' => $data['position_code'] ?? null,
            'shirt_number' => $data['shirt_number'] ?? null,
            'x' => $data['x'] ?? null,
            'y' => $data['y'] ?? null,
            'order_index' => $data['order_index'] ?? 0,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return $this->lineups->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->lineups->delete($id);
    }

    public function find(int $id): array
    {
        return $this->lineups->find($id);
    }
}