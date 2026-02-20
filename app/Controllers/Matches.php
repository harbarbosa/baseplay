<?php

namespace App\Controllers;

use App\Services\MatchService;
use App\Services\MatchCallupService;
use App\Services\MatchLineupService;
use App\Services\MatchEventService;
use App\Services\MatchReportService;
use App\Services\MatchAttachmentService;
use App\Services\TeamService;
use App\Services\CategoryService;
use App\Services\AthleteService;
use App\Services\EventService;
use Config\Services;

class Matches extends BaseController
{
    protected MatchService $matches;
    protected MatchCallupService $callups;
    protected MatchLineupService $lineups;
    protected MatchEventService $events;
    protected MatchReportService $reports;
    protected MatchAttachmentService $attachments;
    protected TeamService $teams;
    protected CategoryService $categories;
    protected AthleteService $athletes;
    protected EventService $agenda;

    public function __construct()
    {
        $this->matches = new MatchService();
        $this->callups = new MatchCallupService();
        $this->lineups = new MatchLineupService();
        $this->events = new MatchEventService();
        $this->reports = new MatchReportService();
        $this->attachments = new MatchAttachmentService();
        $this->teams = new TeamService();
        $this->categories = new CategoryService();
        $this->athletes = new AthleteService();
        $this->agenda = new EventService();
    }

    public function index()
    {
        $filters = [
            'team_id' => $this->request->getGet('team_id'),
            'category_id' => $this->request->getGet('category_id'),
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'competition_name' => $this->request->getGet('competition_name'),
        ];

        $result = $this->matches->list($filters, 20, 'matches');
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $categories = $this->categories->listDistinctByTeam(!empty($filters['team_id']) ? (int) $filters['team_id'] : null, true);

        return view('matches/index', [
            'title' => 'Jogos',
            'matches' => $result['items'],
            'pager' => $result['pager'],
            'filters' => $filters,
            'teams' => $teams,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $teamId = (int) $this->request->getGet('team_id');
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true);
        $events = $this->agenda->list(['type' => 'MATCH'], 200, 'match_events')['items'];

        return view('matches/create', [
            'title' => 'Novo jogo',
            'teams' => $teams,
            'categories' => $categories,
            'events' => $events,
            'team_id' => $teamId,
        ]);
    }

    public function store()
    {
        $payload = $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->matchCreate, config('Validation')->matchCreate_errors);

        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if (($payload['status'] ?? 'scheduled') === 'completed') {
            if ($payload['score_for'] === '' || $payload['score_against'] === '') {
                return redirect()->back()->withInput()->with('error', 'Informe o placar para jogos concluídos.');
            }
        }

        $matchId = $this->matches->create($payload, (int) session('user_id'));
        Services::audit()->log(session('user_id'), 'match_created', ['match_id' => $matchId]);

        return redirect()->to('/matches/' . $matchId)->with('success', 'Jogo criado com sucesso.');
    }

    public function show(int $id)
    {
        $match = $this->matches->findWithRelations($id);
        if (!$match) {
            return redirect()->to('/matches')->with('error', 'Jogo não encontrado.');
        }

        $callups = $this->callups->listByMatch($id);
        $lineups = $this->lineups->listByMatch($id);
        $events = $this->events->listByMatch($id);
        $report = $this->reports->findByMatch($id);
        $attachments = $this->attachments->listByMatch($id);
        $athletes = $this->athletes->listByCategory((int) $match['category_id']);

        return view('matches/show', [
            'title' => 'Detalhe do jogo',
            'match' => $match,
            'callups' => $callups,
            'lineups' => $lineups,
            'events' => $events,
            'report' => $report,
            'attachments' => $attachments,
            'athletes' => $athletes,
        ]);
    }

    public function edit(int $id)
    {
        $match = $this->matches->find($id);
        if (!$match) {
            return redirect()->to('/matches')->with('error', 'Jogo não encontrado.');
        }

        $teams = $this->teams->list([], 200, 'teams_filter')['items'];
        $teamId = (int) $this->request->getGet('team_id');
        if ($teamId <= 0) {
            $teamId = (int) $match['team_id'];
        }
        $categories = $this->categories->listDistinctByTeam($teamId > 0 ? $teamId : null, true);
        $events = $this->agenda->list(['type' => 'MATCH'], 200, 'match_events')['items'];

        return view('matches/edit', [
            'title' => 'Editar jogo',
            'match' => $match,
            'teams' => $teams,
            'categories' => $categories,
            'events' => $events,
            'team_id' => $teamId,
        ]);
    }

    public function update(int $id)
    {
        $match = $this->matches->find($id);
        if (!$match) {
            return redirect()->to('/matches')->with('error', 'Jogo não encontrado.');
        }

        $payload = $this->request->getPost();
        $validation = service('validation');
        $validation->setRules(config('Validation')->matchCreate, config('Validation')->matchCreate_errors);

        if (!$validation->run($payload)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if (($payload['status'] ?? 'scheduled') === 'completed') {
            if ($payload['score_for'] === '' || $payload['score_against'] === '') {
                return redirect()->back()->withInput()->with('error', 'Informe o placar para jogos concluídos.');
            }
        }

        $this->matches->update($id, $payload);
        Services::audit()->log(session('user_id'), 'match_updated', ['match_id' => $id]);

        return redirect()->to('/matches/' . $id)->with('success', 'Jogo atualizado.');
    }

    public function deleteConfirm(int $id)
    {
        $match = $this->matches->find($id);
        if (!$match) {
            return redirect()->to('/matches')->with('error', 'Jogo não encontrado.');
        }

        return view('matches/delete', ['title' => 'Excluir jogo', 'match' => $match]);
    }

    public function delete(int $id)
    {
        $match = $this->matches->find($id);
        if (!$match) {
            return redirect()->to('/matches')->with('error', 'Jogo não encontrado.');
        }

        $this->matches->delete($id);
        Services::audit()->log(session('user_id'), 'match_deleted', ['match_id' => $id]);

        return redirect()->to('/matches')->with('success', 'Jogo removido.');
    }

    public function createFromEvent(int $eventId)
    {
        $matchId = $this->matches->createFromEvent($eventId, (int) session('user_id'));
        if (!$matchId) {
            return redirect()->to('/matches')->with('error', 'Evento inválido para criar jogo.');
        }

        $this->callups->addFromEventParticipants($matchId, $eventId);
        return redirect()->to('/matches/' . $matchId)->with('success', 'Jogo criado a partir da agenda.');
    }

    public function addCallupsCategory(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return redirect()->back()->with('error', 'Jogo não encontrado.');
        }

        $count = $this->callups->addFromCategory($matchId, (int) $match['category_id']);
        return redirect()->back()->with('success', "$count atletas convocados.");
    }

    public function importCallupsFromEvent(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match || empty($match['event_id'])) {
            return redirect()->back()->with('error', 'Nenhum evento vinculado ao jogo.');
        }

        $count = $this->callups->addFromEventParticipants($matchId, (int) $match['event_id']);
        return redirect()->back()->with('success', "$count atletas importados do evento.");
    }

    public function addCallup(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return redirect()->back()->with('error', 'Jogo não encontrado.');
        }

        $athleteId = (int) $this->request->getPost('athlete_id');
        if ($athleteId <= 0) {
            return redirect()->back()->with('error', 'Selecione um atleta.');
        }

        $this->callups->addParticipant($matchId, $athleteId, 'invited');
        return redirect()->back()->with('success', 'Atleta convocado.');
    }

    public function updateCallup(int $id)
    {
        $callup = $this->callups->find($id);
        if (!$callup) {
            return redirect()->back()->with('error', 'Convocação não encontrada.');
        }

        $status = $this->request->getPost('callup_status') ?? 'invited';
        $isStarting = (int) ($this->request->getPost('is_starting') ?? 0);

        $this->callups->update($id, $status, $isStarting);
        return redirect()->back()->with('success', 'Convocação atualizada.');
    }

    public function deleteCallup(int $id)
    {
        $callup = $this->callups->find($id);
        if (!$callup) {
            return redirect()->back()->with('error', 'Convocação não encontrada.');
        }

        $this->callups->delete($id);
        return redirect()->back()->with('success', 'Convocação removida.');
    }

    public function saveLineup(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return redirect()->back()->with('error', 'Jogo não encontrado.');
        }

        $athleteId = (int) $this->request->getPost('athlete_id');
        if ($athleteId <= 0 || !$this->callups->isCalledUp($matchId, $athleteId)) {
            return redirect()->back()->with('error', 'Atleta não convocado para este jogo.');
        }

        $this->lineups->upsert($matchId, $athleteId, [
            'lineup_role' => $this->request->getPost('lineup_role') ?? 'starting',
            'position_code' => $this->request->getPost('position_code') ?? null,
            'shirt_number' => $this->request->getPost('shirt_number') ?? null,
            'order_index' => $this->request->getPost('order_index') ?? 0,
        ]);

        return redirect()->back()->with('success', 'Escalação atualizada.');
    }

    public function addEvent(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return redirect()->back()->with('error', 'Jogo não encontrado.');
        }

        $payload = $this->request->getPost();
        if (empty($payload['event_type'])) {
            return redirect()->back()->with('error', 'Informe o tipo do evento.');
        }

        if ($payload['event_type'] === 'goal' && empty($payload['athlete_id'])) {
            return redirect()->back()->with('error', 'Informe o atleta para gol.');
        }

        $this->events->create($matchId, $payload);
        return redirect()->back()->with('success', 'Evento registrado.');
    }

    public function updateEvent(int $id)
    {
        $event = $this->events->find($id);
        if (!$event) {
            return redirect()->back()->with('error', 'Evento não encontrado.');
        }

        $payload = $this->request->getPost();
        $this->events->update($id, $payload);
        return redirect()->back()->with('success', 'Evento atualizado.');
    }

    public function deleteEvent(int $id)
    {
        $event = $this->events->find($id);
        if (!$event) {
            return redirect()->back()->with('error', 'Evento não encontrado.');
        }

        $this->events->delete($id);
        return redirect()->back()->with('success', 'Evento removido.');
    }

    public function saveReport(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return redirect()->back()->with('error', 'Jogo não encontrado.');
        }

        $this->reports->upsert($matchId, $this->request->getPost());
        return redirect()->back()->with('success', 'Relatório atualizado.');
    }

    public function addAttachment(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return redirect()->back()->with('error', 'Jogo não encontrado.');
        }

        $url = trim((string) $this->request->getPost('url'));
        if ($url === '') {
            return redirect()->back()->with('error', 'Informe um link.');
        }

        $this->attachments->create($matchId, [
            'url' => $url,
            'type' => 'link',
            'original_name' => $this->request->getPost('original_name') ?? null,
        ]);

        return redirect()->back()->with('success', 'Anexo adicionado.');
    }

    public function deleteAttachment(int $id)
    {
        $attachment = $this->attachments->find($id);
        if (!$attachment) {
            return redirect()->back()->with('error', 'Anexo não encontrado.');
        }

        $this->attachments->delete($id);
        return redirect()->back()->with('success', 'Anexo removido.');
    }
}
