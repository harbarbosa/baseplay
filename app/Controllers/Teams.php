<?php

namespace App\Controllers;

use App\Services\TeamService;
use App\Services\CategoryService;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\UserTeamLinkModel;
use App\Models\RolePermissionModel;
use CodeIgniter\I18n\Time;
use Config\Services;

class Teams extends BaseController
{
    protected TeamService $teams;
    protected CategoryService $categories;

    public function __construct()
    {
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        if ($this->scopedTeamIds !== []) {
            $filters['ids'] = $this->scopedTeamIds;
        }

        $result = $this->teams->list($filters, 15, 'teams');

        return view('teams/index', [
            'title' => 'Equipes',
            'teams' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        if ($response = $this->denyIfTeamForbidden((int) $team['id'], '/teams')) {
            return $response;
        }

        $categories = $this->categories->listByTeam($id);

        return view('teams/show', [
            'title' => 'Equipe',
            'team' => $team,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        return view('teams/create', ['title' => 'Nova equipe']);
    }

    public function store()
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $validation = service('validation');
        $rules = config('Validation')->teamCreate;
        $rules['admin_email'] = 'required|valid_email|is_unique[users.email]';
        $rules['admin_name'] = 'permit_empty|min_length[3]';
        $validation->setRules($rules, config('Validation')->teamCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->request->getPost();
        $payload['primary_color'] = $this->normalizeColor($payload['primary_color'] ?? null);
        $payload['secondary_color'] = $this->normalizeColor($payload['secondary_color'] ?? null);

        $upload = $this->handleTeamLogoUpload();
        if (!empty($upload['error'])) {
            return redirect()->back()->withInput()->with('error', $upload['error']);
        }
        if (!empty($upload['path'])) {
            $payload['logo_path'] = $upload['path'];
        }

        $teamId = $this->teams->create($payload);
        Services::audit()->log(session('user_id'), 'team_created', ['team_id' => $teamId]);

        $adminEmail = trim((string) ($payload['admin_email'] ?? ''));
        $adminName = trim((string) ($payload['admin_name'] ?? ''));
        if ($adminName === '') {
            $adminName = trim((string) ($payload['name'] ?? 'Equipe')) . ' Admin';
        }

        $tempPassword = bin2hex(random_bytes(4));

        $userId = (new UserModel())->insert([
            'name' => $adminName,
            'email' => $adminEmail,
            'password_hash' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'status' => 'active',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);

        $roleModel = new RoleModel();
        $role = $roleModel->where('name', 'admin_equipe')->first();
        if (!$role) {
            $roleId = $roleModel->insert([
                'name' => 'admin_equipe',
                'description' => 'Administrador de equipe',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
            $this->copyTrainerPermissionsToRole((int) $roleId);
        } else {
            $roleId = (int) $role['id'];
            $this->ensureRoleHasPermissions($roleId);
        }

        (new UserRoleModel())->insert([
            'user_id' => (int) $userId,
            'role_id' => (int) $roleId,
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        (new UserTeamLinkModel())->insert([
            'user_id' => (int) $userId,
            'team_id' => (int) $teamId,
            'role_in_team' => 'admin_equipe',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        Services::audit()->log(session('user_id'), 'team_admin_created', ['user_id' => $userId, 'team_id' => $teamId]);

        return redirect()->to('/teams')->with('success', 'Equipe criada. Admin da equipe: ' . $adminEmail . ' | Senha temporaria: ' . $tempPassword);
    }

    public function edit(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        return view('teams/edit', ['title' => 'Editar equipe', 'team' => $team]);
    }

    public function update(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        $validation = service('validation');
        $rules = config('Validation')->teamUpdate;
        $rules['name'] = str_replace('{id}', (string) $id, $rules['name']);
        $validation->setRules($rules, config('Validation')->teamCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->request->getPost();
        $payload['primary_color'] = $this->normalizeColor($payload['primary_color'] ?? null);
        $payload['secondary_color'] = $this->normalizeColor($payload['secondary_color'] ?? null);

        $upload = $this->handleTeamLogoUpload();
        if (!empty($upload['error'])) {
            return redirect()->back()->withInput()->with('error', $upload['error']);
        }
        if (!empty($upload['path'])) {
            $payload['logo_path'] = $upload['path'];
        } else {
            $payload['logo_path'] = $team['logo_path'] ?? null;
        }

        $this->teams->update($id, $payload);
        Services::audit()->log(session('user_id'), 'team_updated', ['team_id' => $id]);

        return redirect()->to('/teams/' . $id)->with('success', 'Equipe atualizada.');
    }

    public function deleteConfirm(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        return view('teams/delete', ['title' => 'Excluir equipe', 'team' => $team]);
    }

    public function delete(int $id)
    {
        if (!has_permission('admin.access')) {
            return redirect()->to('/teams')->with('error', 'Acesso negado.');
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return redirect()->to('/teams')->with('error', 'Equipe nao encontrada.');
        }

        $db = db_connect();
        $db->transBegin();

        $filePaths = $this->purgeTeamData($db, $id);
        $this->teams->delete($id);

        if ($db->transStatus() === false) {
            $db->transRollback();
            return redirect()->to('/teams')->with('error', 'Falha ao remover a equipe.');
        }

        $db->transCommit();

        if (!empty($team['logo_path'])) {
            $filePaths[] = $team['logo_path'];
        }

        foreach (array_unique(array_filter($filePaths)) as $path) {
            $this->deleteFileIfExists($path);
        }

        Services::audit()->log(session('user_id'), 'team_deleted', ['team_id' => $id]);

        return redirect()->to('/teams')->with('success', 'Equipe removida.');
    }

    protected function normalizeColor($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strtoupper(trim((string) $value));
        if (preg_match('/^#([0-9A-F]{6})$/', $value)) {
            return $value;
        }
        if (preg_match('/^#([0-9A-F]{3})$/', $value)) {
            return $value;
        }

        return null;
    }

    protected function handleTeamLogoUpload(): array
    {
        $file = $this->request->getFile('team_logo');
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['error' => null, 'path' => null];
        }
        if (!$file->isValid()) {
            return ['error' => 'Falha no upload do logo.', 'path' => null];
        }

        if ($file->getSizeByUnit('mb') > 4) {
            return ['error' => 'Logo maior que 4MB.', 'path' => null];
        }

        $mime = (string) $file->getMimeType();
        if ($mime === '') {
            $mime = (string) $file->getClientMimeType();
        }

        $allowed = [
            'image/png',
            'image/jpeg',
            'image/webp',
            'image/svg+xml',
        ];
        if (!in_array($mime, $allowed, true)) {
            return ['error' => 'Formato de logo invalido. Use PNG, JPG, WEBP ou SVG.', 'path' => null];
        }

        $publicDir = FCPATH . 'uploads/teams';
        if (!is_dir($publicDir)) {
            mkdir($publicDir, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($publicDir, $newName);

        return ['error' => null, 'path' => 'uploads/teams/' . $newName];
    }

    protected function copyTrainerPermissionsToRole(int $roleId): void
    {
        $trainer = (new RoleModel())->where('name', 'treinador')->first();
        if (!$trainer) {
            return;
        }

        $rolePermissions = new RolePermissionModel();
        $permissionIds = array_column(
            $rolePermissions->where('role_id', (int) $trainer['id'])->findAll(),
            'permission_id'
        );

        if ($permissionIds === []) {
            return;
        }

        $now = Time::now()->toDateTimeString();
        foreach ($permissionIds as $permissionId) {
            $rolePermissions->insert([
                'role_id' => $roleId,
                'permission_id' => (int) $permissionId,
                'created_at' => $now,
            ]);
        }
    }

    protected function ensureRoleHasPermissions(int $roleId): void
    {
        $rolePermissions = new RolePermissionModel();
        $exists = $rolePermissions->where('role_id', $roleId)->first();
        if ($exists) {
            return;
        }

        $this->copyTrainerPermissionsToRole($roleId);
    }

    protected function purgeTeamData($db, int $teamId): array
    {
        $filePaths = [];

        $categoryIds = $this->getIds($db, 'categories', 'id', ['team_id' => $teamId]);
        $athleteIds = $categoryIds !== [] ? $this->getIds($db, 'athletes', 'id', ['category_id' => $categoryIds]) : [];
        $eventIds = $this->getIds($db, 'events', 'id', ['team_id' => $teamId]);
        $trainingPlanIds = $this->getIds($db, 'training_plans', 'id', ['team_id' => $teamId]);
        $trainingSessionIds = $this->getIds($db, 'training_sessions', 'id', ['team_id' => $teamId]);
        $matchIds = $this->getIds($db, 'matches', 'id', ['team_id' => $teamId]);
        $boardIds = $this->getIds($db, 'tactical_boards', 'id', ['team_id' => $teamId]);
        $noticeIds = $this->getIds($db, 'notices', 'id', ['team_id' => $teamId]);
        $teamRoleIds = $this->getIds($db, 'roles', 'id', ['team_id' => $teamId]);

        $documentIds = [];
        if ($db->tableExists('documents')) {
            $builder = $db->table('documents')->select('id');
            $builder->groupStart();
            $builder->where('team_id', $teamId);
            if ($athleteIds !== []) {
                $builder->orWhereIn('athlete_id', $athleteIds);
            }
            $builder->groupEnd();
            $documentIds = array_map('intval', array_column($builder->get()->getResultArray(), 'id'));
        }

        if ($documentIds !== []) {
            $filePaths = array_merge($filePaths, $this->getFilePaths($db, 'documents', 'file_path', $documentIds));
            $this->deleteByIds($db, 'document_alerts', 'document_id', $documentIds);
            $this->deleteByIds($db, 'documents', 'id', $documentIds);
        }

        if ($noticeIds !== []) {
            $this->deleteByIds($db, 'notice_reads', 'notice_id', $noticeIds);
            $this->deleteByIds($db, 'notice_replies', 'notice_id', $noticeIds);
            $this->deleteByIds($db, 'notices', 'id', $noticeIds);
        }

        if ($eventIds !== []) {
            $this->deleteByIds($db, 'attendance', 'event_id', $eventIds);
            $this->deleteByIds($db, 'event_participants', 'event_id', $eventIds);
            $this->deleteByIds($db, 'events', 'id', $eventIds);
        }

        if ($trainingSessionIds !== []) {
            $this->deleteByIds($db, 'training_session_athletes', 'training_session_id', $trainingSessionIds);
            $this->deleteByIds($db, 'training_sessions', 'id', $trainingSessionIds);
        }

        if ($trainingPlanIds !== []) {
            $filePaths = array_merge($filePaths, $this->getFilePaths($db, 'training_plan_blocks', 'media_path', $trainingPlanIds, 'training_plan_id'));
            $this->deleteByIds($db, 'training_plan_blocks', 'training_plan_id', $trainingPlanIds);
            $this->deleteByIds($db, 'training_plans', 'id', $trainingPlanIds);
        }

        if ($matchIds !== []) {
            $filePaths = array_merge($filePaths, $this->getFilePaths($db, 'match_attachments', 'file_path', $matchIds, 'match_id'));
            $this->deleteByIds($db, 'match_attachments', 'match_id', $matchIds);
            $this->deleteByIds($db, 'match_reports', 'match_id', $matchIds);
            $this->deleteByIds($db, 'match_events', 'match_id', $matchIds);
            $this->deleteByIds($db, 'match_lineup_positions', 'match_id', $matchIds);
            $this->deleteByIds($db, 'match_callups', 'match_id', $matchIds);
            $this->deleteByIds($db, 'matches', 'id', $matchIds);
        }

        if ($boardIds !== []) {
            $sequenceIds = $this->getIds($db, 'tactical_sequences', 'id', ['tactical_board_id' => $boardIds]);
            if ($sequenceIds !== []) {
                $this->deleteByIds($db, 'tactical_sequence_frames', 'tactical_sequence_id', $sequenceIds);
                $this->deleteByIds($db, 'tactical_sequences', 'id', $sequenceIds);
            }
            $this->deleteByIds($db, 'tactical_board_states', 'tactical_board_id', $boardIds);
            $this->deleteByIds($db, 'tactical_boards', 'id', $boardIds);
        }

        if ($athleteIds !== []) {
            $guardianIds = $this->getIds($db, 'athlete_guardians', 'guardian_id', ['athlete_id' => $athleteIds]);
            $this->deleteByIds($db, 'athlete_guardians', 'athlete_id', $athleteIds);
            $this->deleteByIds($db, 'athletes', 'id', $athleteIds);

            if ($guardianIds !== []) {
                $builder = $db->table('guardians');
                $builder->whereIn('id', $guardianIds);
                $builder->where('id NOT IN (SELECT guardian_id FROM athlete_guardians)', null, false);
                $builder->delete();
            }
        }

        if ($categoryIds !== []) {
            $this->deleteByIds($db, 'category_required_documents', 'category_id', $categoryIds);
            $this->deleteByIds($db, 'categories', 'id', $categoryIds);
        }

        if ($teamRoleIds !== []) {
            $this->deleteByIds($db, 'role_permissions', 'role_id', $teamRoleIds);
            $this->deleteByIds($db, 'user_roles', 'role_id', $teamRoleIds);
            $this->deleteByIds($db, 'roles', 'id', $teamRoleIds);
        }

        $this->deleteByIds($db, 'user_team_links', 'team_id', [$teamId]);

        return $filePaths;
    }

    protected function getIds($db, string $table, string $column, array $where): array
    {
        if (!$db->tableExists($table)) {
            return [];
        }

        $builder = $db->table($table)->select($column);
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                if ($value === []) {
                    return [];
                }
                $builder->whereIn($key, $value);
            } else {
                $builder->where($key, $value);
            }
        }

        return array_map('intval', array_column($builder->get()->getResultArray(), $column));
    }

    protected function deleteByIds($db, string $table, string $column, array $ids): void
    {
        if ($ids === [] || !$db->tableExists($table)) {
            return;
        }

        $db->table($table)->whereIn($column, $ids)->delete();
    }

    protected function getFilePaths($db, string $table, string $column, array $ids, string $idColumn = 'id'): array
    {
        if ($ids === [] || !$db->tableExists($table)) {
            return [];
        }

        $rows = $db->table($table)->select($column)->whereIn($idColumn, $ids)->get()->getResultArray();
        $paths = [];
        foreach ($rows as $row) {
            $value = $row[$column] ?? null;
            if ($value) {
                $paths[] = (string) $value;
            }
        }

        return $paths;
    }

    protected function deleteFileIfExists(string $path): void
    {
        $normalized = ltrim($path, '/\\');
        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalized);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
