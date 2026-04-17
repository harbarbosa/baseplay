<?php

namespace App\Services;

use CodeIgniter\I18n\Time;

class DocumentAlertService
{
    public function getAlerts(array $daysList = [7, 15, 30], array $filters = []): array
    {
        $today = date('Y-m-d');
        $alerts = [];
        $athleteId = !empty($filters['athlete_id']) ? (int) $filters['athlete_id'] : null;
        $athleteIds = !empty($filters['athlete_ids']) && is_array($filters['athlete_ids'])
            ? array_values(array_filter(array_map('intval', $filters['athlete_ids'])))
            : [];

        foreach ($daysList as $days) {
            $limit = date('Y-m-d', strtotime("+{$days} days"));
            $builder = db_connect()->table('documents')
                ->select('documents.*, document_types.name AS type_name, athletes.first_name, athletes.last_name, teams.name AS team_name')
                ->join('document_types', 'document_types.id = documents.document_type_id', 'left')
                ->join('athletes', 'athletes.id = documents.athlete_id', 'left')
                ->join('teams', 'teams.id = documents.team_id', 'left')
                ->where('documents.deleted_at', null)
                ->where('documents.status', 'active')
                ->where('documents.expires_at >=', $today)
                ->where('documents.expires_at <=', $limit)
                ->orderBy('documents.expires_at', 'ASC');
            if ($athleteId) {
                $builder->where('documents.athlete_id', $athleteId);
            } elseif ($athleteIds !== []) {
                $builder->whereIn('documents.athlete_id', $athleteIds);
            }

            $items = $builder->get()->getResultArray();

            $alerts[(string) $days] = $items;
        }

        $expiredBuilder = db_connect()->table('documents')
            ->select('documents.*, document_types.name AS type_name, athletes.first_name, athletes.last_name, teams.name AS team_name')
            ->join('document_types', 'document_types.id = documents.document_type_id', 'left')
            ->join('athletes', 'athletes.id = documents.athlete_id', 'left')
            ->join('teams', 'teams.id = documents.team_id', 'left')
            ->where('documents.deleted_at', null)
            ->where('documents.expires_at <', $today)
            ->orderBy('documents.expires_at', 'ASC');
        if ($athleteId) {
            $expiredBuilder->where('documents.athlete_id', $athleteId);
        } elseif ($athleteIds !== []) {
            $expiredBuilder->whereIn('documents.athlete_id', $athleteIds);
        }
        $expired = $expiredBuilder->get()->getResultArray();

        return [
            'expired' => $expired,
            'expiring' => $alerts,
            'generated_at' => Time::now()->toDateTimeString(),
        ];
    }
}
