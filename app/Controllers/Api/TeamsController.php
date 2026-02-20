<?php

namespace App\Controllers\Api;

use App\Services\TeamService;
use App\Services\CategoryService;

class TeamsController extends BaseApiController
{
    protected TeamService $teams;
    protected CategoryService $categories;

    public function __construct()
    {
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
    }

    protected function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return service('response')->setJSON([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ])->setStatusCode($code);
    }

    protected function fail(string $message, int $code = 400, $errors = null)
    {
        return service('response')->setJSON([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ])->setStatusCode($code);
    }

    public function index()
    {
        if ($response = $this->ensurePermission('teams.view')) {
            return $response;
        }

        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->teams->list($filters, $perPage, 'teams_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('teams_api'),
                'pageCount'   => $result['pager']->getPageCount('teams_api'),
                'perPage'     => $result['pager']->getPerPage('teams_api'),
                'total'       => $result['pager']->getTotal('teams_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('teams.view')) {
            return $response;
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return $this->fail('Equipe não encontrada.', 404);
        }

        return $this->ok($team);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('teams.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->teamCreate, config('Validation')->teamCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $teamId = $this->teams->create($payload);
        return $this->ok(['id' => $teamId], 'Equipe criada.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('teams.update')) {
            return $response;
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return $this->fail('Equipe não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $rules = config('Validation')->teamUpdate;
        $rules['name'] = str_replace('{id}', (string) $id, $rules['name']);
        $validation->setRules($rules, config('Validation')->teamCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $this->teams->update($id, $payload);
        return $this->ok(['id' => $id], 'Equipe atualizada.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('teams.delete')) {
            return $response;
        }

        $team = $this->teams->find($id);
        if (!$team) {
            return $this->fail('Equipe não encontrada.', 404);
        }

        $this->teams->delete($id);
        return $this->ok(['id' => $id], 'Equipe removida.');
    }

    public function categories(int $teamId)
    {
        if ($response = $this->ensurePermission('categories.view')) {
            return $response;
        }

        $team = $this->teams->find($teamId);
        if (!$team) {
            return $this->fail('Equipe não encontrada.', 404);
        }

        $items = $this->categories->listByTeam($teamId);
        return $this->ok($items);
    }

    public function storeCategory(int $teamId)
    {
        if ($response = $this->ensurePermission('categories.create')) {
            return $response;
        }

        $team = $this->teams->find($teamId);
        if (!$team) {
            return $this->fail('Equipe não encontrada.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $payload['team_id'] = $teamId;

        $validation = service('validation');
        $rules = config('Validation')->categoryCreate;
        $rules['name'] = str_replace('{team_id}', (string) $teamId, $rules['name']);
        $validation->setRules($rules, config('Validation')->categoryCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        if (!$this->validateYearRange($payload)) {
            return $this->fail('Ano inicial não pode ser maior que o ano final.', 422);
        }

        $categoryId = $this->categories->create($payload);
        return $this->ok(['id' => $categoryId], 'Categoria criada.', 201);
    }

    protected function validateYearRange(array $payload): bool
    {
        if (!empty($payload['year_from']) && !empty($payload['year_to'])) {
            return (int) $payload['year_from'] <= (int) $payload['year_to'];
        }

        return true;
    }
}
