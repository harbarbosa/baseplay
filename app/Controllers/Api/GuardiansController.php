<?php

namespace App\Controllers\Api;

use App\Services\GuardianService;

class GuardiansController extends BaseApiController
{
    protected GuardianService $guardians;

    public function __construct()
    {
        $this->guardians = new GuardianService();
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
        if ($response = $this->ensurePermission('guardians.view')) {
            return $response;
        }

        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $result = $this->guardians->list($filters, $perPage, 'guardians_api');

        return $this->ok([
            'items' => $result['items'],
            'pager' => [
                'currentPage' => $result['pager']->getCurrentPage('guardians_api'),
                'pageCount'   => $result['pager']->getPageCount('guardians_api'),
                'perPage'     => $result['pager']->getPerPage('guardians_api'),
                'total'       => $result['pager']->getTotal('guardians_api'),
            ],
        ]);
    }

    public function show(int $id)
    {
        if ($response = $this->ensurePermission('guardians.view')) {
            return $response;
        }

        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return $this->fail('ResponsÃ¡vel nÃ£o encontrado.', 404);
        }

        return $this->ok($guardian);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('guardians.create')) {
            return $response;
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->guardianCreate, config('Validation')->guardianCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validaï¿½fÂ¯ï¿½,Â¿ï¿½,Â½ï¿½fÂ¯ï¿½,Â¿ï¿½,Â½o falhou.', 422, $validation->getErrors());
        }

        $guardianId = $this->guardians->create($payload);
        return $this->ok(['id' => $guardianId], 'ResponsÃ¡vel criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('guardians.update')) {
            return $response;
        }

        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return $this->fail('ResponsÃ¡vel nÃ£o encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->guardianUpdate, config('Validation')->guardianCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validaï¿½fÂ¯ï¿½,Â¿ï¿½,Â½ï¿½fÂ¯ï¿½,Â¿ï¿½,Â½o falhou.', 422, $validation->getErrors());
        }

        $this->guardians->update($id, $payload);
        return $this->ok(['id' => $id], 'ResponsÃ¡vel atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('guardians.delete')) {
            return $response;
        }

        $guardian = $this->guardians->find($id);
        if (!$guardian) {
            return $this->fail('ResponsÃ¡vel nÃ£o encontrado.', 404);
        }

        $this->guardians->delete($id);
        return $this->ok(['id' => $id], 'ResponsÃ¡vel removido.');
    }
}
