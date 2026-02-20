<?php

namespace App\Controllers\Api;

use App\Services\DocumentService;
use App\Services\DocumentTypeService;

class DocumentsController extends BaseApiController
{
    protected DocumentService $documents;
    protected DocumentTypeService $types;

    public function __construct()
    {
        $this->documents = new DocumentService();
        $this->types = new DocumentTypeService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ])->setStatusCode($code);
    }

    protected function fail(string $message, int $code = 400, $errors = null)
    {
        return service('response')->setJSON([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ])->setStatusCode($code);
    }

    public function index()
    {
        if ($response = $this->ensurePermission('documents.view')) {
            return $response;
        }

        $user = $this->apiUser();
        $isGuardian = $this->apiUserHasRole('responsavel', $user);
        $guardianIdFromUser = $isGuardian ? $this->resolveGuardianIdFromApiUser($user ?? []) : null;

        $filters = [
            'athlete_id' => $this->request->getGet('athlete_id'),
            'guardian_id' => $this->request->getGet('guardian_id'),
            'team_id' => $this->request->getGet('team_id'),
            'document_type_id' => $this->request->getGet('document_type_id'),
            'status' => $this->request->getGet('status'),
            'expiring_in_days' => $this->request->getGet('expiring_in_days'),
        ];

        if ($isGuardian) {
            // Responsável só pode listar documentos próprios.
            $filters['guardian_id'] = $guardianIdFromUser ?? -1;
            $filters['team_id'] = null;
        }

        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->documents->list($filters, $perPage, 'documents_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('documents_api'),
                'pageCount' => $result['pager']->getPageCount('documents_api'),
                'perPage' => $result['pager']->getPerPage('documents_api'),
                'total' => $result['pager']->getTotal('documents_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('documents.view')) {
            return $response;
        }

        $doc = $this->documents->findWithRelations($id);
        if (!$doc) {
            return $this->fail('Documento nao encontrado.', 404);
        }

        $user = $this->apiUser();
        if ($this->apiUserHasRole('responsavel', $user) && !$this->guardianCanAccessDocument($doc)) {
            return $this->fail('Acesso negado', 403);
        }

        return $this->ok($doc);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('documents.upload')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $payload = $this->request->getPost();
        $isGuardian = $this->apiUserHasRole('responsavel', $user);
        $guardianId = $this->resolveGuardianIdFromApiUser($user);
        if ($isGuardian) {
            $payload['guardian_id'] = $guardianId;
            $payload['team_id'] = null;
        } elseif (empty($payload['guardian_id']) && $guardianId !== null) {
            $payload['guardian_id'] = $guardianId;
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->documentCreate, config('Validation')->documentCreate_errors);
        if (!$validation->run($payload)) {
            return $this->fail('Validacao falhou.', 422, $validation->getErrors());
        }

        if (empty($payload['athlete_id']) && empty($payload['team_id']) && empty($payload['guardian_id'])) {
            return $this->fail('Informe um atleta, responsavel ou equipe.', 422);
        }

        if ($isGuardian && !$this->guardianCanUseAthlete($guardianId, (int) ($payload['athlete_id'] ?? 0))) {
            return $this->fail('Acesso negado', 403);
        }

        $file = $this->request->getFile('document_file');
        if (!$file || !$file->isValid()) {
            return $this->fail('Arquivo invalido.', 422);
        }

        $type = $this->types->find((int) $payload['document_type_id']);
        if ($type && !empty($type['requires_expiration']) && empty($payload['expires_at'])) {
            return $this->fail('Este tipo de documento exige vencimento.', 422);
        }

        if (!empty($payload['issued_at']) && !empty($payload['expires_at'])) {
            if (strtotime($payload['expires_at']) < strtotime($payload['issued_at'])) {
                return $this->fail('A data de vencimento deve ser maior ou igual a data de emissao.', 422);
            }
        }

        $fileData = $this->storeFile($file);
        $docId = $this->documents->create($payload, $fileData, (int) $user['id']);

        return $this->ok(['id' => $docId], 'Documento enviado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('documents.update')) {
            return $response;
        }

        $doc = $this->documents->findWithRelations($id);
        if (!$doc) {
            return $this->fail('Documento nao encontrado.', 404);
        }

        $user = $this->apiUser();
        $isGuardian = $this->apiUserHasRole('responsavel', $user);
        if ($isGuardian && !$this->guardianCanAccessDocument($doc)) {
            return $this->fail('Acesso negado', 403);
        }

        $payload = $this->request->getPost();
        if (empty($payload['guardian_id']) && !empty($doc['guardian_id'])) {
            $payload['guardian_id'] = $doc['guardian_id'];
        }
        if ($isGuardian) {
            $payload['guardian_id'] = $this->resolveGuardianIdFromApiUser($user ?? []);
            $payload['team_id'] = null;
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->documentUpdate, config('Validation')->documentCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validacao falhou.', 422, $validation->getErrors());
        }

        if (empty($payload['athlete_id']) && empty($payload['team_id']) && empty($payload['guardian_id'])) {
            return $this->fail('Informe um atleta, responsavel ou equipe.', 422);
        }

        if ($isGuardian && !$this->guardianCanUseAthlete((int) ($payload['guardian_id'] ?? 0), (int) ($payload['athlete_id'] ?? 0))) {
            return $this->fail('Acesso negado', 403);
        }

        $type = $this->types->find((int) $payload['document_type_id']);
        if ($type && !empty($type['requires_expiration']) && empty($payload['expires_at'])) {
            return $this->fail('Este tipo de documento exige vencimento.', 422);
        }

        if (!empty($payload['issued_at']) && !empty($payload['expires_at'])) {
            if (strtotime($payload['expires_at']) < strtotime($payload['issued_at'])) {
                return $this->fail('A data de vencimento deve ser maior ou igual a data de emissao.', 422);
            }
        }

        $file = $this->request->getFile('document_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileData = $this->storeFile($file);
            $this->documents->replaceFile($id, $fileData);
        }

        $this->documents->updateMeta($id, $payload);

        return $this->ok(['id' => $id], 'Documento atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('documents.delete')) {
            return $response;
        }

        $doc = $this->documents->findWithRelations($id);
        if (!$doc) {
            return $this->fail('Documento nao encontrado.', 404);
        }

        $user = $this->apiUser();
        if ($this->apiUserHasRole('responsavel', $user) && !$this->guardianCanAccessDocument($doc)) {
            return $this->fail('Acesso negado', 403);
        }

        $this->documents->delete($id);
        return $this->ok(['id' => $id], 'Documento removido.');
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

    protected function resolveGuardianIdFromApiUser(array $user): ?int
    {
        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '') {
            return null;
        }

        $row = db_connect()->table('guardians')
            ->select('id')
            ->where('deleted_at', null)
            ->where('email', $email)
            ->get()
            ->getRowArray();

        return $row ? (int) $row['id'] : null;
    }

    protected function guardianCanUseAthlete(?int $guardianId, int $athleteId): bool
    {
        if (($guardianId ?? 0) <= 0 || $athleteId <= 0) {
            return true;
        }

        $link = db_connect()->table('athlete_guardians')
            ->select('id')
            ->where('guardian_id', (int) $guardianId)
            ->where('athlete_id', $athleteId)
            ->get()
            ->getRowArray();

        return !empty($link);
    }

    protected function guardianCanAccessDocument(array $document): bool
    {
        $user = $this->apiUser();
        $guardianId = $this->resolveGuardianIdFromApiUser($user ?? []);
        if (($guardianId ?? 0) <= 0) {
            return false;
        }

        if (!empty($document['guardian_id']) && (int) $document['guardian_id'] === (int) $guardianId) {
            return true;
        }

        return $this->guardianCanUseAthlete((int) $guardianId, (int) ($document['athlete_id'] ?? 0));
    }
}
