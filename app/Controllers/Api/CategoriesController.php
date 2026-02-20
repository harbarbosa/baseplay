<?php

namespace App\Controllers\Api;

use App\Services\CategoryService;
use App\Services\TeamService;

class CategoriesController extends BaseApiController
{
    protected CategoryService $categories;
    protected TeamService $teams;

    public function __construct()
    {
        $this->categories = new CategoryService();
        $this->teams = new TeamService();
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

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('categories.view')) {
            return $response;
        }

        $category = $this->categories->find($id);
        if (!$category) {
            return $this->fail('Categoria não encontrada.', 404);
        }

        return $this->ok($category);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('categories.update')) {
            return $response;
        }

        $category = $this->categories->find($id);
        if (!$category) {
            return $this->fail('Categoria não encontrada.', 404);
        }

        $teamId = (int) $category['team_id'];
        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();

        $validation = service('validation');
        $rules = config('Validation')->categoryUpdate;
        $rules['name'] = str_replace('{team_id}', (string) $teamId, $rules['name']);
        $rules['name'] = str_replace('{id}', (string) $id, $rules['name']);
        $validation->setRules($rules, config('Validation')->categoryCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        if (!$this->validateYearRange($payload)) {
            return $this->fail('Ano inicial não pode ser maior que o ano final.', 422);
        }

        $this->categories->update($id, $payload);
        return $this->ok(['id' => $id], 'Categoria atualizada.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('categories.delete')) {
            return $response;
        }

        $category = $this->categories->find($id);
        if (!$category) {
            return $this->fail('Categoria não encontrada.', 404);
        }

        $this->categories->delete($id);
        return $this->ok(['id' => $id], 'Categoria removida.');
    }

    protected function validateYearRange(array $payload): bool
    {
        if (!empty($payload['year_from']) && !empty($payload['year_to'])) {
            return (int) $payload['year_from'] <= (int) $payload['year_to'];
        }

        return true;
    }
}
