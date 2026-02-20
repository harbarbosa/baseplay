<?php

namespace App\Services;

class ReportService
{
    public function attendance(array $filters): array
    {
        $db = db_connect();
        $builder = $db->table('attendance')
            ->select("athletes.id, athletes.first_name, athletes.last_name,
                SUM(attendance.status = 'present') AS present,
                SUM(attendance.status = 'late') AS late,
                SUM(attendance.status = 'absent') AS absent,
                SUM(attendance.status = 'justified') AS justified,
                COUNT(*) AS total")
            ->join('events', 'events.id = attendance.event_id', 'left')
            ->join('athletes', 'athletes.id = attendance.athlete_id', 'left')
            ->where('events.deleted_at', null);

        $this->applyEventFilters($builder, $filters);
        if (!empty($filters['athlete_id'])) {
            $builder->where('attendance.athlete_id', (int) $filters['athlete_id']);
        }

        $rows = $builder->groupBy('athletes.id')->get()->getResultArray();

        foreach ($rows as &$row) {
            $total = (int) $row['total'];
            $present = (int) $row['present'] + (int) $row['late'] + (int) $row['justified'];
            $row['presence_pct'] = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        }

        $headers = ['Atleta', 'Presenças', 'Atrasos', 'Faltas', 'Justificadas', 'Total', 'Presença %'];
        $dataRows = [];
        foreach ($rows as $row) {
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $dataRows[] = [
                $name,
                (int) $row['present'],
                (int) $row['late'],
                (int) $row['absent'],
                (int) $row['justified'],
                (int) $row['total'],
                $row['presence_pct'] . '%',
            ];
        }

        return ['headers' => $headers, 'rows' => $dataRows, 'raw' => $rows];
    }

    public function trainings(array $filters): array
    {
        $db = db_connect();
        $builder = $db->table('training_sessions')
            ->select('categories.name AS category_name, COUNT(*) AS total_sessions')
            ->join('categories', 'categories.id = training_sessions.category_id', 'left')
            ->where('training_sessions.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $builder->where('training_sessions.team_id', (int) $filters['team_id']);
        }
        if (!empty($filters['category_id'])) {
            $builder->where('training_sessions.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('training_sessions.session_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('training_sessions.session_date <=', $filters['date_to']);
        }

        $rows = $builder->groupBy('categories.name')->get()->getResultArray();

        $headers = ['Categoria', 'Treinos realizados'];
        $dataRows = [];
        foreach ($rows as $row) {
            $dataRows[] = [$row['category_name'] ?? '-', (int) $row['total_sessions']];
        }

        return ['headers' => $headers, 'rows' => $dataRows, 'raw' => $rows];
    }

    public function matches(array $filters): array
    {
        $db = db_connect();
        $builder = $db->table('matches')
            ->select('matches.*, teams.name AS team_name, categories.name AS category_name')
            ->join('teams', 'teams.id = matches.team_id', 'left')
            ->join('categories', 'categories.id = matches.category_id', 'left')
            ->where('matches.deleted_at', null);

        $this->applyMatchFilters($builder, $filters);
        $rows = $builder->orderBy('matches.match_date', 'DESC')->get()->getResultArray();

        $wins = 0;
        $draws = 0;
        $losses = 0;
        foreach ($rows as $row) {
            if (($row['status'] ?? '') !== 'completed') {
                continue;
            }
            if ((int) $row['score_for'] > (int) $row['score_against']) {
                $wins++;
            } elseif ((int) $row['score_for'] < (int) $row['score_against']) {
                $losses++;
            } else {
                $draws++;
            }
        }

        $headers = ['Data', 'Equipe', 'Categoria', 'Adversário', 'Placar', 'Status'];
        $dataRows = [];
        foreach ($rows as $row) {
            $placar = ($row['status'] ?? '') === 'completed'
                ? ((string) ($row['score_for'] ?? '-') . ' x ' . (string) ($row['score_against'] ?? '-'))
                : '-';
            $dataRows[] = [
                $row['match_date'] ?? '-',
                $row['team_name'] ?? '-',
                $row['category_name'] ?? '-',
                $row['opponent_name'] ?? '-',
                $placar,
                $row['status'] ?? '-',
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $dataRows,
            'raw' => $rows,
            'summary' => ['wins' => $wins, 'draws' => $draws, 'losses' => $losses],
        ];
    }

    public function documents(array $filters): array
    {
        $db = db_connect();
        $builder = $db->table('documents')
            ->select('documents.*, document_types.name AS type_name, athletes.first_name, athletes.last_name, teams.name AS team_name')
            ->join('document_types', 'document_types.id = documents.document_type_id', 'left')
            ->join('athletes', 'athletes.id = documents.athlete_id', 'left')
            ->join('teams', 'teams.id = documents.team_id', 'left')
            ->where('documents.deleted_at', null);

        if (!empty($filters['team_id'])) {
            $builder->where('documents.team_id', (int) $filters['team_id']);
        }
        if (!empty($filters['athlete_id'])) {
            $builder->where('documents.athlete_id', (int) $filters['athlete_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('documents.status', $filters['status']);
        }
        if (!empty($filters['expiring_in_days'])) {
            $days = (int) $filters['expiring_in_days'];
            $today = date('Y-m-d');
            $limit = date('Y-m-d', strtotime("+{$days} days"));
            $builder->where('documents.expires_at >=', $today)
                ->where('documents.expires_at <=', $limit);
        }

        $rows = $builder->orderBy('documents.expires_at', 'ASC')->get()->getResultArray();

        $headers = ['Tipo', 'Atleta', 'Equipe', 'Vencimento', 'Status'];
        $dataRows = [];
        foreach ($rows as $row) {
            $athlete = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $dataRows[] = [
                $row['type_name'] ?? '-',
                $athlete !== '' ? $athlete : '-',
                $row['team_name'] ?? '-',
                $row['expires_at'] ?? '-',
                $row['status'] ?? '-',
            ];
        }

        return ['headers' => $headers, 'rows' => $dataRows, 'raw' => $rows];
    }

    public function athlete(int $athleteId, array $filters): array
    {
        $db = db_connect();

        $attendance = $db->table('attendance')
            ->select("SUM(attendance.status = 'present') AS present,
                SUM(attendance.status = 'late') AS late,
                SUM(attendance.status = 'absent') AS absent,
                SUM(attendance.status = 'justified') AS justified,
                COUNT(*) AS total")
            ->join('events', 'events.id = attendance.event_id', 'left')
            ->where('attendance.athlete_id', $athleteId)
            ->where('events.deleted_at', null);
        $this->applyEventFilters($attendance, $filters);
        $attendanceRow = $attendance->get()->getRowArray();

        $sessions = $db->table('training_session_athletes')
            ->select('COUNT(*) AS total')
            ->join('training_sessions', 'training_sessions.id = training_session_athletes.training_session_id', 'left')
            ->where('training_session_athletes.athlete_id', $athleteId)
            ->where('training_sessions.deleted_at', null);
        if (!empty($filters['date_from'])) {
            $sessions->where('training_sessions.session_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $sessions->where('training_sessions.session_date <=', $filters['date_to']);
        }
        $sessionsRow = $sessions->get()->getRowArray();

        $matches = $db->table('match_callups')
            ->select('COUNT(*) AS total')
            ->join('matches', 'matches.id = match_callups.match_id', 'left')
            ->where('match_callups.athlete_id', $athleteId)
            ->where('matches.deleted_at', null);
        if (!empty($filters['date_from'])) {
            $matches->where('matches.match_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $matches->where('matches.match_date <=', $filters['date_to']);
        }
        $matchesRow = $matches->get()->getRowArray();

        $headers = ['Indicador', 'Valor'];
        $dataRows = [
            ['Presenças', (int) ($attendanceRow['present'] ?? 0)],
            ['Atrasos', (int) ($attendanceRow['late'] ?? 0)],
            ['Faltas', (int) ($attendanceRow['absent'] ?? 0)],
            ['Justificadas', (int) ($attendanceRow['justified'] ?? 0)],
            ['Total de presença', (int) ($attendanceRow['total'] ?? 0)],
            ['Treinos registrados', (int) ($sessionsRow['total'] ?? 0)],
            ['Jogos convocados', (int) ($matchesRow['total'] ?? 0)],
        ];

        return ['headers' => $headers, 'rows' => $dataRows, 'raw' => $attendanceRow];
    }

    protected function applyEventFilters($builder, array $filters): void
    {
        if (!empty($filters['team_id'])) {
            $builder->where('events.team_id', (int) $filters['team_id']);
        }
        if (!empty($filters['category_id'])) {
            $builder->where('events.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('events.start_datetime >=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $builder->where('events.start_datetime <=', $filters['date_to'] . ' 23:59:59');
        }
    }

    protected function applyMatchFilters($builder, array $filters): void
    {
        if (!empty($filters['team_id'])) {
            $builder->where('matches.team_id', (int) $filters['team_id']);
        }
        if (!empty($filters['category_id'])) {
            $builder->where('matches.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('matches.match_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('matches.match_date <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $builder->where('matches.status', $filters['status']);
        }
        if (!empty($filters['competition_name'])) {
            $builder->like('matches.competition_name', $filters['competition_name']);
        }
    }
}
