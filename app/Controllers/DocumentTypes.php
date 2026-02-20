<?php

namespace App\Controllers;

use App\Services\DocumentTypeService;
use Config\Services;

class DocumentTypes extends BaseController
{
    protected DocumentTypeService $types;

    public function __construct()
    {
        $this->types = new DocumentTypeService();
    }

    public function index()
    {
        $filters = [
            'status' => $this->request->getGet('status'),
            'search' => $this->request->getGet('search'),
        ];

        $result = $this->types->list($filters, 15, 'document_types');

        return view('document_types/index', [
            'title' => 'Tipos de documento',
            'types' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('document_types/create', ['title' => 'Novo tipo de documento']);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->documentTypeCreate, config('Validation')->documentTypeCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $typeId = $this->types->create($this->request->getPost());
        Services::audit()->log(session('user_id'), 'document_type_created', ['document_type_id' => $typeId]);

        return redirect()->to('/document-types')->with('success', 'Tipo criado com sucesso.');
    }

    public function edit(int $id)
    {
        $type = $this->types->find($id);
        if (!$type) {
            return redirect()->to('/document-types')->with('error', 'Tipo não encontrado.');
        }

        return view('document_types/edit', ['title' => 'Editar tipo', 'type' => $type]);
    }

    public function update(int $id)
    {
        $type = $this->types->find($id);
        if (!$type) {
            return redirect()->to('/document-types')->with('error', 'Tipo não encontrado.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->documentTypeCreate, config('Validation')->documentTypeCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->types->update($id, $this->request->getPost());
        Services::audit()->log(session('user_id'), 'document_type_updated', ['document_type_id' => $id]);

        return redirect()->to('/document-types')->with('success', 'Tipo atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $type = $this->types->find($id);
        if (!$type) {
            return redirect()->to('/document-types')->with('error', 'Tipo não encontrado.');
        }

        return view('document_types/delete', ['title' => 'Excluir tipo', 'type' => $type]);
    }

    public function delete(int $id)
    {
        $type = $this->types->find($id);
        if (!$type) {
            return redirect()->to('/document-types')->with('error', 'Tipo não encontrado.');
        }

        $this->types->delete($id);
        Services::audit()->log(session('user_id'), 'document_type_deleted', ['document_type_id' => $id]);

        return redirect()->to('/document-types')->with('success', 'Tipo removido.');
    }
}