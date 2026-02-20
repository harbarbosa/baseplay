<?php

namespace App\Controllers\Api;

use App\Services\NoticeReadService;
use App\Services\NoticeReplyService;
use App\Services\NoticeService;

class NoticesController extends BaseApiController
{
    protected NoticeService $notices;
    protected NoticeReadService $reads;
    protected NoticeReplyService $replies;

    public function __construct()
    {
        $this->notices = new NoticeService();
        $this->reads = new NoticeReadService();
        $this->replies = new NoticeReplyService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
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
        if ($response = $this->ensurePermission('notices.view')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'priority' => $this->request->getGet('priority'),
            'status' => $this->request->getGet('status'),
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
            'search' => $this->request->getGet('search'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $isElevated = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'notices.publish')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'admin.access');

        $result = $this->notices->list(
            $filters,
            $perPage,
            'notices_api',
            $isElevated ? null : (int) $user['id'],
            !$isElevated
        );

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('notices_api'),
                'pageCount' => $result['pager']->getPageCount('notices_api'),
                'perPage' => $result['pager']->getPerPage('notices_api'),
                'total' => $result['pager']->getTotal('notices_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('notices.view')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $notice = $this->notices->findWithRelations($id);
        if (!$notice) {
            return $this->fail('Aviso não encontrado.', 404);
        }

        $isElevated = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'notices.publish')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'admin.access');
        if (!$isElevated && !$this->notices->userCanAccessNotice((int) $user['id'], $notice)) {
            return $this->fail('Forbidden', 403);
        }

        return $this->ok($notice);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('notices.create')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $payload['publish_at'] = $this->normalizeDateTime($payload['publish_at'] ?? null);
        $payload['expires_at'] = $this->normalizeDateTime($payload['expires_at'] ?? null);

        $validation = service('validation');
        $validation->setRules(config('Validation')->noticeCreate, config('Validation')->noticeCreate_errors);
        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['publish_at'] ?? null, $payload['expires_at'] ?? null)) {
            return $this->fail('A data de expiração deve ser maior ou igual à publicação.', 422);
        }

        $canPublish = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'notices.publish');
        if (($payload['status'] ?? 'published') === 'published' && !$canPublish) {
            return $this->fail('Você não tem permissão para publicar avisos.', 403);
        }

        $payload['status'] = $payload['status'] ?? ($canPublish ? 'published' : 'draft');
        $noticeId = $this->notices->create($payload, (int) $user['id']);
        return $this->ok(['id' => $noticeId], 'Aviso criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('notices.update')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $notice = $this->notices->find($id);
        if (!$notice) {
            return $this->fail('Aviso não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $payload['publish_at'] = $this->normalizeDateTime($payload['publish_at'] ?? null);
        $payload['expires_at'] = $this->normalizeDateTime($payload['expires_at'] ?? null);

        $validation = service('validation');
        $validation->setRules(config('Validation')->noticeUpdate, config('Validation')->noticeCreate_errors);
        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['publish_at'] ?? null, $payload['expires_at'] ?? null)) {
            return $this->fail('A data de expiração deve ser maior ou igual à publicação.', 422);
        }

        $canPublish = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'notices.publish');
        if (($payload['status'] ?? $notice['status']) === 'published' && !$canPublish) {
            return $this->fail('Você não tem permissão para publicar avisos.', 403);
        }

        $this->notices->update($id, $payload);
        return $this->ok(['id' => $id], 'Aviso atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('notices.delete')) {
            return $response;
        }

        $notice = $this->notices->find($id);
        if (!$notice) {
            return $this->fail('Aviso não encontrado.', 404);
        }

        $this->notices->delete($id);
        return $this->ok(['id' => $id], 'Aviso removido.');
    }

    public function read(int $id)
    {
        if ($response = $this->ensurePermission('notices.view')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $notice = $this->notices->findWithRelations($id);
        if (!$notice) {
            return $this->fail('Aviso não encontrado.', 404);
        }

        $isElevated = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'notices.publish')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'admin.access');
        if (!$isElevated && !$this->notices->userCanAccessNotice((int) $user['id'], $notice)) {
            return $this->fail('Forbidden', 403);
        }

        $readId = $this->reads->markRead($id, (int) $user['id']);
        return $this->ok(['id' => $readId], 'Aviso marcado como lido.');
    }

    public function replies(int $id)
    {
        if ($response = $this->ensurePermission('notices.view')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $notice = $this->notices->findWithRelations($id);
        if (!$notice) {
            return $this->fail('Aviso não encontrado.', 404);
        }

        $isElevated = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'notices.publish')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'admin.access');
        if (!$isElevated && !$this->notices->userCanAccessNotice((int) $user['id'], $notice)) {
            return $this->fail('Forbidden', 403);
        }

        return $this->ok($this->replies->listByNotice($id));
    }

    public function reply(int $id)
    {
        if ($response = $this->ensurePermission('notices.view')) {
            return $response;
        }

        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $notice = $this->notices->findWithRelations($id);
        if (!$notice) {
            return $this->fail('Aviso não encontrado.', 404);
        }

        $isElevated = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'notices.publish')
            || \Config\Services::rbac()->userHasPermission((int) $user['id'], 'admin.access');
        if (!$isElevated && !$this->notices->userCanAccessNotice((int) $user['id'], $notice)) {
            return $this->fail('Forbidden', 403);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            return $this->fail('Informe uma resposta.', 422);
        }

        $replyId = $this->replies->create($id, (int) $user['id'], $message);
        return $this->ok(['id' => $replyId], 'Resposta enviada.', 201);
    }

    protected function normalizeDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }
        return $value;
    }

    protected function validateDateRange(?string $start, ?string $end): bool
    {
        if (!$start || !$end) {
            return true;
        }
        return strtotime($end) >= strtotime($start);
    }
}
