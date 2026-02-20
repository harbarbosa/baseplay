<?php

namespace App\Services;

use App\Models\GuardianModel;
use CodeIgniter\I18n\Time;

class GuardianService
{
    protected GuardianModel $guardians;

    public function __construct()
    {
        $this->guardians = new GuardianModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'guardians'): array
    {
        $model = $this->guardians->where('deleted_at', null);

        if (!empty($filters['search'])) {
            $model = $model->groupStart()
                ->like('full_name', $filters['search'])
                ->orLike('email', $filters['search'])
                ->orLike('phone', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $model = $model->where('status', $filters['status']);
        }

        $model = $model->orderBy('id', 'DESC');

        $items = $model->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $items, 'pager' => $pager];
    }

    public function listAllActive(): array
    {
        return $this->guardians->where('status', 'active')->orderBy('full_name')->findAll();
    }

    public function find(int $id): array
    {
        return $this->guardians->find($id);
    }

    public function create(array $data): int
    {
        $payload = [
            'full_name'     => $data['full_name'],
            'phone'         => $this->normalizePhone($data['phone'] ?? null),
            'email'         => $data['email'] ?? null,
            'relation_type' => $data['relation_type'] ?? null,
            'document_id'   => $data['document_id'] ?? null,
            'address'       => $data['address'] ?? null,
            'status'        => $data['status'] ?? 'active',
            'created_at'    => Time::now()->toDateTimeString(),
            'updated_at'    => Time::now()->toDateTimeString(),
        ];

        return (int) $this->guardians->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'full_name'     => $data['full_name'],
            'phone'         => $this->normalizePhone($data['phone'] ?? null),
            'email'         => $data['email'] ?? null,
            'relation_type' => $data['relation_type'] ?? null,
            'document_id'   => $data['document_id'] ?? null,
            'address'       => $data['address'] ?? null,
            'status'        => $data['status'] ?? 'active',
            'updated_at'    => Time::now()->toDateTimeString(),
        ];

        return $this->guardians->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->guardians->delete($id);
    }

    protected function normalizePhone(string $phone): string
    {
        if ($phone === null) {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $phone));
    }
}
