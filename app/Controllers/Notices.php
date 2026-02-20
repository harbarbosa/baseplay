<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Services\NoticeReadService;
use App\Services\NoticeReplyService;
use App\Services\NoticeService;
use App\Services\TeamService;
use Config\Services;

class Notices extends BaseController
{
    protected NoticeService $notices;
    protected NoticeReadService $reads;
    protected NoticeReplyService $replies;
    protected TeamService $teams;
    protected CategoryService $categories;

    public function __construct()
    {
        $this->notices = new NoticeService();
        $this->reads = new NoticeReadService();
        $this->replies = new NoticeReplyService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'priority' => $this->request->getGet('priority'),
            'status' => $this->request->getGet('status'),
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
        ];

        $isElevated = has_permission('notices.publish') || has_permission('admin.access');
        $result = $this->notices->list(
            $filters,
            15,
            'notices',
            $isElevated ? null : (int) session('user_id'),
            !$isElevated
        );

        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $teamId = (int) ($filters['team_id'] ?? 0);
        $categories = $this->categories->listAll($teamId > 0 ? $teamId : null);

        return view('notices/index', [
            'title' => 'Avisos',
            'notices' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $teamId = (int) $this->request->getGet('team_id');
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll($teamId > 0 ? $teamId : null);

        return view('notices/create', [
            'title' => 'Novo aviso',
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
        ]);
    }

    public function store()
    {
        $payload = $this->request->getPost();
        $payload['publish_at'] = $this->normalizeDateTime($payload['publish_at'] ?? null);
        $payload['expires_at'] = $this->normalizeDateTime($payload['expires_at'] ?? null);

        $validation = service('validation');
        $validation->setRules(config('Validation')->noticeCreate, config('Validation')->noticeCreate_errors);
        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['publish_at'] ?? null, $payload['expires_at'] ?? null)) {
            return redirect()->back()->withInput()->with('error', 'A data de expiração deve ser maior ou igual à publicação.');
        }

        if (($payload['status'] ?? 'published') === 'published' && !has_permission('notices.publish')) {
            return redirect()->back()->withInput()->with('error', 'Você não tem permissão para publicar avisos.');
        }

        $payload['status'] = $payload['status'] ?? (has_permission('notices.publish') ? 'published' : 'draft');
        $noticeId = $this->notices->create($payload, (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'notice_created', ['notice_id' => $noticeId]);

        return redirect()->to('/notices/' . $noticeId)->with('success', 'Aviso criado com sucesso.');
    }

    public function show(int $id)
    {
        $notice = $this->notices->findWithRelations($id);
        if (!$notice) {
            return redirect()->to('/notices')->with('error', 'Aviso não encontrado.');
        }

        $isElevated = has_permission('notices.publish') || has_permission('admin.access');
        if (!$isElevated && !$this->notices->userCanAccessNotice((int) session('user_id'), $notice)) {
            return redirect()->to('/notices')->with('error', 'Sem permissão para acessar este aviso.');
        }

        return view('notices/show', [
            'title' => 'Aviso',
            'notice' => $notice,
            'read' => $this->reads->isRead($id, (int) session('user_id')),
            'readers' => $isElevated ? $this->reads->listReaders($id) : [],
            'replies' => $this->replies->listByNotice($id),
        ]);
    }

    public function edit(int $id)
    {
        $notice = $this->notices->find($id);
        if (!$notice) {
            return redirect()->to('/notices')->with('error', 'Aviso não encontrado.');
        }

        $teamId = (int) ($notice['team_id'] ?? 0);
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll($teamId > 0 ? $teamId : null);

        return view('notices/edit', [
            'title' => 'Editar aviso',
            'notice' => $notice,
            'teams' => $teams,
            'categories' => $categories,
            'team_id' => $teamId,
        ]);
    }

    public function update(int $id)
    {
        $notice = $this->notices->find($id);
        if (!$notice) {
            return redirect()->to('/notices')->with('error', 'Aviso não encontrado.');
        }

        $payload = $this->request->getPost();
        $payload['publish_at'] = $this->normalizeDateTime($payload['publish_at'] ?? null);
        $payload['expires_at'] = $this->normalizeDateTime($payload['expires_at'] ?? null);

        $validation = service('validation');
        $validation->setRules(config('Validation')->noticeUpdate, config('Validation')->noticeCreate_errors);
        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if (!$this->validateDateRange($payload['publish_at'] ?? null, $payload['expires_at'] ?? null)) {
            return redirect()->back()->withInput()->with('error', 'A data de expiração deve ser maior ou igual à publicação.');
        }

        if (($payload['status'] ?? $notice['status']) === 'published' && !has_permission('notices.publish')) {
            return redirect()->back()->withInput()->with('error', 'Você não tem permissão para publicar avisos.');
        }

        $this->notices->update($id, $payload);
        Services::audit()->log(session('user_id'), 'notice_updated', ['notice_id' => $id]);

        return redirect()->to('/notices/' . $id)->with('success', 'Aviso atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $notice = $this->notices->find($id);
        if (!$notice) {
            return redirect()->to('/notices')->with('error', 'Aviso não encontrado.');
        }

        return view('notices/delete', ['title' => 'Excluir aviso', 'notice' => $notice]);
    }

    public function delete(int $id)
    {
        $notice = $this->notices->find($id);
        if (!$notice) {
            return redirect()->to('/notices')->with('error', 'Aviso não encontrado.');
        }

        $this->notices->delete($id);
        Services::audit()->log(session('user_id'), 'notice_deleted', ['notice_id' => $id]);
        return redirect()->to('/notices')->with('success', 'Aviso removido.');
    }

    public function markRead(int $id)
    {
        $notice = $this->notices->findWithRelations($id);
        if (!$notice) {
            return redirect()->to('/notices')->with('error', 'Aviso não encontrado.');
        }

        $isElevated = has_permission('notices.publish') || has_permission('admin.access');
        if (!$isElevated && !$this->notices->userCanAccessNotice((int) session('user_id'), $notice)) {
            return redirect()->to('/notices')->with('error', 'Sem permissão para acessar este aviso.');
        }

        $this->reads->markRead($id, (int) session('user_id'));
        return redirect()->back()->with('success', 'Aviso marcado como lido.');
    }

    public function reply(int $id)
    {
        $notice = $this->notices->findWithRelations($id);
        if (!$notice) {
            return redirect()->to('/notices')->with('error', 'Aviso não encontrado.');
        }

        $message = trim((string) $this->request->getPost('message'));
        if ($message === '') {
            return redirect()->back()->with('error', 'Informe uma resposta.');
        }

        $isElevated = has_permission('notices.publish') || has_permission('admin.access');
        if (!$isElevated && !$this->notices->userCanAccessNotice((int) session('user_id'), $notice)) {
            return redirect()->to('/notices')->with('error', 'Sem permissão para acessar este aviso.');
        }

        $this->replies->create($id, (int) session('user_id'), $message);
        return redirect()->back()->with('success', 'Resposta enviada.');
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
