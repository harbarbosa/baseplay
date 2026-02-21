<?php

namespace App\Services;

use App\Models\AthleteModel;
use App\Models\CategoryModel;
use App\Models\TeamModel;
use CodeIgniter\I18n\Time;

class AthleteService
{
    protected AthleteModel $athletes;
    protected CategoryModel $categories;
    protected TeamModel $teams;

    public function __construct()
    {
        $this->athletes = new AthleteModel();
        $this->categories = new CategoryModel();
        $this->teams = new TeamModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'athletes'): array
    {
        $model = $this->athletes
            ->select('athletes.*, categories.name AS category_name, teams.name AS team_name, teams.id AS team_id')
            ->join('categories', 'categories.id = athletes.category_id', 'left')
            ->join('teams', 'teams.id = categories.team_id', 'left')
            ->where('athletes.deleted_at', null);

        if (!empty($filters['search'])) {
            $model = $model->groupStart()
                ->like('athletes.first_name', $filters['search'])
                ->orLike('athletes.last_name', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['team_id'])) {
            $model = $model->where('teams.id', (int) $filters['team_id']);
        }

        if (!empty($filters['category_id'])) {
            $model = $model->where('categories.id', (int) $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $model = $model->where('athletes.status', $filters['status']);
        }

        $model = $model->orderBy('athletes.id', 'DESC');

        $items = $model->paginate($perPage, $group);
        $pager = $model->pager;

        return ['items' => $items, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        return $this->athletes->find($id);
    }

    public function findWithRelations(int $id): ?array
    {
        $builder = $this->athletes->builder();
        $builder->select('athletes.*, categories.name AS category_name, teams.name AS team_name, teams.id AS team_id');
        $builder->join('categories', 'categories.id = athletes.category_id', 'left');
        $builder->join('teams', 'teams.id = categories.team_id', 'left');
        $builder->where('athletes.id', $id);
        $builder->where('athletes.deleted_at', null);

        return $builder->get()->getRowArray() ?: null;
    }

    public function create(array $data): int
    {
        $payload = [
            'category_id'   => (int) $data['category_id'],
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'] ?? null,
            'birth_date'    => $data['birth_date'],
            'document_id'   => $data['document_id'] ?? null,
            'position'      => $data['position'] ?? null,
            'dominant_foot' => $data['dominant_foot'] ?? null,
            'height_cm'     => $data['height_cm'] ?? null,
            'weight_kg'     => $data['weight_kg'] ?? null,
            'medical_notes' => $data['medical_notes'] ?? null,
            'internal_notes'=> $data['internal_notes'] ?? null,
            'status'        => $data['status'] ?? 'active',
            'created_at'    => Time::now()->toDateTimeString(),
            'updated_at'    => Time::now()->toDateTimeString(),
        ];

        return (int) $this->athletes->insert($payload);
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'category_id'   => (int) $data['category_id'],
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'] ?? null,
            'birth_date'    => $data['birth_date'],
            'document_id'   => $data['document_id'] ?? null,
            'position'      => $data['position'] ?? null,
            'dominant_foot' => $data['dominant_foot'] ?? null,
            'height_cm'     => $data['height_cm'] ?? null,
            'weight_kg'     => $data['weight_kg'] ?? null,
            'medical_notes' => $data['medical_notes'] ?? null,
            'internal_notes'=> $data['internal_notes'] ?? null,
            'status'        => $data['status'] ?? 'active',
            'updated_at'    => Time::now()->toDateTimeString(),
        ];

        return $this->athletes->update($id, $payload);
    }

    public function delete(int $id): bool
    {
        return $this->athletes->delete($id);
    }

    public function listByCategory(int $categoryId): array
    {
        return $this->athletes
            ->where('category_id', $categoryId)
            ->where('deleted_at', null)
            ->orderBy('first_name', 'ASC')
            ->findAll();
    }

    public function listAllWithRelations(array $teamIds = []): array
    {
        $model = $this->athletes
            ->select('athletes.id, athletes.first_name, athletes.last_name, categories.id AS category_id, categories.name AS category_name, teams.id AS team_id, teams.name AS team_name')
            ->join('categories', 'categories.id = athletes.category_id', 'left')
            ->join('teams', 'teams.id = categories.team_id', 'left')
            ->where('athletes.deleted_at', null);

        if ($teamIds !== []) {
            $ids = array_values(array_filter(array_map('intval', $teamIds)));
            if ($ids !== []) {
                $model = $model->whereIn('teams.id', $ids);
            }
        }

        return $model
            ->orderBy('teams.name', 'ASC')
            ->orderBy('categories.name', 'ASC')
            ->orderBy('athletes.first_name', 'ASC')
            ->findAll();
    }
}
