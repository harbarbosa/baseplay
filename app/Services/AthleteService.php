<?php

namespace App\Services;

use App\Models\AthleteModel;
use App\Models\CategoryModel;
use App\Models\TeamModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\UserTeamLinkModel;
use App\Models\UserCategoryLinkModel;
use App\Models\RoleModel;
use CodeIgniter\I18n\Time;

class AthleteService
{
    protected AthleteModel $athletes;
    protected CategoryModel $categories;
    protected TeamModel $teams;
    protected UserModel $users;
    protected UserRoleModel $userRoles;
    protected UserTeamLinkModel $userTeams;
    protected UserCategoryLinkModel $userCategories;
    protected RoleModel $roles;

    public function __construct()
    {
        $this->athletes = new AthleteModel();
        $this->categories = new CategoryModel();
        $this->teams = new TeamModel();
        $this->users = new UserModel();
        $this->userRoles = new UserRoleModel();
        $this->userTeams = new UserTeamLinkModel();
        $this->userCategories = new UserCategoryLinkModel();
        $this->roles = new RoleModel();
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
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['category_ids'])));
            if ($ids !== []) {
                $model = $model->whereIn('categories.id', $ids);
            }
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
        $cpf = $this->normalizeCpf($data['cpf'] ?? '');
        $payload = [
            'category_id'   => (int) $data['category_id'],
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'] ?? null,
            'birth_date'    => $data['birth_date'],
            'cpf'           => $cpf,
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

        $db = db_connect();
        $db->transStart();

        $athleteId = (int) $this->athletes->insert($payload);
        if ($athleteId > 0 && $cpf !== '') {
            $this->createAthleteUser($athleteId, $payload, $cpf);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return 0;
        }

        return $athleteId;
    }

    public function update(int $id, array $data): bool
    {
        $cpf = $this->normalizeCpf($data['cpf'] ?? '');
        $payload = [
            'category_id'   => (int) $data['category_id'],
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'] ?? null,
            'birth_date'    => $data['birth_date'],
            'cpf'           => $cpf,
            'position'      => $data['position'] ?? null,
            'dominant_foot' => $data['dominant_foot'] ?? null,
            'height_cm'     => $data['height_cm'] ?? null,
            'weight_kg'     => $data['weight_kg'] ?? null,
            'medical_notes' => $data['medical_notes'] ?? null,
            'internal_notes'=> $data['internal_notes'] ?? null,
            'status'        => $data['status'] ?? 'active',
            'updated_at'    => Time::now()->toDateTimeString(),
        ];

        $updated = $this->athletes->update($id, $payload);
        if ($updated && $cpf !== '') {
            $this->syncAthleteUser($id, $payload, $cpf);
        }

        return $updated;
    }

    protected function normalizeCpf(?string $value): string
    {
        $value = (string) $value;
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    protected function makeInitialPassword(array $payload, string $cpf): string
    {
        $lastName = trim((string) ($payload['last_name'] ?? ''));
        $base = $lastName !== '' ? $lastName : (string) ($payload['first_name'] ?? '');
        $parts = preg_split('/\s+/', trim($base));
        $surname = $parts ? end($parts) : $base;
        $surname = $surname ?: $base;
        $surname = iconv('UTF-8', 'ASCII//TRANSLIT', $surname) ?: $surname;
        $surname = preg_replace('/[^a-zA-Z0-9]/', '', $surname) ?? $surname;
        $surname = strtolower($surname);
        $prefix = substr($cpf, 0, 3);
        return $surname . $prefix;
    }

    protected function createAthleteUser(int $athleteId, array $payload, string $cpf): void
    {
        $existing = $this->users->where('email', $cpf)->first();
        if ($existing) {
            throw new \RuntimeException('CPF já utilizado por outro usuário.');
        }

        $role = $this->roles->where('name', 'atleta')->first();
        if (!$role) {
            throw new \RuntimeException('Papel atleta não encontrado.');
        }

        $password = $this->makeInitialPassword($payload, $cpf);
        $userId = (int) $this->users->insert([
            'name' => trim((string) ($payload['first_name'] ?? '') . ' ' . (string) ($payload['last_name'] ?? '')),
            'email' => $cpf,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 'active',
            'athlete_id' => $athleteId,
            'must_change_password' => 0,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);

        $this->userRoles->insert([
            'user_id' => $userId,
            'role_id' => (int) $role['id'],
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $categoryId = (int) ($payload['category_id'] ?? 0);
        if ($categoryId > 0) {
            $category = $this->categories->find($categoryId);
            if ($category && !empty($category['team_id'])) {
                $this->userTeams->insert([
                    'user_id' => $userId,
                    'team_id' => (int) $category['team_id'],
                    'created_at' => Time::now()->toDateTimeString(),
                ]);
            }
            $this->userCategories->insert([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'created_at' => Time::now()->toDateTimeString(),
            ]);
        }
    }

    protected function syncAthleteUser(int $athleteId, array $payload, string $cpf): void
    {
        $user = $this->users->where('athlete_id', $athleteId)->first();
        if (!$user) {
            $this->createAthleteUser($athleteId, $payload, $cpf);
            return;
        }

        $this->users->update((int) $user['id'], [
            'name' => trim((string) ($payload['first_name'] ?? '') . ' ' . (string) ($payload['last_name'] ?? '')),
            'email' => $cpf,
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->athletes->delete($id);
    }

    public function listByCategory(int $categoryId, array $categoryIds = []): array
    {
        $model = $this->athletes
            ->where('category_id', $categoryId)
            ->where('deleted_at', null)
            ->orderBy('first_name', 'ASC');
        if ($categoryIds !== []) {
            $ids = array_values(array_filter(array_map('intval', $categoryIds)));
            if ($ids !== []) {
                $model = $model->whereIn('category_id', $ids);
            }
        }

        return $model->findAll();
    }

    public function listAllWithRelations(array $teamIds = [], array $categoryIds = []): array
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
        if ($categoryIds !== []) {
            $ids = array_values(array_filter(array_map('intval', $categoryIds)));
            if ($ids !== []) {
                $model = $model->whereIn('categories.id', $ids);
            }
        }

        return $model
            ->orderBy('teams.name', 'ASC')
            ->orderBy('categories.name', 'ASC')
            ->orderBy('athletes.first_name', 'ASC')
            ->findAll();
    }
}
