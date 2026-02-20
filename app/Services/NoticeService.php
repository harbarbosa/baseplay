<?php

namespace App\Services;

use App\Models\NoticeModel;
use CodeIgniter\I18n\Time;

class NoticeService
{
    protected NoticeModel $notices;

    public function __construct()
    {
        $this->notices = new NoticeModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'notices', ?int $userId = null, bool $restricted = false): array
    {
        $model = $this->notices
            ->select('notices.*, teams.name AS team_name, categories.name AS category_name')
            ->select('(SELECT COUNT(*) FROM notice_reads nr WHERE nr.notice_id = notices.id) AS read_count')
            ->join('teams', 'teams.id = notices.team_id', 'left')
            ->join('categories', 'categories.id = notices.category_id', 'left')
            ->where('notices.deleted_at', null);

        if (!empty($filters['search'])) {
            $model = $model->like('notices.title', $filters['search']);
        }
        if (!empty($filters['team_id'])) {
            $model = $model->where('notices.team_id', (int) $filters['team_id']);
        }
        if (!empty($filters['category_id'])) {
            $model = $model->where('notices.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['priority'])) {
            $model = $model->where('notices.priority', $filters['priority']);
        }
        if (!empty($filters['status'])) {
            $model = $model->where('notices.status', $filters['status']);
        }
        if (!empty($filters['from_date'])) {
            $model = $model->where('notices.publish_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (!empty($filters['to_date'])) {
            $model = $model->where('notices.publish_at <=', $filters['to_date'] . ' 23:59:59');
        }

        if ($restricted && $userId !== null) {
            $model = $this->applyScope($model, $userId);
            $model = $model->where('notices.status', 'published');
        }

        $model = $model
            ->orderBy('notices.publish_at', 'DESC')
            ->orderBy('notices.created_at', 'DESC');

        $items = $model->paginate($perPage, $group);
        $items = $this->appendReadStats($items);

        return ['items' => $items, 'pager' => $model->pager];
    }

    public function find(int $id): ?array
    {
        return $this->notices->find($id) ?: null;
    }

    public function findWithRelations(int $id): ?array
    {
        $builder = $this->notices->builder();
        $builder->select('notices.*, teams.name AS team_name, categories.name AS category_name');
        $builder->join('teams', 'teams.id = notices.team_id', 'left');
        $builder->join('categories', 'categories.id = notices.category_id', 'left');
        $builder->where('notices.id', $id);
        $builder->where('notices.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function create(array $data, ?int $userId = null): int
    {
        return (int) $this->notices->insert([
            'team_id' => $this->nullableInt($data['team_id'] ?? null),
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => $data['title'],
            'message' => $data['message'],
            'created_by' => $userId,
            'priority' => $data['priority'] ?? 'normal',
            'publish_at' => $data['publish_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => $data['status'] ?? 'published',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        return $this->notices->update($id, [
            'team_id' => $this->nullableInt($data['team_id'] ?? null),
            'category_id' => $this->nullableInt($data['category_id'] ?? null),
            'title' => $data['title'],
            'message' => $data['message'],
            'priority' => $data['priority'] ?? 'normal',
            'publish_at' => $data['publish_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => $data['status'] ?? 'published',
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->notices->delete($id);
    }

    public function userCanAccessNotice(int $userId, array $notice): bool
    {
        if (empty($notice['team_id']) && empty($notice['category_id'])) {
            return true;
        }

        $teamIds = $this->getUserTeamIds($userId);
        if ($teamIds === []) {
            return false;
        }

        if (!empty($notice['team_id'])) {
            return in_array((int) $notice['team_id'], $teamIds, true);
        }

        if (!empty($notice['category_id'])) {
            $teamId = $this->getTeamIdForCategory((int) $notice['category_id']);
            return $teamId !== null && in_array($teamId, $teamIds, true);
        }

        return false;
    }

    protected function applyScope(NoticeModel $model, int $userId): NoticeModel
    {
        $teamIds = $this->getUserTeamIds($userId);
        if ($teamIds === []) {
            return $model->groupStart()
                ->where('notices.team_id', null)
                ->where('notices.category_id', null)
                ->groupEnd();
        }

        return $model->groupStart()
            ->groupStart()
            ->where('notices.team_id', null)
            ->where('notices.category_id', null)
            ->groupEnd()
            ->orWhereIn('notices.team_id', $teamIds)
            ->orWhereIn('categories.team_id', $teamIds)
            ->groupEnd();
    }

    protected function appendReadStats(array $items): array
    {
        foreach ($items as &$notice) {
            $readCount = (int) ($notice['read_count'] ?? 0);
            $targetCount = $this->countTargetReaders($notice);
            $notice['read_count'] = $readCount;
            $notice['target_count'] = $targetCount;
            $notice['read_percent'] = $targetCount > 0 ? (int) round(($readCount / $targetCount) * 100) : 0;
        }

        return $items;
    }

    protected function countTargetReaders(array $notice): int
    {
        $db = db_connect();

        if (!empty($notice['team_id'])) {
            return (int) $db->table('user_team_links')
                ->where('team_id', (int) $notice['team_id'])
                ->countAllResults();
        }

        if (!empty($notice['category_id'])) {
            $teamId = $this->getTeamIdForCategory((int) $notice['category_id']);
            if ($teamId !== null) {
                return (int) $db->table('user_team_links')
                    ->where('team_id', $teamId)
                    ->countAllResults();
            }
        }

        return (int) $db->table('users')->where('status', 'active')->countAllResults();
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

    protected function getTeamIdForCategory(int $categoryId): ?int
    {
        $row = db_connect()->table('categories')
            ->select('team_id')
            ->where('id', $categoryId)
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
