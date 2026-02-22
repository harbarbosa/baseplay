<?php

namespace App\Services;

class PendingCenterService
{
    public function getData(array $filters = []): array
    {
        $teamId = !empty($filters['team_id']) ? (int) $filters['team_id'] : null;
        $categoryId = !empty($filters['category_id']) ? (int) $filters['category_id'] : null;
        $type = (string) ($filters['type'] ?? '');

        $data = [
            'expired_documents' => $this->expiredDocuments($teamId, $categoryId),
            'expiring_documents' => $this->expiringDocuments($teamId, $categoryId, 30),
            'missing_required_documents' => $this->missingRequiredDocuments($teamId, $categoryId),
            'upcoming_events_without_callups' => $this->upcomingEventsWithoutCallups($teamId, $categoryId),
        ];

        if ($type === '') {
            return $data;
        }

        return [
            'expired_documents' => $type === 'expired_documents' ? $data['expired_documents'] : [],
            'expiring_documents' => $type === 'expiring_documents' ? $data['expiring_documents'] : [],
            'missing_required_documents' => $type === 'missing_required_documents' ? $data['missing_required_documents'] : [],
            'upcoming_events_without_callups' => $type === 'upcoming_events_without_callups' ? $data['upcoming_events_without_callups'] : [],
        ];
    }

    protected function expiredDocuments(?int $teamId, ?int $categoryId): array
    {
        $builder = db_connect()->table('documents d')
            ->select('d.id, d.athlete_id, d.document_type_id, d.expires_at, d.original_name, dt.name AS type_name, t.name AS team_name, c.name AS category_name, a.first_name, a.last_name')
            ->join('document_types dt', 'dt.id = d.document_type_id', 'left')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('d.deleted_at', null)
            ->where('d.expires_at <', date('Y-m-d'))
            ->orderBy('d.expires_at', 'ASC')
            ->limit(60);

        if ($teamId) {
            $builder->where('t.id', $teamId);
        }
        if ($categoryId) {
            $builder->where('c.id', $categoryId);
        }

        return $builder->get()->getResultArray();
    }

    protected function expiringDocuments(?int $teamId, ?int $categoryId, int $days): array
    {
        $today = date('Y-m-d');
        $limit = date('Y-m-d', strtotime("+{$days} days"));

        $builder = db_connect()->table('documents d')
            ->select('d.id, d.athlete_id, d.document_type_id, d.expires_at, d.original_name, dt.name AS type_name, t.name AS team_name, c.name AS category_name, a.first_name, a.last_name')
            ->join('document_types dt', 'dt.id = d.document_type_id', 'left')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('d.deleted_at', null)
            ->where('d.expires_at >=', $today)
            ->where('d.expires_at <=', $limit)
            ->orderBy('d.expires_at', 'ASC')
            ->limit(80);

        if ($teamId) {
            $builder->where('t.id', $teamId);
        }
        if ($categoryId) {
            $builder->where('c.id', $categoryId);
        }

        return $builder->get()->getResultArray();
    }

    protected function upcomingEventsWithoutCallups(?int $teamId, ?int $categoryId): array
    {
        $from = date('Y-m-d H:i:s');
        $to = date('Y-m-d H:i:s', strtotime('+7 days'));

        $builder = db_connect()->table('events e')
            ->select('e.id, e.type, e.title, e.start_datetime, t.name AS team_name, c.name AS category_name')
            ->join('teams t', 't.id = e.team_id', 'left')
            ->join('categories c', 'c.id = e.category_id', 'left')
            ->where('e.deleted_at', null)
            ->where('e.status', 'scheduled')
            ->where('e.start_datetime >=', $from)
            ->where('e.start_datetime <=', $to)
            ->where("NOT EXISTS (SELECT 1 FROM event_participants ep WHERE ep.event_id = e.id)", null, false)
            ->orderBy('e.start_datetime', 'ASC')
            ->limit(40);

        if ($teamId) {
            $builder->where('e.team_id', $teamId);
        }
        if ($categoryId) {
            $builder->where('e.category_id', $categoryId);
        }

        return $builder->get()->getResultArray();
    }

    protected function missingRequiredDocuments(?int $teamId, ?int $categoryId): array
    {
        $db = db_connect();
        if (! $db->tableExists('category_required_documents')) {
            return [];
        }

        $reqBuilder = $db->table('category_required_documents crd')
            ->select('crd.category_id, crd.document_type_id, c.name AS category_name, t.name AS team_name, t.id AS team_id, dt.name AS type_name')
            ->join('categories c', 'c.id = crd.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->join('document_types dt', 'dt.id = crd.document_type_id', 'left');

        if ($teamId) {
            $reqBuilder->where('c.team_id', $teamId);
        }
        if ($categoryId) {
            $reqBuilder->where('c.id', $categoryId);
        }

        $requirements = $reqBuilder->get()->getResultArray();
        if ($requirements === []) {
            return [];
        }

        $items = [];
        foreach ($requirements as $req) {
            $catId = (int) ($req['category_id'] ?? 0);
            $typeId = (int) ($req['document_type_id'] ?? 0);
            if ($catId <= 0 || $typeId <= 0) {
                continue;
            }

            $athletes = $db->table('athletes')
                ->select('id, first_name, last_name')
                ->where('deleted_at', null)
                ->where('status', 'active')
                ->where('category_id', $catId)
                ->get()
                ->getResultArray();

            foreach ($athletes as $athlete) {
                $exists = $db->table('documents')
                    ->where('deleted_at', null)
                    ->where('athlete_id', (int) $athlete['id'])
                    ->where('document_type_id', $typeId)
                    ->countAllResults() > 0;

                if ($exists) {
                    continue;
                }

                $items[] = [
                    'athlete_id' => (int) $athlete['id'],
                    'first_name' => $athlete['first_name'] ?? '',
                    'last_name' => $athlete['last_name'] ?? '',
                    'category_name' => $req['category_name'] ?? '-',
                    'team_name' => $req['team_name'] ?? '-',
                    'team_id' => (int) ($req['team_id'] ?? 0),
                    'category_id' => $catId,
                    'document_type_id' => $typeId,
                    'type_name' => $req['type_name'] ?? '-',
                ];
            }
        }

        return $items;
    }

    public function missingRequiredDocumentsForApi(array $teamIds = [], ?int $teamId = null, ?int $categoryId = null, ?int $guardianId = null): array
    {
        $db = db_connect();
        if (! $db->tableExists('category_required_documents')) {
            return [];
        }

        $reqBuilder = $db->table('category_required_documents crd')
            ->select('crd.category_id, crd.document_type_id, c.name AS category_name, t.name AS team_name, t.id AS team_id, dt.name AS type_name')
            ->join('categories c', 'c.id = crd.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->join('document_types dt', 'dt.id = crd.document_type_id', 'left');

        if ($teamId) {
            $reqBuilder->where('c.team_id', $teamId);
        } elseif ($teamIds !== []) {
            $reqBuilder->whereIn('c.team_id', $teamIds);
        }
        if ($categoryId) {
            $reqBuilder->where('c.id', $categoryId);
        }

        $requirements = $reqBuilder->get()->getResultArray();
        if ($requirements === []) {
            return [];
        }

        $items = [];
        foreach ($requirements as $req) {
            $catId = (int) ($req['category_id'] ?? 0);
            $typeId = (int) ($req['document_type_id'] ?? 0);
            if ($catId <= 0 || $typeId <= 0) {
                continue;
            }

            $athletesBuilder = $db->table('athletes')
                ->select('id, first_name, last_name')
                ->where('deleted_at', null)
                ->where('status', 'active')
                ->where('category_id', $catId);

            if ($guardianId) {
                $athletesBuilder->join('athlete_guardians ag', 'ag.athlete_id = athletes.id', 'inner');
                $athletesBuilder->where('ag.guardian_id', (int) $guardianId);
            }

            $athletes = $athletesBuilder->get()->getResultArray();

            foreach ($athletes as $athlete) {
                $exists = $db->table('documents')
                    ->where('deleted_at', null)
                    ->where('athlete_id', (int) $athlete['id'])
                    ->where('document_type_id', $typeId)
                    ->countAllResults() > 0;

                if ($exists) {
                    continue;
                }

                $items[] = [
                    'athlete_id' => (int) $athlete['id'],
                    'first_name' => $athlete['first_name'] ?? '',
                    'last_name' => $athlete['last_name'] ?? '',
                    'category_name' => $req['category_name'] ?? '-',
                    'team_name' => $req['team_name'] ?? '-',
                    'team_id' => (int) ($req['team_id'] ?? 0),
                    'category_id' => $catId,
                    'document_type_id' => $typeId,
                    'type_name' => $req['type_name'] ?? '-',
                ];
            }
        }

        return $items;
    }
}
