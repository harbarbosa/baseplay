<?php

namespace App\Controllers\Api;

use App\Services\DocumentTypeService;

class DocumentTypesController extends BaseApiController
{
    protected DocumentTypeService $types;

    public function __construct()
    {
        $this->types = new DocumentTypeService();
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
        if ($response = $this->ensurePermission('documents.view')) {
            return $response;
        }

        $items = $this->types->listAllActive();
        return $this->ok($items);
    }

    public function store()
    {
        if ($response = $this->ensurePermission('document_types.manage')) {
            return $response;
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->documentTypeCreate, config('Validation')->documentTypeCreate_errors);

        if (!$validation->run($this->request->getJSON(true) ?: $this->request->getPost())) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $id = $this->types->create($payload);

        return $this->ok(['id' => $id], 'Tipo criado.', 201);
    }

    public function update(int $id)
    {
        if ($response = $this->ensurePermission('document_types.manage')) {
            return $response;
        }

        $type = $this->types->find($id);
        if (!$type) {
            return $this->fail('Tipo não encontrado.', 404);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $validation = service('validation');
        $validation->setRules(config('Validation')->documentTypeCreate, config('Validation')->documentTypeCreate_errors);

        if (!$validation->run($payload)) {
            return $this->fail('Validação falhou.', 422, $validation->getErrors());
        }

        $this->types->update($id, $payload);
        return $this->ok(['id' => $id], 'Tipo atualizado.');
    }

    public function delete(int $id)
    {
        if ($response = $this->ensurePermission('document_types.manage')) {
            return $response;
        }

        $type = $this->types->find($id);
        if (!$type) {
            return $this->fail('Tipo não encontrado.', 404);
        }

        $this->types->delete($id);
        return $this->ok(['id' => $id], 'Tipo removido.');
    }
}