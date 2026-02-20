<?php

namespace App\Controllers\Api;

use App\Services\TrainingPlanBlockService;
use App\Services\TrainingPlanService;

class TrainingPlanBlocksController extends BaseApiController
{
    protected TrainingPlanBlockService $blocks;
    protected TrainingPlanService $plans;

    public function __construct()
    {
        $this->blocks = new TrainingPlanBlockService();
        $this->plans = new TrainingPlanService();
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

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('training_plans.update')) {
            return $response;
        }

        $block = $this->blocks->find($id);
        if (!$block) {
            return $this->fail('Bloco não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $payload['training_plan_id'] = (int) $block['training_plan_id'];
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanBlockCreate, config('Validation')->trainingPlanBlockCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $this->blocks->update($id, $payload);
        $this->plans->recalcTotalDuration((int) $block['training_plan_id']);

        return $this->ok(['id' => $id], 'Bloco atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('training_plans.delete')) {
            return $response;
        }

        $block = $this->blocks->find($id);
        if (!$block) {
            return $this->fail('Bloco não encontrado.', 404);
        }

        $this->blocks->delete($id);
        $this->plans->recalcTotalDuration((int) $block['training_plan_id']);

        return $this->ok(['id' => $id], 'Bloco removido.');
    }
}
