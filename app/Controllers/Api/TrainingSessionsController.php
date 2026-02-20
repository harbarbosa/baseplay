<?php

namespace App\Controllers\Api;

use App\Services\TrainingSessionService;

class TrainingSessionsController extends BaseApiController
{
    protected TrainingSessionService $sessions;

    public function __construct()
    {
        $this->sessions = new TrainingSessionService();
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
        if ($response = $this->ensurePermission('training_sessions.view')) {
            return $response;
        }

        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->sessions->list($filters, $perPage, 'training_sessions_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('training_sessions_api'),
                'pageCount' => $result['pager']->getPageCount('training_sessions_api'),
                'perPage' => $result['pager']->getPerPage('training_sessions_api'),
                'total' => $result['pager']->getTotal('training_sessions_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('training_sessions.view')) {
            return $response;
        }

        $session = $this->sessions->findWithRelations($id);
        if (!$session) {
            return $this->fail('SessÃ£o nÃ£o encontrada.', 404);
        }

        return $this->ok($session);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('training_sessions.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingSessionCreate, config('Validation')->trainingSessionCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('ValidaÃ§Ã£o falhou.', 422, $validation->getErrors());
        }

        $user = $this->apiUser();
        $id = $this->sessions->create($payload, $user ? (int) $user['id'] : 0);
        return $this->ok(['id' => $id], 'SessÃ£o criada.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('training_sessions.update')) {
            return $response;
        }

        $session = $this->sessions->find($id);
        if (!$session) {
            return $this->fail('SessÃ£o nÃ£o encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingSessionCreate, config('Validation')->trainingSessionCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('ValidaÃ§Ã£o falhou.', 422, $validation->getErrors());
        }

        $this->sessions->update($id, $payload);
        return $this->ok(['id' => $id], 'SessÃ£o atualizada.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('training_sessions.delete')) {
            return $response;
        }

        $session = $this->sessions->find($id);
        if (!$session) {
            return $this->fail('SessÃ£o nÃ£o encontrada.', 404);
        }

        $this->sessions->delete($id);
        return $this->ok(['id' => $id], 'SessÃ£o removida.');
    }

    public function athletes(int $id)
    {
        if ($response = $this->ensurePermission('training_sessions.view')) {
            return $response;
        }

        return $this->ok($this->sessions->listAthletes($id));
    }
}
