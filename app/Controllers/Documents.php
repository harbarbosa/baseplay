<?php

namespace App\Controllers;

use App\Services\DocumentService;
use App\Services\DocumentTypeService;
use App\Services\DocumentOverviewService;
use App\Services\TeamService;
use App\Services\AthleteService;
use App\Services\CategoryService;
use App\Models\GuardianModel;
use Config\Services;

class Documents extends BaseController
{
    protected DocumentService $documents;
    protected DocumentTypeService $types;
    protected DocumentOverviewService $overview;
    protected TeamService $teams;
    protected AthleteService $athletes;
    protected CategoryService $categories;

    public function __construct()
    {
        $this->documents = new DocumentService();
        $this->types = new DocumentTypeService();
        $this->teams = new TeamService();
        $this->athletes = new AthleteService();
        $this->categories = new CategoryService();
        $this->overview = new DocumentOverviewService();
    }

    public function index()
    {
        $guardianContext = $this->resolveCurrentGuardianContext();

        $filters = [
            'athlete_id' => $this->request->getGet('athlete_id'),
            'athlete_name' => $this->request->getGet('athlete_name'),
            'team_id' => $this->request->getGet('team_id'),
            'guardian_id' => $this->request->getGet('guardian_id'),
            'category_id' => $this->request->getGet('category_id'),
            'document_type_id' => $this->request->getGet('document_type_id'),
            'status' => $this->request->getGet('status'),
            'expiring_in_days' => $this->request->getGet('expiring_in_days'),
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
            'sort' => $this->request->getGet('sort') ?: 'expires_nearest',
        ];

        if ($guardianContext['is_guardian']) {
            $filters['uploaded_by'] = (int) session('user_id');
            if ($guardianContext['guardian_id']) {
                $filters['guardian_id'] = $guardianContext['guardian_id'];
            }
        }

        $result = $this->documents->list($filters, 15, 'documents');
        $types = $this->types->listAllActive();
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $athletes = $this->athletes->listAllWithRelations();
        $categories = $this->categories->listDistinctAllByTeam(true);
        $statusCounters = $this->documents->statusCounters($filters);
        $complianceByCategory = $this->documents->complianceByCategory(!empty($filters['team_id']) ? (int) $filters['team_id'] : null);

        return view('documents/index', [
            'title' => 'Documentos',
            'documents' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'types' => $types,
            'teams' => $teams,
            'athletes' => $athletes,
            'categories' => $categories,
            'statusCounters' => $statusCounters,
            'complianceByCategory' => $complianceByCategory,
        ]);
    }

    public function overview()
    {
        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'document_type_id' => $this->request->getGet('document_type_id'),
            'status' => $this->request->getGet('status'),
            'days' => $this->request->getGet('days'),
        ];

        $data = $this->overview->overview((int) session('user_id'), $filters);
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctAllByTeam(true);
        $types = $this->types->listAllActive();

        return view('documents/overview', [
            'title' => 'Documentos - Visao geral',
            'filters' => $data['filters'],
            'cards' => $data['cards'],
            'compliance' => $data['compliance'],
            'critical' => $data['critical'],
            'teams' => $teams,
            'categories' => $categories,
            'types' => $types,
        ]);
    }

    public function create()
    {
        $types = $this->types->listAllActive();
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $athletes = $this->athletes->listAllWithRelations();
        $categories = $this->categories->listDistinctAllByTeam(true);
        $guardianContext = $this->resolveCurrentGuardianContext();

        return view('documents/create', [
            'title' => 'Upload de documento',
            'types' => $types,
            'teams' => $teams,
            'athletes' => $athletes,
            'categories' => $categories,
            'guardianContext' => $guardianContext,
        ]);
    }

    public function store()
    {
        $payload = $this->request->getPost();
        $guardianContext = $this->resolveCurrentGuardianContext();

        
        $typeName = trim((string) ($payload['document_type_name'] ?? ''));
        if (empty($payload['document_type_id']) && $typeName !== '') {
            $payload['document_type_id'] = $this->types->findOrCreateByName($typeName);
        }

        $athleteName = trim((string) ($payload['athlete_name'] ?? ''));
        if ($athleteName !== '') {
            $resolved = $this->resolveAthleteId($athleteName, $payload['team_id'] ?? null, $payload['category_id'] ?? null);
            if ($resolved['error']) {
                return redirect()->back()->withInput()->with('error', $resolved['error']);
            }
            $payload['athlete_id'] = $resolved['id'];
        }
        if (empty($payload['team_id']) && !empty($payload['category_id'])) {
            $row = db_connect()->table('categories')->select('team_id')->where('id', (int) $payload['category_id'])->get()->getRowArray();
            if ($row && !empty($row['team_id'])) {
                $payload['team_id'] = (int) $row['team_id'];
            }
        }
        if (empty($payload['team_id']) && !empty($payload['athlete_id'])) {
            $row = db_connect()->table('athletes')
                ->select('teams.id AS team_id')
                ->join('categories', 'categories.id = athletes.category_id', 'left')
                ->join('teams', 'teams.id = categories.team_id', 'left')
                ->where('athletes.id', (int) $payload['athlete_id'])
                ->get()->getRowArray();
            if ($row && !empty($row['team_id'])) {
                $payload['team_id'] = (int) $row['team_id'];
            }
        }

        if ($guardianContext['is_guardian']) {
            $payload['guardian_id'] = $guardianContext['guardian_id'] ?: null;
            $payload['athlete_id'] = $payload['athlete_id'] ?? null;
            $payload['team_id'] = $payload['team_id'] ?? null;
            if (empty($payload['guardian_id'])) {
                return redirect()->back()->withInput()->with('error', 'Seu usuário não está vinculado a um responsável. Peça ao admin para vincular o mesmo e-mail.');
            }
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->documentCreate, config('Validation')->documentCreate_errors);

        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $file = $this->request->getFile('document_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Arquivo inválido.');
        }

        $type = $this->types->find((int) $payload['document_type_id']);
        if ($type && !empty($type['requires_expiration']) && empty($payload['expires_at'])) {
            return redirect()->back()->withInput()->with('error', 'Este tipo de documento exige vencimento.');
        }

        if (!empty($payload['issued_at']) && !empty($payload['expires_at'])) {
            if (strtotime($payload['expires_at']) < strtotime($payload['issued_at'])) {
                return redirect()->back()->withInput()->with('error', 'A data de vencimento deve ser maior ou igual à data de emissão.');
            }
        }

        $fileData = $this->storeFile($file);
        $docId = $this->documents->create($payload, $fileData, (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'document_uploaded', ['document_id' => $docId]);

        return redirect()->to('/documents/' . $docId)->with('success', 'Documento enviado com sucesso.');
    }

    public function show(int $id)
    {
        $document = $this->documents->findWithRelations($id);
        if (!$document) {
            return redirect()->to('/documents')->with('error', 'Documento não encontrado.');
        }

        if (!$this->documents->userCanAccessDocument((int) session('user_id'), $document)) {
            return redirect()->to('/documents')->with('error', 'Sem permissão para acessar este documento.');
        }

        return view('documents/show', [
            'title' => 'Documento',
            'document' => $document,
        ]);
    }

    public function edit(int $id)
    {
        $document = $this->documents->findWithRelations($id);
        if (!$document) {
            return redirect()->to('/documents')->with('error', 'Documento não encontrado.');
        }

        $types = $this->types->listAllActive();
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $athletes = $this->athletes->listAllWithRelations();
        $categories = $this->categories->listDistinctAllByTeam(true);

        return view('documents/edit', [
            'title' => 'Editar documento',
            'document' => $document,
            'types' => $types,
            'teams' => $teams,
            'athletes' => $athletes,
            'categories' => $categories,
            'guardianContext' => $this->resolveCurrentGuardianContext(),
        ]);
    }

    public function update(int $id)
    {
        $document = $this->documents->findWithRelations($id);
        if (!$document) {
            return redirect()->to('/documents')->with('error', 'Documento não encontrado.');
        }

        $payload = $this->request->getPost();
        $guardianContext = $this->resolveCurrentGuardianContext();
        $typeName = trim((string) ($payload['document_type_name'] ?? ''));
        if (empty($payload['document_type_id']) && $typeName !== '') {
            $payload['document_type_id'] = $this->types->findOrCreateByName($typeName);
        }

        $athleteName = trim((string) ($payload['athlete_name'] ?? ''));
        if ($athleteName !== '') {
            $resolved = $this->resolveAthleteId($athleteName, $payload['team_id'] ?? null, $payload['category_id'] ?? null);
            if ($resolved['error']) {
                return redirect()->back()->withInput()->with('error', $resolved['error']);
            }
            $payload['athlete_id'] = $resolved['id'];
        }
        if (empty($payload['team_id']) && !empty($payload['category_id'])) {
            $row = db_connect()->table('categories')->select('team_id')->where('id', (int) $payload['category_id'])->get()->getRowArray();
            if ($row && !empty($row['team_id'])) {
                $payload['team_id'] = (int) $row['team_id'];
            }
        }
        if (empty($payload['team_id']) && !empty($payload['athlete_id'])) {
            $row = db_connect()->table('athletes')
                ->select('teams.id AS team_id')
                ->join('categories', 'categories.id = athletes.category_id', 'left')
                ->join('teams', 'teams.id = categories.team_id', 'left')
                ->where('athletes.id', (int) $payload['athlete_id'])
                ->get()->getRowArray();
            if ($row && !empty($row['team_id'])) {
                $payload['team_id'] = (int) $row['team_id'];
            }
        }

        if ($guardianContext['is_guardian']) {
            $payload['guardian_id'] = $guardianContext['guardian_id'] ?: null;
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->documentUpdate, config('Validation')->documentCreate_errors);

        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $type = $this->types->find((int) $payload['document_type_id']);
        if ($type && !empty($type['requires_expiration']) && empty($payload['expires_at'])) {
            return redirect()->back()->withInput()->with('error', 'Este tipo de documento exige vencimento.');
        }

        if (!empty($payload['issued_at']) && !empty($payload['expires_at'])) {
            if (strtotime($payload['expires_at']) < strtotime($payload['issued_at'])) {
                return redirect()->back()->withInput()->with('error', 'A data de vencimento deve ser maior ou igual à data de emissão.');
            }
        }

        $file = $this->request->getFile('document_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileData = $this->storeFile($file);
            $this->documents->replaceFile($id, $fileData);
        }

        $this->documents->updateMeta($id, $payload);
        Services::audit()->log(session('user_id'), 'document_updated', ['document_id' => $id]);

        return redirect()->to('/documents/' . $id)->with('success', 'Documento atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $document = $this->documents->findWithRelations($id);
        if (!$document) {
            return redirect()->to('/documents')->with('error', 'Documento não encontrado.');
        }

        return view('documents/delete', ['title' => 'Excluir documento', 'document' => $document]);
    }

    public function delete(int $id)
    {
        $document = $this->documents->find($id);
        if (!$document) {
            return redirect()->to('/documents')->with('error', 'Documento não encontrado.');
        }

        $this->documents->delete($id);
        Services::audit()->log(session('user_id'), 'document_deleted', ['document_id' => $id]);

        return redirect()->to('/documents')->with('success', 'Documento removido.');
    }

    public function download(int $id)
    {
        $document = $this->documents->findWithRelations($id);
        if (!$document) {
            return redirect()->to('/documents')->with('error', 'Documento não encontrado.');
        }

        if (!$this->documents->userCanAccessDocument((int) session('user_id'), $document)) {
            return redirect()->to('/documents')->with('error', 'Sem permissão para acessar este documento.');
        }

        $path = WRITEPATH . 'uploads/' . $document['file_path'];
        if (!is_file($path)) {
            return redirect()->to('/documents')->with('error', 'Arquivo não encontrado.');
        }

        $downloadName = $document['original_name'] ?? basename($path);
        return $this->response->download($path, null)->setFileName($downloadName);
    }

    protected function storeFile($file): array
    {
        $folder = 'documents/' . date('Y') . '/' . date('m');
        $targetDir = WRITEPATH . 'uploads/' . $folder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return [
            'file_path' => $folder . '/' . $newName,
            'original_name' => $file->getClientName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    protected function resolveAthleteId(string $name, $teamId = null, $categoryId = null): array
    {
        $normalize = function (string $value): string {
            $value = mb_strtolower(trim(preg_replace('/\s+/', ' ', $value)));
            $trans = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            if ($trans !== false) {
                $value = $trans;
            }
            return $value;
        };

        $normalized = $normalize($name);
        if ($normalized === '') {
            return ['id' => null, 'error' => 'Informe o nome do atleta.'];
        }

        $builder = db_connect()->table('athletes')
            ->select('athletes.id, athletes.first_name, athletes.last_name')
            ->join('categories', 'categories.id = athletes.category_id', 'left')
            ->join('teams', 'teams.id = categories.team_id', 'left')
            ->where('athletes.deleted_at', null);

        if (!empty($teamId)) {
            $builder->where('teams.id', (int) $teamId);
        }
        if (!empty($categoryId)) {
            $builder->where('categories.id', (int) $categoryId);
        }

        $rows = $builder->get()->getResultArray();
        $exactMatches = [];
        $partialMatches = [];
        foreach ($rows as $row) {
            $full = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $fullNorm = $normalize($full);
            if ($fullNorm === $normalized) {
                $exactMatches[] = (int) $row['id'];
            } elseif ($fullNorm !== '' && str_contains($fullNorm, $normalized)) {
                $partialMatches[] = (int) $row['id'];
            }
        }

        if (count($exactMatches) === 1) {
            return ['id' => $exactMatches[0], 'error' => null];
        }

        if (count($exactMatches) > 1) {
            return ['id' => null, 'error' => 'Há mais de um atleta com esse nome. Informe clube e sub para filtrar.'];
        }

        if (count($partialMatches) === 1) {
            return ['id' => $partialMatches[0], 'error' => null];
        }

        if (count($partialMatches) > 1) {
            return ['id' => null, 'error' => 'Há mais de um atleta com nome parecido. Informe clube e sub para filtrar.'];
        }

        return ['id' => null, 'error' => 'Atleta não encontrado. Verifique o nome.'];
    }

    protected function resolveCurrentGuardianContext(): array
    {
        helper('auth');
        $roles = array_map(static fn($r) => mb_strtolower((string) $r), user_roles());
        $isGuardian = in_array('responsavel', $roles, true) || in_array('responsável', $roles, true) || in_array('responsã¡vel', $roles, true);

        if (!$isGuardian) {
            return ['is_guardian' => false, 'guardian_id' => null, 'guardian_name' => null];
        }

        $email = (string) session('user_email');
        $guardian = null;
        if ($email !== '') {
            $guardian = (new GuardianModel())
                ->where('deleted_at', null)
                ->where('email', $email)
                ->first();
        }

        return [
            'is_guardian' => true,
            'guardian_id' => $guardian['id'] ?? null,
            'guardian_name' => $guardian['full_name'] ?? null,
        ];
    }
}
