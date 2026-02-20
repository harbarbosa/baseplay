<?php

namespace App\Controllers\Api;

use App\Services\TrainingPlanService;
use App\Services\TrainingPlanBlockService;

class TrainingPlansController extends BaseApiController
{
    protected TrainingPlanService $plans;
    protected TrainingPlanBlockService $blocks;

    public function __construct()
    {
        $this->plans = new TrainingPlanService();
        $this->blocks = new TrainingPlanBlockService();
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
        if ($response = $this->ensurePermission('training_plans.view')) {
            return $response;
        }

        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'planned_date_from' => $this->request->getGet('planned_date_from'),
            'planned_date_to' => $this->request->getGet('planned_date_to'),
            'status' => $this->request->getGet('status'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->plans->list($filters, $perPage, 'training_plans_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('training_plans_api'),
                'pageCount' => $result['pager']->getPageCount('training_plans_api'),
                'perPage' => $result['pager']->getPerPage('training_plans_api'),
                'total' => $result['pager']->getTotal('training_plans_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('training_plans.view')) {
            return $response;
        }

        $plan = $this->plans->findWithRelations($id);
        if (!$plan) {
            return $this->fail('Plano não encontrado.', 404);
        }

        return $this->ok($plan);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('training_plans.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanCreate, config('Validation')->trainingPlanCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $user = $this->apiUser();
        $id = $this->plans->create($payload, $user ? (int) $user['id'] : 0);
        return $this->ok(['id' => $id], 'Plano criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('training_plans.update')) {
            return $response;
        }

        $plan = $this->plans->find($id);
        if (!$plan) {
            return $this->fail('Plano não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanCreate, config('Validation')->trainingPlanCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $this->plans->update($id, $payload);
        return $this->ok(['id' => $id], 'Plano atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('training_plans.delete')) {
            return $response;
        }

        $plan = $this->plans->find($id);
        if (!$plan) {
            return $this->fail('Plano não encontrado.', 404);
        }

        $this->plans->delete($id);
        return $this->ok(['id' => $id], 'Plano removido.');
    }

    public function blocks(int $planId)
    {
        if ($response = $this->ensurePermission('training_plans.view')) {
            return $response;
        }

        return $this->ok($this->plans->listBlocks($planId));
    }

    public function storeBlock(int $planId)
    {
        if ($response = $this->ensurePermission('training_plans.update')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $payload['training_plan_id'] = $planId;
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanBlockCreate, config('Validation')->trainingPlanBlockCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $id = $this->blocks->create($payload);
        $this->plans->recalcTotalDuration($planId);

        return $this->ok(['id' => $id], 'Bloco criado.', 201);
    }
}
