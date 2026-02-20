<?php

namespace App\Services;

use App\Models\MatchReportModel;
use CodeIgniter\I18n\Time;

class MatchReportService
{
    protected MatchReportModel $reports;

    public function __construct()
    {
        $this->reports = new MatchReportModel();
    }

    public function findByMatch(int $matchId): ?array
    {
        return $this->reports->where('match_id', $matchId)->first() ?: null;
    }

    public function upsert(int $matchId, array $data): int
    {
        $existing = $this->findByMatch($matchId);

        $payload = [
            'match_id' => $matchId,
            'summary' => $data['summary'] ?? null,
            'strengths' => $data['strengths'] ?? null,
            'weaknesses' => $data['weaknesses'] ?? null,
            'next_actions' => $data['next_actions'] ?? null,
            'coach_notes' => $data['coach_notes'] ?? null,
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        if ($existing) {
            $this->reports->update((int) $existing['id'], $payload);
            return (int) $existing['id'];
        }

        $payload['created_at'] = Time::now()->toDateTimeString();
        return (int) $this->reports->insert($payload);
    }
}
