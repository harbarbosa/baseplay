<?php

namespace App\Services;

use App\Models\DocumentModel;
use App\Models\DocumentTypeModel;
use CodeIgniter\I18n\Time;

class DocumentService
{
    protected DocumentModel $documents;
    protected DocumentTypeModel $types;

    public function __construct()
    {
        $this->documents = new DocumentModel();
        $this->types = new DocumentTypeModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'documents'): array
    {
        $this->updateExpiredStatuses();

        $model = $this->documents
            ->select('documents.*, document_types.name AS type_name, athletes.first_name, athletes.last_name')
            ->select('document_teams.name AS team_name')
            ->select('guardians.full_name AS guardian_name')
            ->select('athlete_teams.name AS athlete_team_name, athlete_categories.name AS athlete_category_name')
            ->join('document_types', 'document_types.id = documents.document_type_id', 'left')
            ->join('athletes', 'athletes.id = documents.athlete_id', 'left')
            ->join('guardians', 'guardians.id = documents.guardian_id', 'left')
            ->join('categories athlete_categories', 'athlete_categories.id = athletes.category_id', 'left')
            ->join('teams athlete_teams', 'athlete_teams.id = athlete_categories.team_id', 'left')
            ->join('teams document_teams', 'document_teams.id = documents.team_id', 'left')
            ->where('documents.deleted_at', null);

        if (!empty($filters['athlete_id'])) {
            $model = $model->where('documents.athlete_id', (int) $filters['athlete_id']);
        }
        if (!empty($filters['team_id'])) {
            $model = $model->where('documents.team_id', (int) $filters['team_id']);
        }
        if (!empty($filters['guardian_id'])) {
            $model = $model->where('documents.guardian_id', (int) $filters['guardian_id']);
        }
        if (!empty($filters['uploaded_by'])) {
            $model = $model->where('documents.uploaded_by', (int) $filters['uploaded_by']);
        }
        if (!empty($filters['category_id'])) {
            $model = $model->where('athlete_categories.id', (int) $filters['category_id']);
        }
        if (!empty($filters['document_type_id'])) {
            $model = $model->where('documents.document_type_id', (int) $filters['document_type_id']);
        }
        if (!empty($filters['athlete_name'])) {
            $model = $model->groupStart()
                ->orLike('athletes.first_name', $filters['athlete_name'])
                ->orLike('athletes.last_name', $filters['athlete_name'])
                ->groupEnd();
        }
        if (!empty($filters['status'])) {
            $model = $model->where('documents.status', $filters['status']);
        }
        if (!empty($filters['expiring_in_days'])) {
            $days = (int) $filters['expiring_in_days'];
            $today = date('Y-m-d');
            $future = date('Y-m-d', strtotime("+{$days} days"));
            $model = $model->where('documents.expires_at >=', $today)
                ->where('documents.expires_at <=', $future);
        }
        if (!empty($filters['from_date'])) {
            $model = $model->where('documents.created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (!empty($filters['to_date'])) {
            $model = $model->where('documents.created_at <=', $filters['to_date'] . ' 23:59:59');
        }

        $sort = (string) ($filters['sort'] ?? 'expires_nearest');
        if ($sort === 'created_desc') {
            $model->orderBy('documents.created_at', 'DESC');
        } else {
            $model->orderBy('documents.expires_at IS NULL', 'ASC', false);
            $model->orderBy('documents.expires_at', 'ASC');
            $model->orderBy('documents.created_at', 'DESC');
        }

        $items = $model->paginate($perPage, $group);
        return ['items' => $items, 'pager' => $model->pager];
    }

    public function statusCounters(array $filters = []): array
    {
        $base = db_connect()->table('documents d');
        $base->join('athletes', 'athletes.id = d.athlete_id', 'left');
        $base->join('categories', 'categories.id = athletes.category_id', 'left');
        $base->where('d.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $base->where('categories.team_id', (int) $filters['team_id']);
        }
        if (!empty($filters['category_id'])) {
            $base->where('categories.id', (int) $filters['category_id']);
        }

        $today = date('Y-m-d');
        $counters = [
            'expired' => 0,
            'expiring' => 0,
            'active' => 0,
        ];

        $rows = $base
            ->select("SUM(d.expires_at < '{$today}') AS expired_count")
            ->select("SUM(d.expires_at >= '{$today}' AND d.expires_at <= DATE_ADD('{$today}', INTERVAL 30 DAY)) AS expiring_count")
            ->select("SUM(d.status = 'active') AS active_count")
            ->get()
            ->getRowArray();

        if ($rows) {
            $counters['expired'] = (int) ($rows['expired_count'] ?? 0);
            $counters['expiring'] = (int) ($rows['expiring_count'] ?? 0);
            $counters['active'] = (int) ($rows['active_count'] ?? 0);
        }

        return $counters;
    }

    public function complianceByCategory(?int $teamId = null): array
    {
        $db = db_connect();
        $builder = $db->table('categories c')
            ->select('c.id, c.name, t.name AS team_name')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('c.deleted_at', null)
            ->orderBy('t.name', 'ASC')
            ->orderBy('c.name', 'ASC');

        if ($teamId !== null && $teamId > 0) {
            $builder->where('c.team_id', $teamId);
        }

        $rows = $builder->get()->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $categoryId = (int) $row['id'];
            $athletes = $db->table('athletes')
                ->where('deleted_at', null)
                ->where('status', 'active')
                ->where('category_id', $categoryId)
                ->countAllResults();

            $withDocs = $db->table('documents d')
                ->select('COUNT(DISTINCT d.athlete_id) AS athletes_with_docs')
                ->join('athletes a', 'a.id = d.athlete_id', 'left')
                ->where('d.deleted_at', null)
                ->where('a.deleted_at', null)
                ->where('a.status', 'active')
                ->where('a.category_id', $categoryId)
                ->get()
                ->getRowArray();

            $covered = (int) ($withDocs['athletes_with_docs'] ?? 0);
            $percentage = $athletes > 0 ? round(($covered / $athletes) * 100, 1) : 0;

            $result[] = [
                'team_name' => $row['team_name'] ?? '-',
                'category_name' => $row['name'] ?? '-',
                'athletes_total' => $athletes,
                'athletes_with_docs' => $covered,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    public function find(int $id): ?array
    {
        $this->updateExpiredStatuses();
        return $this->documents->find($id) ?: null;
    }

    public function findWithRelations(int $id): ?array
    {
        $this->updateExpiredStatuses();

        $builder = $this->documents->builder();
        $builder->select('documents.*, document_types.name AS type_name, document_types.requires_expiration, document_types.default_valid_days');
        $builder->select('athletes.first_name, athletes.last_name');
        $builder->select('guardians.full_name AS guardian_name');
        $builder->select('document_teams.name AS team_name');
        $builder->select('athlete_teams.name AS athlete_team_name, athlete_categories.name AS athlete_category_name');
        $builder->join('document_types', 'document_types.id = documents.document_type_id', 'left');
        $builder->join('athletes', 'athletes.id = documents.athlete_id', 'left');
        $builder->join('guardians', 'guardians.id = documents.guardian_id', 'left');
        $builder->join('categories athlete_categories', 'athlete_categories.id = athletes.category_id', 'left');
        $builder->join('teams athlete_teams', 'athlete_teams.id = athlete_categories.team_id', 'left');
        $builder->join('teams document_teams', 'document_teams.id = documents.team_id', 'left');
        $builder->where('documents.id', $id);
        $builder->where('documents.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function create(array $data, array $fileData, int $userId): int
    {
        $type = $this->types->find((int) $data['document_type_id']);
        $expiresAt = $data['expires_at'] ?? null;
        if (!$expiresAt && $type && !empty($type['default_valid_days'])) {
            $base = $data['issued_at'] ?? date('Y-m-d');
            $expiresAt = date('Y-m-d', strtotime($base . ' +' . (int) $type['default_valid_days'] . ' days'));
        }

        $payload = [
            'document_type_id' => (int) $data['document_type_id'],
            'athlete_id' => $this->nullableInt($data['athlete_id'] ?? null),
            'guardian_id' => $this->nullableInt($data['guardian_id'] ?? null),
            'team_id' => $this->nullableInt($data['team_id'] ?? null),
            'file_path' => $fileData['file_path'],
            'original_name' => $fileData['original_name'],
            'mime_type' => $fileData['mime_type'],
            'file_size' => $fileData['file_size'],
            'issued_at' => $data['issued_at'] ?? null,
            'expires_at' => $expiresAt,
            'uploaded_by' => $userId,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'active',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->documents->insert($payload);
    }

    public function updateMeta(int $id, array $data): bool
    {
        $type = $this->types->find((int) $data['document_type_id']);
        $expiresAt = $data['expires_at'] ?? null;
        if (!$expiresAt && $type && !empty($type['default_valid_days'])) {
            $base = $data['issued_at'] ?? date('Y-m-d');
            $expiresAt = date('Y-m-d', strtotime($base . ' +' . (int) $type['default_valid_days'] . ' days'));
        }

        $payload = [
            'document_type_id' => (int) $data['document_type_id'],
            'athlete_id' => $this->nullableInt($data['athlete_id'] ?? null),
            'guardian_id' => $this->nullableInt($data['guardian_id'] ?? null),
            'team_id' => $this->nullableInt($data['team_id'] ?? null),
            'issued_at' => $data['issued_at'] ?? null,
            'expires_at' => $expiresAt,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'active',
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return $this->documents->update($id, $payload);
    }

    public function replaceFile(int $id, array $fileData): bool
    {
        return $this->documents->update($id, [
            'file_path' => $fileData['file_path'],
            'original_name' => $fileData['original_name'],
            'mime_type' => $fileData['mime_type'],
            'file_size' => $fileData['file_size'],
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->documents->delete($id);
    }

    public function userCanAccessDocument(int $userId, array $document): bool
    {
        if (\Config\Services::rbac()->userHasPermission($userId, 'admin.access')) {
            return true;
        }

        if (!empty($document['uploaded_by']) && (int) $document['uploaded_by'] === $userId) {
            return true;
        }

        $teamIds = $this->getUserTeamIds($userId);
        if ($teamIds === []) {
            return false;
        }

        if (!empty($document['team_id'])) {
            return in_array((int) $document['team_id'], $teamIds, true);
        }

        if (!empty($document['athlete_id'])) {
            $teamId = $this->getTeamIdForAthlete((int) $document['athlete_id']);
            return $teamId !== null && in_array($teamId, $teamIds, true);
        }

        return false;
    }

    public function updateExpiredStatuses(): void
    {
        $today = date('Y-m-d');
        $this->documents
            ->where('status', 'active')
            ->where('expires_at <', $today)
            ->set([
                'status' => 'expired',
                'updated_at' => Time::now()->toDateTimeString(),
            ])
            ->update();
    }

    protected function getUserTeamIds(int $userId): array
    {
        $rows = db_connect()->table('user_team_links')
            ->select('team_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_map(static fn(array $row): int => (int) $row['team_id'], $rows);
    }

    protected function getTeamIdForAthlete(int $athleteId): ?int
    {
        $row = db_connect()->table('athletes')
            ->select('categories.team_id')
            ->join('categories', 'categories.id = athletes.category_id', 'left')
            ->where('athletes.id', $athleteId)
            ->get()
            ->getRowArray();

        if (!$row || empty($row['team_id'])) {
            return null;
        }

        return (int) $row['team_id'];
    }

    protected function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;
        return $int > 0 ? $int : null;
    }
}
