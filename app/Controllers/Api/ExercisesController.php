<?php

namespace App\Controllers\Api;

use App\Services\ExerciseService;

class ExercisesController extends BaseApiController
{
    protected ExerciseService $exercises;

    public function __construct()
    {
        $this->exercises = new ExerciseService();
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
        if ($response = $this->ensurePermission('exercises.view')) {
            return $response;
        }

        $filters = [
            'search' => $this->request->getGet('search'),
            'objective' => $this->request->getGet('objective'),
            'age_group' => $this->request->getGet('age_group'),
            'intensity' => $this->request->getGet('intensity'),
            'tag' => $this->request->getGet('tag'),
            'status' => $this->request->getGet('status'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->exercises->list($filters, $perPage, 'exercises_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('exercises_api'),
                'pageCount' => $result['pager']->getPageCount('exercises_api'),
                'perPage' => $result['pager']->getPerPage('exercises_api'),
                'total' => $result['pager']->getTotal('exercises_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('exercises.view')) {
            return $response;
        }

        $exercise = $this->exercises->findWithTags($id);
        if (!$exercise) {
            return $this->fail('Exercício não encontrado.', 404);
        }

        return $this->ok($exercise);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('exercises.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->exerciseCreate, config('Validation')->exerciseCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $user = $this->apiUser();
        $id = $this->exercises->create($payload, $user ? (int) $user['id'] : 0);
        return $this->ok(['id' => $id], 'Exercício criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('exercises.update')) {
            return $response;
        }

        $exercise = $this->exercises->find($id);
        if (!$exercise) {
            return $this->fail('Exercício não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->exerciseCreate, config('Validation')->exerciseCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $this->exercises->update($id, $payload);
        return $this->ok(['id' => $id], 'Exercício atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('exercises.delete')) {
            return $response;
        }

        $exercise = $this->exercises->find($id);
        if (!$exercise) {
            return $this->fail('Exercício não encontrado.', 404);
        }

        $this->exercises->delete($id);
        return $this->ok(['id' => $id], 'Exercício removido.');
    }
}
