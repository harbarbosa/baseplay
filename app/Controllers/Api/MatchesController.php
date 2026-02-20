<?php

namespace App\Controllers\Api;

use App\Services\MatchService;
use App\Services\MatchCallupService;

class MatchesController extends BaseApiController
{
    protected MatchService $matches;
    protected MatchCallupService $callups;

    public function __construct()
    {
        $this->matches = new MatchService();
        $this->callups = new MatchCallupService();
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
        if ($response = $this->ensurePermission('matches.view')) {
            return $response;
        }

        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'competition_name' => $this->request->getGet('competition_name'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->matches->list($filters, $perPage, 'matches_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('matches_api'),
                'pageCount' => $result['pager']->getPageCount('matches_api'),
                'perPage' => $result['pager']->getPerPage('matches_api'),
                'total' => $result['pager']->getTotal('matches_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('matches.view')) {
            return $response;
        }

        $match = $this->matches->findWithRelations($id);
        if (!$match) {
            return $this->fail('Jogo não encontrado.', 404);
        }

        return $this->ok($match);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('matches.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->matchCreate, config('Validation')->matchCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        if (($payload['status'] ?? 'scheduled') === 'completed') {
            if ($payload['score_for'] === '' || $payload['score_against'] === '') {
                return $this->fail('Informe o placar para jogos concluídos.', 422);
            }
        }

        $user = $this->apiUser();
        $matchId = $this->matches->create($payload, $user  (int) $user['id'] : 0);
        return $this->ok(['id' => $matchId], 'Jogo criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('matches.update')) {
            return $response;
        }

        $match = $this->matches->find($id);
        if (!$match) {
            return $this->fail('Jogo não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->matchCreate, config('Validation')->matchCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $this->matches->update($id, $payload);
        return $this->ok(['id' => $id], 'Jogo atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('matches.delete')) {
            return $response;
        }

        $match = $this->matches->find($id);
        if (!$match) {
            return $this->fail('Jogo não encontrado.', 404);
        }

        $this->matches->delete($id);
        return $this->ok(['id' => $id], 'Jogo removido.');
    }

    public function fromEvent(int $eventId)
    {
        if ($response = $this->ensurePermission('matches.create')) {
            return $response;
        }

        $user = $this->apiUser();
        $matchId = $this->matches->createFromEvent($eventId, $user  (int) $user['id'] : 0);
        if (!$matchId) {
            return $this->fail('Evento inválido para criação de jogo.', 422);
        }

        $this->callups->addFromEventParticipants($matchId, $eventId);
        return $this->ok(['id' => $matchId], 'Jogo criado a partir do evento.', 201);
    }

    public function confirm(int $matchId)
    {
        $user = $this->apiUser();
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $allowed = \Config\Services::rbac()->userHasPermission((int) $user['id'], 'matches.view');
        if (!$allowed) {
            return $this->fail('Forbidden', 403);
        }

        $match = $this->matches->find($matchId);
        if (!$match) {
            return $this->fail('Jogo não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $athleteId = (int) ($payload['athlete_id'] ?? 0);
        $status = $payload['callup_status'] ?? 'pending';

        if ($athleteId <= 0) {
            return $this->fail('Atleta inválido.', 422);
        }

        $callupId = $this->callups->addParticipant($matchId, $athleteId, $status);
        return $this->ok(['id' => $callupId], 'Convocação atualizada.');
    }
}
