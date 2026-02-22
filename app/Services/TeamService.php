<?php

namespace App\Services;

use App\Models\TeamModel;
use CodeIgniter\I18n\Time;

class TeamService
{
    protected TeamModel $teams;

    public function __construct()
    {
        $this->teams = new TeamModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'teams'): array
    {
        $model = $this->teams;

        if (!empty($filters['ids']) && is_array($filters['ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['ids'])));
            if ($ids !== []) {
                $model = $model->whereIn('id', $ids);
            }
        }

        if (!empty($filters['search'])) {
            $model = $model->groupStart()
                ->like('name', $filters['search'])
                ->orLike('short_name', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['status'])) {
            $model = $model->where('status', $filters['status']);
        }

        $model = $model->orderBy('id', 'DESC');

        $teams = $model->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $teams, 'pager' => $pager];
    }

    public function find(int $id): array
    {
        return $this->teams->find($id);
    }

    public function create(array $data): int
    {
        $payload = [
            'name'        => $data['name'],
            'short_name'  => $data['short_name'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active',
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'legal_name' => $data['legal_name'] ?? null,
            'trade_name' => $data['trade_name'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'foundation_date' => $data['foundation_date'] ?? null,
            'president_name' => $data['president_name'] ?? null,
            'vice_president_name' => $data['vice_president_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'address_street' => $data['address_street'] ?? null,
            'address_number' => $data['address_number'] ?? null,
            'address_complement' => $data['address_complement'] ?? null,
            'address_neighborhood' => $data['address_neighborhood'] ?? null,
            'address_city' => $data['address_city'] ?? null,
            'address_state' => $data['address_state'] ?? null,
            'address_zip' => $data['address_zip'] ?? null,
            'address_country' => $data['address_country'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_at'  => Time::now()->toDateTimeString(),
            'updated_at'  => Time::now()->toDateTimeString(),
        ];

        return (int) $this->teams->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'name'        => $data['name'],
            'short_name'  => $data['short_name'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active',
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'legal_name' => $data['legal_name'] ?? null,
            'trade_name' => $data['trade_name'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'foundation_date' => $data['foundation_date'] ?? null,
            'president_name' => $data['president_name'] ?? null,
            'vice_president_name' => $data['vice_president_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'address_street' => $data['address_street'] ?? null,
            'address_number' => $data['address_number'] ?? null,
            'address_complement' => $data['address_complement'] ?? null,
            'address_neighborhood' => $data['address_neighborhood'] ?? null,
            'address_city' => $data['address_city'] ?? null,
            'address_state' => $data['address_state'] ?? null,
            'address_zip' => $data['address_zip'] ?? null,
            'address_country' => $data['address_country'] ?? null,
            'notes' => $data['notes'] ?? null,
            'updated_at'  => Time::now()->toDateTimeString(),
        ];

        return $this->teams->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->teams->delete($id);
    }
}
