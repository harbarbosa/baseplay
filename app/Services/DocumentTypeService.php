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
            'is_required' => 0,
            'status' => 'active',
        ]);
    }

    public function create(array $data): int
    {
        $isRequired = !empty($data['is_required']) ? 1 : 0;
        $payload = [
            'name' => $data['name'],
            'requires_expiration' => !empty($data['requires_expiration']) ? 1 : 0,
            'default_valid_days' => ($data['default_valid_days'] ?? '') !== '' ? (int) $data['default_valid_days'] : null,
            'is_required' => $isRequired,
            'status' => $data['status'] ?? 'active',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        $id = (int) $this->types->insert($payload);
        $this->syncCategoryRequirements($id, $isRequired);
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $isRequired = !empty($data['is_required']) ? 1 : 0;
        $payload = [
            'name' => $data['name'],
            'requires_expiration' => !empty($data['requires_expiration']) ? 1 : 0,
            'default_valid_days' => ($data['default_valid_days'] ?? '') !== '' ? (int) $data['default_valid_days'] : null,
            'is_required' => $isRequired,
            'status' => $data['status'] ?? 'active',
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        $updated = $this->types->update($id, $payload);
        if ($updated) {
            $this->syncCategoryRequirements($id, $isRequired);
        }
        return $updated;
    }

    public function delete(int $id): bool
    {
        return $this->types->delete($id);
    }

    protected function syncCategoryRequirements(int $typeId, int $isRequired): void
    {
        if ($typeId <= 0) {
            return;
        }

        $db = db_connect();
        if (! $db->tableExists('category_required_documents')) {
            return;
        }

        if ($isRequired !== 1) {
            $db->table('category_required_documents')
                ->where('document_type_id', $typeId)
                ->delete();
            return;
        }

        $categories = $db->table('categories')
            ->select('id')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();
        if ($categories === []) {
            return;
        }

        $existingRows = $db->table('category_required_documents')
            ->select('id, category_id, deleted_at')
            ->where('document_type_id', $typeId)
            ->get()
            ->getResultArray();
        $existing = [];
        foreach ($existingRows as $row) {
            $existing[(int) $row['category_id']] = $row;
        }

        $now = Time::now()->toDateTimeString();
        foreach ($categories as $category) {
            $categoryId = (int) ($category['id'] ?? 0);
            if ($categoryId <= 0) {
                continue;
            }
            if (isset($existing[$categoryId])) {
                $db->table('category_required_documents')
                    ->where('document_type_id', $typeId)
                    ->where('category_id', $categoryId)
                    ->update([
                        'is_required' => 1,
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            $db->table('category_required_documents')->insert([
                'category_id' => $categoryId,
                'document_type_id' => $typeId,
                'is_required' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
