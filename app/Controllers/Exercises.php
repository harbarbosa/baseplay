<?php

namespace App\Controllers;

use App\Services\ExerciseService;
use App\Models\ExerciseTacticalBoardModel;
use App\Models\TacticalBoardModel;
use CodeIgniter\I18n\Time;
use Config\Services;

class Exercises extends BaseController
{
    protected ExerciseService $exercises;
    protected ExerciseTacticalBoardModel $exerciseBoards;
    protected TacticalBoardModel $boards;

    public function __construct()
    {
        $this->exercises = new ExerciseService();
        $this->exerciseBoards = new ExerciseTacticalBoardModel();
        $this->boards = new TacticalBoardModel();
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

        $linkedBoards = $this->exerciseBoards
            ->select('exercise_tactical_boards.tactical_board_id, tactical_boards.title AS board_title, teams.name AS team_name, categories.name AS category_name, tactical_boards.team_id')
            ->join('tactical_boards', 'tactical_boards.id = exercise_tactical_boards.tactical_board_id', 'inner')
            ->join('teams', 'teams.id = tactical_boards.team_id', 'left')
            ->join('categories', 'categories.id = tactical_boards.category_id', 'left')
            ->where('exercise_tactical_boards.exercise_id', $id)
            ->orderBy('tactical_boards.updated_at', 'DESC')
            ->findAll();

        $boardOptions = $this->listTacticalBoardsForUser();

        return view('exercises/show', [
            'title' => 'Exercício',
            'exercise' => $exercise,
            'linkedBoards' => $linkedBoards,
            'boardOptions' => $boardOptions,
        ]);
    }

    public function addTacticalBoard(int $exerciseId)
    {
        $exercise = $this->exercises->find($exerciseId);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        $boardIds = $this->request->getPost('tactical_board_ids') ?? $this->request->getPost('tactical_board_id') ?? [];
        $boardIds = $this->resolveBoardIdsForUser(is_array($boardIds) ? $boardIds : [$boardIds]);
        if ($boardIds === []) {
            return redirect()->back()->with('error', 'Selecione uma prancheta.');
        }

        foreach ($boardIds as $boardId) {
            $exists = $this->exerciseBoards
                ->where('exercise_id', $exerciseId)
                ->where('tactical_board_id', $boardId)
                ->first();

            if (!$exists) {
                $this->exerciseBoards->insert([
                    'exercise_id' => $exerciseId,
                    'tactical_board_id' => $boardId,
                    'created_at' => Time::now()->toDateTimeString(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pranchetas vinculadas.');
    }

    public function removeTacticalBoard(int $exerciseId, int $boardId)
    {
        $exercise = $this->exercises->find($exerciseId);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        $board = $this->boards->find($boardId);
        if ($board && ($response = $this->denyIfTeamForbidden((int) $board['team_id'], '/exercises/' . $exerciseId))) {
            return $response;
        }

        $this->exerciseBoards
            ->where('exercise_id', $exerciseId)
            ->where('tactical_board_id', $boardId)
            ->delete();

        return redirect()->back()->with('success', 'Vínculo removido.');
    }

    public function create()
    {
        $boardOptions = $this->listTacticalBoardsForUser();

        return view('exercises/create', [
            'title' => 'Novo exercício',
            'boardOptions' => $boardOptions,
            'linkedBoardIds' => [],
        ]);
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
        $this->syncExerciseBoards($id, $this->request->getPost('tactical_board_ids') ?? []);

        return redirect()->to('/exercises/' . $id)->with('success', 'Exercício criado.');
    }

    public function edit(int $id)
    {
        $exercise = $this->exercises->findWithTags($id);
        if (!$exercise) {
            return redirect()->to('/exercises')->with('error', 'Exercício não encontrado.');
        }

        $linkedBoardIds = array_map('intval', array_column(
            $this->exerciseBoards->where('exercise_id', $id)->findAll(),
            'tactical_board_id'
        ));
        $boardOptions = $this->listTacticalBoardsForUser();

        return view('exercises/edit', [
            'title' => 'Editar exercício',
            'exercise' => $exercise,
            'boardOptions' => $boardOptions,
            'linkedBoardIds' => $linkedBoardIds,
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
        $this->syncExerciseBoards($id, $this->request->getPost('tactical_board_ids') ?? []);

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

    protected function listTacticalBoardsForUser(): array
    {
        $builder = $this->boards
            ->select('tactical_boards.id, tactical_boards.title, tactical_boards.team_id, teams.name AS team_name, categories.name AS category_name')
            ->join('teams', 'teams.id = tactical_boards.team_id', 'left')
            ->join('categories', 'categories.id = tactical_boards.category_id', 'left')
            ->where('tactical_boards.deleted_at', null);

        if ($this->scopedTeamIds !== []) {
            $builder->whereIn('tactical_boards.team_id', $this->scopedTeamIds);
        }

        return $builder->orderBy('tactical_boards.updated_at', 'DESC')->findAll(200);
    }

    protected function resolveBoardIdsForUser(array $boardIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $boardIds), static fn ($id) => $id > 0)));
        if ($ids === []) {
            return [];
        }

        $builder = $this->boards->select('tactical_boards.id, tactical_boards.team_id')
            ->whereIn('tactical_boards.id', $ids)
            ->where('tactical_boards.deleted_at', null);

        if ($this->scopedTeamIds !== []) {
            $builder->whereIn('tactical_boards.team_id', $this->scopedTeamIds);
        }

        $rows = $builder->findAll();
        return array_map('intval', array_column($rows, 'id'));
    }

    protected function syncExerciseBoards(int $exerciseId, $boardIds): void
    {
        $ids = is_array($boardIds) ? $boardIds : [$boardIds];
        $ids = $this->resolveBoardIdsForUser($ids);

        $this->exerciseBoards->where('exercise_id', $exerciseId)->delete();

        foreach ($ids as $boardId) {
            $this->exerciseBoards->insert([
                'exercise_id' => $exerciseId,
                'tactical_board_id' => $boardId,
                'created_at' => Time::now()->toDateTimeString(),
            ]);
        }
    }
}
