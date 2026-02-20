<?php

namespace App\Services;

use App\Models\SystemAlertModel;
use CodeIgniter\I18n\Time;

class AlertService
{
    protected SystemAlertModel $alerts;

    public function __construct()
    {
        $this->alerts = new SystemAlertModel();
    }

    public function list(array $filters = [], int $perPage = 20, string $group = 'alerts'): array
    {
        $builder = $this->alerts
            ->orderBy('is_read', 'ASC')
            ->orderBy('created_at', 'DESC');

        if (isset($filters['is_read']) && $filters['is_read'] !== '' && $filters['is_read'] !== null) {
            $builder->where('is_read', (int) $filters['is_read']);
        }

        if (!empty($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (!empty($filters['severity'])) {
            $builder->where('severity', $filters['severity']);
        }

        $items = $builder->paginate($perPage, $group);

        return [
            'items' => $items,
            'pager' => $builder->pager,
        ];
    }

    public function find(int $id): ?array
    {
        return $this->alerts->find($id) ?: null;
    }

    public function create(array $data): int
    {
        $payload = [
            'organization_id' => $data['organization_id'] ?? null,
            'type' => $data['type'],
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'is_read' => 0,
            'created_at' => Time::now()->toDateTimeString(),
            'read_at' => null,
        ];

        return (int) $this->alerts->insert($payload);
    }

    public function createIfNotExistsToday(array $data): ?int
    {
        $start = date('Y-m-d 00:00:00');

        $exists = $this->alerts
            ->where('type', $data['type'])
            ->where('entity_type', $data['entity_type'])
            ->where('entity_id', $data['entity_id'] ?? null)
            ->where('title', $data['title'])
            ->where('created_at >=', $start)
            ->first();

        if ($exists) {
            return null;
        }

        return $this->create($data);
    }

    public function markRead(int $id): bool
    {
        return $this->alerts->update($id, [
            'is_read' => 1,
            'read_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function unreadCount(): int
    {
        return $this->alerts
            ->where('is_read', 0)
            ->countAllResults();
    }
}