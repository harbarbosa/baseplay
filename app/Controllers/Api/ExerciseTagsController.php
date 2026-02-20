<?php

namespace App\Controllers\Api;

use App\Services\ExerciseService;

class ExerciseTagsController extends BaseApiController
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

        return $this->ok($this->exercises->listTags());
    }

    public function store()
    {
        if ($response = $this->ensurePermission('exercises.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            return $this->fail('Informe o nome da tag.', 422);
        }

        $id = $this->exercises->createTag($name);
        return $this->ok(['id' => $id], 'Tag criada.', 201);
    }
}