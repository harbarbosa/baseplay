<?php

namespace App\Services;

use App\Models\DocumentTypeModel;
use CodeIgniter\I18n\Time;

class DocumentTypeService
{
    protected DocumentTypeModel $types;

    public function __construct()
    {
        $this->types = new DocumentTypeModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'document_types'): array
    {
        $model = $this->types;

        if (!empty($filters['status'])) {
            $model = $model->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $model = $model->like('name', $filters['search']);
        }

        $items = $model->orderBy('name', 'ASC')->paginate($perPage, $group);
        return ['items' => $items, 'pager' => $model->pager];
    }

    public function listAllActive(): array
    {
        return $this->types->where('status', 'active')->orderBy('name', 'ASC')->findAll();
    }

    public function find(int $id): ?array
    {
        return $this->types->find($id) ?: null;
    }

    public function findByName(string $name): ?array
    {
        $normalized = mb_strtolower(trim($name));
        if ($normalized === '') {
            return null;
        }

        return $this->types
            ->where('LOWER(name)', $normalized)
            ->first() ?: null;
    }

    public function findOrCreateByName(string $name): int
    {
        $normalized = trim($name);
        if ($normalized === '') {
            return 0;
        }

        $found = $this->findByName($normalized);
        if ($found) {
            return (int) $found['id'];
        }

        return $this->create([
            'name' => $normalized,
            'requires_expiration' => 0,
            'default_valid_days' => null,
            'status' => 'active',
        ]);
    }

    public function create(array $data): int
    {
        $payload = [
            'name' => $data['name'],
            'requires_expiration' => !empty($data['requires_expiration']) ? 1 : 0,
            'default_valid_days' => ($data['default_valid_days'] ?? '') !== '' ? (int) $data['default_valid_days'] : null,
            'status' => $data['status'] ?? 'active',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->types->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'name' => $data['name'],
            'requires_expiration' => !empty($data['requires_expiration']) ? 1 : 0,
            'default_valid_days' => ($data['default_valid_days'] ?? '') !== '' ? (int) $data['default_valid_days'] : null,
            'status' => $data['status'] ?? 'active',
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return $this->types->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->types->delete($id);
    }
}
