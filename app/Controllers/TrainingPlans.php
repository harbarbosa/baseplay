<?php

namespace App\Controllers;

use App\Services\TrainingPlanService;
use App\Services\TrainingPlanBlockService;
use App\Services\ExerciseService;
use App\Services\TeamService;
use App\Services\CategoryService;
use Config\Services;

class TrainingPlans extends BaseController
{
    protected TrainingPlanService $plans;
    protected TrainingPlanBlockService $blocks;
    protected ExerciseService $exercises;
    protected TeamService $teams;
    protected CategoryService $categories;

    public function __construct()
    {
        $this->plans = new TrainingPlanService();
        $this->blocks = new TrainingPlanBlockService();
        $this->exercises = new ExerciseService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
    }

    public function index()
    {
        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'planned_date_from' => $this->request->getGet('planned_date_from'),
            'planned_date_to' => $this->request->getGet('planned_date_to'),
            'status' => $this->request->getGet('status'),
        ];

        $result = $this->plans->list($filters, 15, 'training_plans');
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll();

        return view('training_plans/index', [
            'title' => 'Planos de treino',
            'plans' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function show(int $id)
    {
        $plan = $this->plans->findWithRelations($id);
        if (!$plan) {
            return redirect()->to('/training-plans')->with('error', 'Plano nÃ£o encontrado.');
        }

        $blocks = $this->plans->listBlocks($id);
        $exerciseList = $this->exercises->list([], 200, 'exercise_select')['items'];

        return view('training_plans/show', [
            'title' => 'Plano de treino',
            'plan' => $plan,
            'blocks' => $blocks,
            'exercises' => $exerciseList,
        ]);
    }

    public function create()
    {
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll();

        return view('training_plans/create', [
            'title' => 'Novo plano',
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanCreate, config('Validation')->trainingPlanCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $id = $this->plans->create($this->request->getPost(), (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'training_plan_created', ['training_plan_id' => $id]);

        return redirect()->to('/training-plans/' . $id)->with('success', 'Plano criado.');
    }

    public function edit(int $id)
    {
        $plan = $this->plans->find($id);
        if (!$plan) {
            return redirect()->to('/training-plans')->with('error', 'Plano nÃ£o encontrado.');
        }

        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $categories = $this->categories->listAll();

        return view('training_plans/edit', [
            'title' => 'Editar plano',
            'plan' => $plan,
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function update(int $id)
    {
        $plan = $this->plans->find($id);
        if (!$plan) {
            return redirect()->to('/training-plans')->with('error', 'Plano nÃ£o encontrado.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanCreate, config('Validation')->trainingPlanCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->plans->update($id, $this->request->getPost());
        Services::audit()->log(session('user_id'), 'training_plan_updated', ['training_plan_id' => $id]);

        return redirect()->to('/training-plans/' . $id)->with('success', 'Plano atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $plan = $this->plans->find($id);
        if (!$plan) {
            return redirect()->to('/training-plans')->with('error', 'Plano nÃ£o encontrado.');
        }

        return view('training_plans/delete', ['title' => 'Excluir plano', 'plan' => $plan]);
    }

    public function delete(int $id)
    {
        $plan = $this->plans->find($id);
        if (!$plan) {
            return redirect()->to('/training-plans')->with('error', 'Plano nÃ£o encontrado.');
        }

        $this->plans->delete($id);
        Services::audit()->log(session('user_id'), 'training_plan_deleted', ['training_plan_id' => $id]);

        return redirect()->to('/training-plans')->with('success', 'Plano removido.');
    }

    public function addBlock(int $planId)
    {
        $plan = $this->plans->find($planId);
        if (!$plan) {
            return redirect()->back()->with('error', 'Plano nÃ£o encontrado.');
        }

        $data = $this->request->getPost();
        $data['training_plan_id'] = $planId;
        $data['order_index'] = (int) ($data['order_index'] ?? 1);

        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanBlockCreate, config('Validation')->trainingPlanBlockCreate_errors);

        if (!$validation->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $upload = $this->handleBlockMediaUpload();
        if (!empty($upload['error'])) {
            return redirect()->back()->withInput()->with('error', $upload['error']);
        }
        $data = array_merge($data, $upload['data']);

        $this->blocks->create($data);
        $this->plans->recalcTotalDuration($planId);

        return redirect()->back()->with('success', 'Bloco adicionado.');
    }

    public function updateBlock(int $id)
    {
        $block = $this->blocks->find($id);
        if (!$block) {
            return redirect()->back()->with('error', 'Bloco nÃ£o encontrado.');
        }

        $data = $this->request->getPost();
        $data['training_plan_id'] = (int) $block['training_plan_id'];
        $data['order_index'] = (int) ($data['order_index'] ?? $block['order_index']);

        $validation = service('validation');
        $validation->setRules(config('Validation')->trainingPlanBlockCreate, config('Validation')->trainingPlanBlockCreate_errors);

        if (!$validation->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $upload = $this->handleBlockMediaUpload();
        if (!empty($upload['error'])) {
            return redirect()->back()->withInput()->with('error', $upload['error']);
        }
        $data = array_merge($data, $upload['data']);

        $this->blocks->update($id, $data);
        $this->plans->recalcTotalDuration((int) $block['training_plan_id']);

        return redirect()->back()->with('success', 'Bloco atualizado.');
    }

    public function deleteBlock(int $id)
    {
        $block = $this->blocks->find($id);
        if (!$block) {
            return redirect()->back()->with('error', 'Bloco nÃ£o encontrado.');
        }

        $this->blocks->delete($id);
        $this->plans->recalcTotalDuration((int) $block['training_plan_id']);

        return redirect()->back()->with('success', 'Bloco removido.');
    }

    public function downloadBlockMedia(int $id)
    {
        $block = $this->blocks->find($id);
        if (!$block || empty($block['media_path'])) {
            return redirect()->back()->with('error', 'Arquivo do bloco nÃ£o encontrado.');
        }

        $fullPath = WRITEPATH . $block['media_path'];
        if (!is_file($fullPath)) {
            return redirect()->back()->with('error', 'Arquivo do bloco nÃ£o encontrado.');
        }

        $downloadName = $block['media_name'] ?? basename($fullPath);
        return $this->response->download($fullPath, null)->setFileName($downloadName);
    }

    protected function handleBlockMediaUpload(): array
    {
        $file = $this->request->getFile('media_file');
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['error' => null, 'data' => []];
        }
        if (!$file->isValid()) {
            return ['error' => 'Falha no upload do arquivo.', 'data' => []];
        }

        if ($file->getSizeByUnit('mb') > 20) {
            return ['error' => 'Arquivo maior que 20MB.', 'data' => []];
        }

        $detectedMime = (string) $file->getMimeType();
        if ($detectedMime === '') {
            $detectedMime = (string) $file->getClientMimeType();
        }

        $allowed = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'video/mp4',
            'video/webm',
            'video/quicktime',
        ];

        if (!in_array($detectedMime, $allowed, true)) {
            return ['error' => 'Formato não permitido. Use PDF, imagem ou vídeo.', 'data' => []];
        }

        $targetDir = WRITEPATH . 'uploads/training_blocks';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $storedName = $file->getRandomName();
        $file->move($targetDir, $storedName);

        return [
            'error' => null,
            'data' => [
                'media_path' => 'uploads/training_blocks/' . $storedName,
                'media_name' => $file->getClientName(),
                'media_mime' => $detectedMime,
            ],
        ];
    }
}
