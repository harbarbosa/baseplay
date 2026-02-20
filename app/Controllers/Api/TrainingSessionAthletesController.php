<?php

namespace App\Controllers\Api;

use App\Services\TrainingSessionAthleteService;

class TrainingSessionAthletesController extends BaseApiController
{
    protected TrainingSessionAthleteService $items;

    public function __construct()
    {
        $this->items = new TrainingSessionAthleteService();
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

    public function store(int $sessionId)
    {
        if ($response = $this->ensurePermission('training_sessions.update')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $payload['training_session_id'] = $sessionId;
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingSessionAthleteCreate, config('Validation')->trainingSessionAthleteCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $id = $this->items->createOrUpdate($payload);
        return $this->ok(['id' => $id], 'Registro atualizado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('training_sessions.update')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingSessionAthleteCreate, config('Validation')->trainingSessionAthleteCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $payload['training_session_id'] = $payload['training_session_id'] ?? 0;
        $id = $this->items->createOrUpdate($payload);
        return $this->ok(['id' => $id], 'Registro atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('training_sessions.update')) {
            return $response;
        }

        $this->items->delete($id);
        return $this->ok(['id' => $id], 'Registro removido.');
    }
}