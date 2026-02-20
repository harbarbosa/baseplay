<?php

namespace App\Services;

class DocumentOverviewService
{
    public function overview(int $userId, array $filters = []): array
    {
        $teamIds = $this->getScopedTeamIds($userId);
        $days = (int) ($filters['days'] ?? 7);
        $days = in_array($days, [7, 30, 90], true) ? $days : 7;

        return [
            'filters' => [
                'team_id' => $filters['team_id'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'document_type_id' => $filters['document_type_id'] ?? null,
                'status' => $filters['status'] ?? null,
                'days' => $days,
            ],
            'cards' => $this->cards($teamIds, $days, $filters),
            'compliance' => $this->complianceByCategory($teamIds, $days, $filters),
            'critical' => $this->criticalPendencies($teamIds, $days, $filters),
        ];
    }

    protected function cards(array $teamIds, int $days, array $filters): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime("+{$days} days"));

        $base = $db->table('documents d')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('d.deleted_at', null);

        $this->applyFilters($base, $teamIds, $filters);

        $expired = (int) (clone $base)
            ->where('d.expires_at <', $today)
            ->countAllResults();

        $expiring = (int) (clone $base)
            ->where('d.expires_at >=', $today)
            ->where('d.expires_at <=', $future)
            ->countAllResults();

        $missingRequired = $this->countMissingRequired($teamIds, $filters);

        $awaitingApproval = 0;
        if ($this->hasStatus($db, 'pending')) {
            $awaitingApproval = (int) (clone $base)
                ->where('d.status', 'pending')
                ->countAllResults();
        }

        return [
            'expired' => $expired,
            'expiring' => $expiring,
            'missing_required' => $missingRequired,
            'awaiting_approval' => $awaitingApproval,
        ];
    }

    protected function complianceByCategory(array $teamIds, int $days, array $filters): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime("+{$days} days"));

        $categories = $db->table('categories c')
            ->select('c.id, c.name, t.name AS team_name')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('c.deleted_at', null);
        if (!empty($filters['team_id'])) {
            $categories->where('c.team_id', (int) $filters['team_id']);
        } elseif ($teamIds !== []) {
            $categories->whereIn('c.team_id', $teamIds);
        }
        if (!empty($filters['category_id'])) {
            $categories->where('c.id', (int) $filters['category_id']);
        }
        $categories = $categories->get()->getResultArray();

        $required = $db->table('category_required_documents crd')
            ->select('crd.category_id, crd.document_type_id')
            ->where('crd.deleted_at', null)
            ->where('crd.is_required', 1);
        if (!empty($filters['category_id'])) {
            $required->where('crd.category_id', (int) $filters['category_id']);
        }
        $requiredRows = $required->get()->getResultArray();
        $requiredByCategory = [];
        foreach ($requiredRows as $row) {
            $requiredByCategory[(int) $row['category_id']][] = (int) $row['document_type_id'];
        }

        $athletes = $db->table('athletes a')
            ->select('a.id, a.category_id')
            ->where('a.deleted_at', null)
            ->where('a.status', 'active');
        if (!empty($filters['category_id'])) {
            $athletes->where('a.category_id', (int) $filters['category_id']);
        }
        $athletes = $athletes->get()->getResultArray();
        $athletesByCategory = [];
        foreach ($athletes as $row) {
            $athletesByCategory[(int) $row['category_id']][] = (int) $row['id'];
        }

        $docs = $db->table('documents d')
            ->select('d.athlete_id, d.document_type_id, d.expires_at, d.status')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('d.deleted_at', null);
        if (!empty($filters['document_type_id'])) {
            $docs->where('d.document_type_id', (int) $filters['document_type_id']);
        }
        if (!empty($filters['category_id'])) {
            $docs->where('c.id', (int) $filters['category_id']);
        }
        if (!empty($filters['team_id'])) {
            $docs->where('c.team_id', (int) $filters['team_id']);
        } elseif ($teamIds !== []) {
            $docs->whereIn('c.team_id', $teamIds);
        }
        $docsRows = $docs->get()->getResultArray();

        $docMap = [];
        foreach ($docsRows as $row) {
            $athleteId = (int) $row['athlete_id'];
            $typeId = (int) $row['document_type_id'];
            $docMap[$athleteId][$typeId][] = $row;
        }

        $results = [];
        foreach ($categories as $category) {
            $categoryId = (int) $category['id'];
            $requiredTypes = $requiredByCategory[$categoryId] ?? [];
            $athleteIds = $athletesByCategory[$categoryId] ?? [];
            $athleteTotal = count($athleteIds);
            $requiredTotal = count($requiredTypes);
            if ($athleteTotal === 0 || $requiredTotal === 0) {
                $results[] = [
                    'team_name' => $category['team_name'] ?? '-',
                    'category_name' => $category['name'] ?? '-',
                    'athletes_total' => $athleteTotal,
                    'ok_pct' => 0,
                    'pending_pct' => 0,
                    'expired_pct' => 0,
                ];
                continue;
            }

            $okCount = 0;
            $pendingCount = 0;
            $expiredCount = 0;

            foreach ($athleteIds as $athleteId) {
                $status = $this->athleteRequirementStatus($athleteId, $requiredTypes, $docMap, $today, $future);
                if ($status === 'ok') {
                    $okCount++;
                } elseif ($status === 'expired') {
                    $expiredCount++;
                } else {
                    $pendingCount++;
                }
            }

            $results[] = [
                'team_name' => $category['team_name'] ?? '-',
                'category_name' => $category['name'] ?? '-',
                'athletes_total' => $athleteTotal,
                'ok_pct' => $athleteTotal > 0 ? round(($okCount / $athleteTotal) * 100, 1) : 0,
                'pending_pct' => $athleteTotal > 0 ? round(($pendingCount / $athleteTotal) * 100, 1) : 0,
                'expired_pct' => $athleteTotal > 0 ? round(($expiredCount / $athleteTotal) * 100, 1) : 0,
            ];
        }

        return $results;
    }

    protected function criticalPendencies(array $teamIds, int $days, array $filters): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime("+{$days} days"));

        $docs = $db->table('documents d')
            ->select('a.id AS athlete_id, a.first_name, a.last_name')
            ->select('c.name AS category_name, t.name AS team_name')
            ->select('dt.name AS document_type_name')
            ->select('d.status, d.expires_at')
            ->join('document_types dt', 'dt.id = d.document_type_id', 'left')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('d.deleted_at', null)
            ->groupStart()
                ->where('d.expires_at <', $today)
                ->orGroupStart()
                    ->where('d.expires_at >=', $today)
                    ->where('d.expires_at <=', $future)
                ->groupEnd()
            ->groupEnd();
        $this->applyFilters($docs, $teamIds, $filters);
        $docs = $docs->orderBy('d.expires_at', 'ASC')->limit(15)->get()->getResultArray();

        $missing = $this->missingRequiredRows($teamIds, $filters, $today, $future, 15);

        return array_slice(array_merge($missing, $docs), 0, 15);
    }

    protected function missingRequiredRows(array $teamIds, array $filters, string $today, string $future, int $limit): array
    {
        $db = db_connect();
        $required = $db->table('category_required_documents crd')
            ->select('crd.category_id, crd.document_type_id, dt.name AS document_type_name')
            ->join('document_types dt', 'dt.id = crd.document_type_id', 'left')
            ->where('crd.deleted_at', null)
            ->where('crd.is_required', 1);
        if (!empty($filters['category_id'])) {
            $required->where('crd.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['document_type_id'])) {
            $required->where('crd.document_type_id', (int) $filters['document_type_id']);
        }
        $requiredRows = $required->get()->getResultArray();
        if (!$requiredRows) {
            return [];
        }

        $athletes = $db->table('athletes a')
            ->select('a.id, a.first_name, a.last_name, c.id AS category_id, c.name AS category_name, t.name AS team_name')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('a.deleted_at', null)
            ->where('a.status', 'active');
        if (!empty($filters['category_id'])) {
            $athletes->where('a.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['team_id'])) {
            $athletes->where('c.team_id', (int) $filters['team_id']);
        } elseif ($teamIds !== []) {
            $athletes->whereIn('c.team_id', $teamIds);
        }
        $athletes = $athletes->get()->getResultArray();

        $docs = $db->table('documents d')
            ->select('d.athlete_id, d.document_type_id, d.expires_at, d.status')
            ->where('d.deleted_at', null)
            ->get()
            ->getResultArray();
        $docMap = [];
        foreach ($docs as $row) {
            $docMap[(int) $row['athlete_id']][(int) $row['document_type_id']][] = $row;
        }

        $rows = [];
        foreach ($athletes as $athlete) {
            foreach ($requiredRows as $req) {
                if ((int) $req['category_id'] !== (int) $athlete['category_id']) {
                    continue;
                }
                $docList = $docMap[(int) $athlete['id']][(int) $req['document_type_id']] ?? [];
                $hasValid = false;
                foreach ($docList as $doc) {
                    if (!empty($doc['expires_at']) && $doc['expires_at'] < $today) {
                        continue;
                    }
                    if (!empty($doc['status']) && $doc['status'] !== 'active') {
                        continue;
                    }
                    if (!empty($doc['expires_at']) && $doc['expires_at'] <= $future) {
                        continue;
                    }
                    $hasValid = true;
                    break;
                }
                if (!$hasValid) {
                    $rows[] = [
                        'athlete_id' => (int) $athlete['id'],
                        'first_name' => $athlete['first_name'] ?? '',
                        'last_name' => $athlete['last_name'] ?? '',
                        'category_name' => $athlete['category_name'] ?? '-',
                        'team_name' => $athlete['team_name'] ?? '-',
                        'document_type_name' => $req['document_type_name'] ?? '-',
                        'status' => 'missing',
                        'expires_at' => null,
                    ];
                }
                if (count($rows) >= $limit) {
                    return $rows;
                }
            }
        }

        return $rows;
    }

    protected function athleteRequirementStatus(int $athleteId, array $requiredTypes, array $docMap, string $today, string $future): string
    {
        $hasExpired = false;
        foreach ($requiredTypes as $typeId) {
            $docs = $docMap[$athleteId][$typeId] ?? [];
            $valid = false;
            $expiring = false;
            foreach ($docs as $doc) {
                $expiresAt = $doc['expires_at'] ?? null;
                $status = $doc['status'] ?? 'active';
                if ($status !== 'active') {
                    continue;
                }
                if ($expiresAt && $expiresAt < $today) {
                    $hasExpired = true;
                    continue;
                }
                if ($expiresAt && $expiresAt <= $future) {
                    $expiring = true;
                }
                $valid = true;
            }
            if (!$valid) {
                return 'pending';
            }
            if ($expiring) {
                return 'pending';
            }
        }
        return $hasExpired ? 'expired' : 'ok';
    }

    protected function countMissingRequired(array $teamIds, array $filters): int
    {
        return count($this->missingRequiredRows($teamIds, $filters, date('Y-m-d'), date('Y-m-d', strtotime('+7 days')), PHP_INT_MAX));
    }

    protected function applyFilters($builder, array $teamIds, array $filters): void
    {
        if (!empty($filters['team_id'])) {
            $builder->where('c.team_id', (int) $filters['team_id']);
        } elseif ($teamIds !== []) {
            $builder->whereIn('c.team_id', $teamIds);
        }
        if (!empty($filters['category_id'])) {
            $builder->where('c.id', (int) $filters['category_id']);
        }
        if (!empty($filters['document_type_id'])) {
            $builder->where('d.document_type_id', (int) $filters['document_type_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('d.status', $filters['status']);
        }
    }

    protected function getScopedTeamIds(int $userId): array
    {
        if (\Config\Services::rbac()->userHasPermission($userId, 'admin.access')) {
            return [];
        }

        $rows = db_connect()->table('user_team_links')
            ->select('team_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_map(static fn(array $row): int => (int) $row['team_id'], $rows);
    }

    protected function hasStatus($db, string $status): bool
    {
        try {
            $row = $db->query("SHOW COLUMNS FROM documents LIKE 'status'")->getRowArray();
            if (!$row || empty($row['Type'])) {
                return false;
            }
            return str_contains($row['Type'], $status);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
