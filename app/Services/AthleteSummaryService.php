<?php

namespace App\Services;

use App\Models\AthleteModel;
use DateTimeImmutable;

class AthleteSummaryService
{
    protected AthleteModel $athletes;

    public function __construct()
    {
        $this->athletes = new AthleteModel();
    }

    public function getLastActivity(int $athleteId): array
    {
        $athlete = $this->athletes->find($athleteId);
        if (!$athlete) {
            return [
                'last_training' => null,
                'last_match' => null,
            ];
        }

        $lastTraining = $this->findLastTrainingFromSessions($athleteId) ?? $this->findLastTrainingFromEventAttendance($athleteId);

        $lastMatch = $this->findLastMatchFromMatches($athleteId) ?? $this->findLastMatchFromEvents($athleteId);

        return [
            'last_training' => $lastTraining,
            'last_match' => $lastMatch,
        ];
    }

    protected function findLastTrainingFromSessions(int $athleteId): ?array
    {
        $db = db_connect();
        if (!$db->tableExists('training_sessions') || !$db->tableExists('training_session_athletes')) {
            return null;
        }

        $row = $db->table('training_session_athletes tsa')
            ->select('ts.session_date AS activity_date, ts.title')
            ->join('training_sessions ts', 'ts.id = tsa.training_session_id', 'inner')
            ->where('tsa.athlete_id', $athleteId)
            ->whereIn('tsa.attendance_status', ['present', 'late', 'justified'])
            ->where('ts.deleted_at', null)
            ->orderBy('ts.session_date', 'DESC')
            ->orderBy('ts.id', 'DESC')
            ->get(1)
            ->getRowArray();

        if (!$row) {
            return null;
        }

        return $this->formatActivity(
            $row['activity_date'] ?? null,
            $row['title'] ?? 'Treino',
            'training_sessions'
        );
    }

    protected function findLastTrainingFromEventAttendance(int $athleteId): ?array
    {
        $db = db_connect();
        if (!$db->tableExists('events') || !$db->tableExists('attendance')) {
            return null;
        }

        $row = $db->table('attendance a')
            ->select('DATE(e.start_datetime) AS activity_date, e.title')
            ->join('events e', 'e.id = a.event_id', 'inner')
            ->where('a.athlete_id', $athleteId)
            ->whereIn('a.status', ['present', 'late', 'justified'])
            ->where('e.type', 'TRAINING')
            ->where('e.deleted_at', null)
            ->orderBy('e.start_datetime', 'DESC')
            ->orderBy('e.id', 'DESC')
            ->get(1)
            ->getRowArray();

        if (!$row) {
            return null;
        }

        return $this->formatActivity(
            $row['activity_date'] ?? null,
            $row['title'] ?? 'Treino',
            'events_attendance'
        );
    }

    protected function findLastMatchFromMatches(int $athleteId): ?array
    {
        $db = db_connect();
        if (
            !$db->tableExists('matches')
            || !$db->tableExists('match_callups')
            || !$db->tableExists('match_events')
            || !$db->tableExists('match_lineup_positions')
        ) {
            return null;
        }

        $row = $db->table('matches m')
            ->select('m.match_date AS activity_date, m.opponent_name, m.competition_name')
            ->where('m.deleted_at', null)
            ->where(
                '(EXISTS (SELECT 1 FROM match_lineup_positions mlp WHERE mlp.match_id = m.id AND mlp.athlete_id = ' . (int) $athleteId . ')
                  OR EXISTS (SELECT 1 FROM match_events me WHERE me.match_id = m.id AND (me.athlete_id = ' . (int) $athleteId . ' OR me.related_athlete_id = ' . (int) $athleteId . '))
                  OR EXISTS (SELECT 1 FROM match_callups mc WHERE mc.match_id = m.id AND mc.athlete_id = ' . (int) $athleteId . ' AND mc.callup_status = "confirmed"))',
                null,
                false
            )
            ->orderBy('m.match_date', 'DESC')
            ->orderBy('m.id', 'DESC')
            ->get(1)
            ->getRowArray();

        if (!$row) {
            return null;
        }

        $title = 'vs ' . ($row['opponent_name'] ?? '-');
        if (!empty($row['competition_name'])) {
            $title .= ' (' . $row['competition_name'] . ')';
        }

        return $this->formatActivity(
            $row['activity_date'] ?? null,
            $title,
            'matches'
        );
    }

    protected function findLastMatchFromEvents(int $athleteId): ?array
    {
        $db = db_connect();
        if (!$db->tableExists('events') || !$db->tableExists('attendance') || !$db->tableExists('event_participants')) {
            return null;
        }

        $row = $db->table('events e')
            ->select('DATE(e.start_datetime) AS activity_date, e.title')
            ->where('e.type', 'MATCH')
            ->where('e.deleted_at', null)
            ->where(
                '(EXISTS (SELECT 1 FROM attendance a WHERE a.event_id = e.id AND a.athlete_id = ' . (int) $athleteId . ' AND a.status IN ("present","late","justified"))
                  OR EXISTS (SELECT 1 FROM event_participants ep WHERE ep.event_id = e.id AND ep.athlete_id = ' . (int) $athleteId . ' AND ep.invitation_status = "confirmed"))',
                null,
                false
            )
            ->orderBy('e.start_datetime', 'DESC')
            ->orderBy('e.id', 'DESC')
            ->get(1)
            ->getRowArray();

        if (!$row) {
            return null;
        }

        return $this->formatActivity(
            $row['activity_date'] ?? null,
            $row['title'] ?? 'Jogo',
            'events_attendance'
        );
    }

    protected function formatActivity(?string $date, string $title, string $source): ?array
    {
        if (empty($date)) {
            return null;
        }

        $activityDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$activityDate) {
            return null;
        }

        $today = new DateTimeImmutable(date('Y-m-d'));
        $daysAgo = (int) $today->diff($activityDate)->format('%a');

        return [
            'date' => $activityDate->format('Y-m-d'),
            'title' => $title,
            'source' => $source,
            'days_ago' => $daysAgo,
        ];
    }
}
