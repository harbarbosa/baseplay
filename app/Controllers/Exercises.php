<?php

namespace App\Controllers;

use App\Services\ExerciseService;
use Config\Services;

class Exercises extends BaseController
{
    protected ExerciseService $exercises;

    public function __construct()
    {
        $this->exercises = new ExerciseService();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'objective' => $this->request->getGet('objective'),
            'age_group' => $this->request->getGet('age_group'),
            'intensity' => $this->request->getGet('intensity'),
            'tag' => $this->request->getGet('tag'),
            'status' => $this->request->getGet('status'),
        ];

        $result = $this->exercises->list($filters, 15, 'exercises');
        $tags = $this->exercises->listTags();

        return view('exercises/index', [
            'title' => 'Exercícios',
            'exercises' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'tags' => $tags,
        ]);
    }

    public function show(int $id)
    {
        $exercise = $this->exercises->findWithTags($id);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        return view('exercises/show', [
            'title' => 'Exercício',
            'exercise' => $exercise,
        ]);
    }

    public function create()
    {
        return view('exercises/create', ['title' => 'Novo exercício']);
    }

    public function store()
    {
        $validation = service('validation');
        $validation->setRules(config('Validation')->exerciseCreate, config('Validation')->exerciseCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $id = $this->exercises->create($this->request->getPost(), (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'exercise_created', ['exercise_id' => $id]);

        return redirect()->to('/exercises/' . $id)->with('success', 'Exercício criado.');
    }

    public function edit(int $id)
    {
        $exercise = $this->exercises->findWithTags($id);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        return view('exercises/edit', [
            'title' => 'Editar exercício',
            'exercise' => $exercise,
        ]);
    }

    public function update(int $id)
    {
        $exercise = $this->exercises->find($id);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        $validation = service('validation');
        $validation->setRules(config('Validation')->exerciseCreate, config('Validation')->exerciseCreate_errors);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->exercises->update($id, $this->request->getPost());
        Services::audit()->log(session('user_id'), 'exercise_updated', ['exercise_id' => $id]);

        return redirect()->to('/exercises/' . $id)->with('success', 'Exercício atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $exercise = $this->exercises->find($id);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        return view('exercises/delete', ['title' => 'Excluir exercício', 'exercise' => $exercise]);
    }

    public function delete(int $id)
    {
        $exercise = $this->exercises->find($id);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        $this->exercises->delete($id);
        Services::audit()->log(session('user_id'), 'exercise_deleted', ['exercise_id' => $id]);

        return redirect()->to('/exercises')->with('success', 'Exercício removido.');
    }
}