<?php

namespace App\Controllers\Api;

use App\Services\AthleteService;
use App\Services\AthleteGuardianService;
use App\Services\GuardianService;
use App\Services\AthleteSummaryService;

class AthletesController extends BaseApiController
{
    protected AthleteService $athletes;
    protected AthleteGuardianService $links;
    protected GuardianService $guardians;
    protected AthleteSummaryService $summary;

    public function __construct()
    {
        $this->athletes = new AthleteService();
        $this->links = new AthleteGuardianService();
        $this->guardians = new GuardianService();
        $this->summary = new AthleteSummaryService();
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
        if ($response = $this->ensurePermission('athletes.view')) {
            return $response;
        }

        $filters = [
            'search'      => $this->request->getGet('search'),
            'team_id'     => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'status'      => $this->request->getGet('status'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->athletes->list($filters, $perPage, 'athletes_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('athletes_api'),
                'pageCount'   => $result['pager']->getPageCount('athletes_api'),
                'perPage'     => $result['pager']->getPerPage('athletes_api'),
                'total'       => $result['pager']->getTotal('athletes_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('athletes.view')) {
            return $response;
        }

        $athlete = $this->athletes->findWithRelations($id);
        if (!$athlete) {
            return $this->fail('Atleta não encontrado.', 404);
        }

        return $this->ok($athlete);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('athletes.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->athleteCreate, config('Validation')->athleteCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        if ($this->isFutureDate($payload['birth_date'] ?? null)) {
            return $this->fail('A data de nascimento não pode ser futura.', 422);
        }

        $athleteId = $this->athletes->create($payload);
        return $this->ok(['id' => $athleteId], 'Atleta criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('athletes.update')) {
            return $response;
        }

        $athlete = $this->athletes->find($id);
        if (!$athlete) {
            return $this->fail('Atleta não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->athleteUpdate, config('Validation')->athleteCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        if ($this->isFutureDate($payload['birth_date'] ?? null)) {
            return $this->fail('A data de nascimento não pode ser futura.', 422);
        }

        $this->athletes->update($id, $payload);
        return $this->ok(['id' => $id], 'Atleta atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('athletes.delete')) {
            return $response;
        }

        $athlete = $this->athletes->find($id);
        if (!$athlete) {
            return $this->fail('Atleta não encontrado.', 404);
        }

        $this->athletes->delete($id);
        return $this->ok(['id' => $id], 'Atleta removido.');
    }

    public function guardians(int $athleteId)
    {
        if ($response = $this->ensurePermission('guardians.view')) {
            return $response;
        }

        $athlete = $this->athletes->find($athleteId);
        if (!$athlete) {
            return $this->fail('Atleta não encontrado.', 404);
        }

        $items = $this->links->listByAthlete($athleteId);
        return $this->ok($items);
    }

    public function linkGuardian(int $athleteId)
    {
        if ($response = $this->ensurePermission('guardians.create')) {
            return $response;
        }

        $athlete = $this->athletes->find($athleteId);
        if (!$athlete) {
            return $this->fail('Atleta não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $guardianId = (int) ($payload['guardian_id'] ?? 0);

        if ($guardianId === 0 && !empty($payload['full_name'])) {
            $validation = service('validation');
            $validation->setRules(config('Validation')->guardianCreate, config('Validation')->guardianCreate_errors);
            if (!$validation->run($payload)) {
                return $this->fail('Validação falhou.', 422, $validation->getErrors());
            }
            $guardianId = $this->guardians->create($payload);
        }

        if ($guardianId === 0) {
            return $this->fail('Responsável inválido.', 422);
        }

        $linkId = $this->links->link($athleteId, $guardianId, (int) ($payload['is_primary'] ?? 0), $payload['notes'] ?? null);
        return $this->ok(['id' => $linkId], 'Responsável vinculado.', 201);
    }

    public function lastActivity(int $id)
    {
        if ($response = $this->ensurePermission('athletes.view')) {
            return $response;
        }

        $athlete = $this->athletes->find($id);
        if (!$athlete) {
            return service('response')->setJSON([
                'success' => false,
                'message' => 'Atleta não encontrado.',
                'data' => null,
                'errors' => null,
            ])->setStatusCode(404);
        }

        return service('response')->setJSON([
            'success' => true,
            'message' => 'OK',
            'data' => $this->summary->getLastActivity($id),
            'errors' => null,
        ])->setStatusCode(200);
    }

    protected function isFutureDate(string $date): bool
    {
        if (!$date) {
            return false;
        }

        return strtotime($date) > time();
    }
}