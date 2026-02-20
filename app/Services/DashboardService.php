<?php

namespace App\Services;

class DashboardService
{
    public function admin(): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $totalAthletes = (int) $db->table('athletes')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->countAllResults();

        $attendanceRow = $db->table('attendance a')
            ->select("SUM(a.status IN ('present','late','justified')) AS present_count, COUNT(*) AS total")
            ->join('events e', 'e.id = a.event_id', 'left')
            ->where('e.start_datetime >=', $monthStart . ' 00:00:00')
            ->where('e.start_datetime <=', $monthEnd . ' 23:59:59')
            ->get()
            ->getRowArray();

        $attendancePct = 0.0;
        if (!empty($attendanceRow['total'])) {
            $attendancePct = round(((int) $attendanceRow['present_count'] / (int) $attendanceRow['total']) * 100, 2);
        }

        $upcomingEvents = $db->table('events')
            ->where('deleted_at', null)
            ->where('status', 'scheduled')
            ->where('start_datetime >=', $today . ' 00:00:00')
            ->orderBy('start_datetime', 'ASC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $matchesCount = (int) $db->table('matches')
            ->where('deleted_at', null)
            ->where('match_date >=', $monthStart)
            ->where('match_date <=', $monthEnd)
            ->countAllResults();

        $docsExpired = (int) $db->table('documents')
            ->where('deleted_at', null)
            ->where('expires_at <', $today)
            ->countAllResults();

        $lowAttendanceCount = $this->countLowAttendanceAthletes();

        $alerts = (new DocumentAlertService())->getAlerts([7, 15, 30]);
        $systemAlertUnread = (new AlertService())->unreadCount();

        return [
            'kpis' => [
                'totalAthletes' => $totalAthletes,
                'attendancePct' => $attendancePct,
                'upcomingEventsCount' => count($upcomingEvents),
                'matchesCount' => $matchesCount,
                'docsExpired' => $docsExpired,
                'lowAttendanceCount' => $lowAttendanceCount,
                'upcomingEvents' => $upcomingEvents,
                'documentAlerts' => $alerts,
                'systemAlertUnread' => $systemAlertUnread,
            ],
            'charts' => $this->getAdminCharts($monthStart, $monthEnd),
        ];
    }

    public function trainer(int $userId): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $teamIds = $this->getUserTeamIds($userId);

        $attendanceBuilder = $db->table('attendance a')
            ->select("SUM(a.status IN ('present','late','justified')) AS present_count, COUNT(*) AS total")
            ->join('events e', 'e.id = a.event_id', 'left')
            ->where('e.deleted_at', null);
        if ($teamIds !== []) {
            $attendanceBuilder->whereIn('e.team_id', $teamIds);
        }
        $attendanceRow = $attendanceBuilder->get()->getRowArray();

        $attendancePct = 0.0;
        if (!empty($attendanceRow['total'])) {
            $attendancePct = round(((int) $attendanceRow['present_count'] / (int) $attendanceRow['total']) * 100, 2);
        }

        $nextTraining = $db->table('events')
            ->where('deleted_at', null)
            ->where('status', 'scheduled')
            ->where('type', 'TRAINING')
            ->where('start_datetime >=', $today . ' 00:00:00')
            ->orderBy('start_datetime', 'ASC');
        if ($teamIds !== []) {
            $nextTraining->whereIn('team_id', $teamIds);
        }
        $nextTraining = $nextTraining->get()->getRowArray();

        $nextMatch = $db->table('events')
            ->where('deleted_at', null)
            ->where('status', 'scheduled')
            ->where('type', 'MATCH')
            ->where('start_datetime >=', $today . ' 00:00:00')
            ->orderBy('start_datetime', 'ASC');
        if ($teamIds !== []) {
            $nextMatch->whereIn('team_id', $teamIds);
        }
        $nextMatch = $nextMatch->get()->getRowArray();

        $docsExpired = $db->table('documents d')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('d.deleted_at', null)
            ->where('d.expires_at <', $today);
        if ($teamIds !== []) {
            $docsExpired->whereIn('c.team_id', $teamIds);
        }

        return [
            'kpis' => [
                'attendancePct' => $attendancePct,
                'nextTraining' => $nextTraining,
                'nextMatch' => $nextMatch,
                'documentsPending' => (int) $docsExpired->countAllResults(),
                'lowAttendanceCount' => $this->countLowAttendanceAthletes($teamIds),
                'systemAlertUnread' => (new AlertService())->unreadCount(),
            ],
        ];
    }

    public function assistant(int $userId): array
    {
        $db = db_connect();
        $teamIds = $this->getUserTeamIds($userId);
        $from = date('Y-m-d 00:00:00');
        $to = date('Y-m-d 23:59:59', strtotime('+2 days'));

        $events = $db->table('events')
            ->where('deleted_at', null)
            ->where('status', 'scheduled')
            ->where('start_datetime >=', $from)
            ->where('start_datetime <=', $to)
            ->orderBy('start_datetime', 'ASC');
        if ($teamIds !== []) {
            $events->whereIn('team_id', $teamIds);
        }

        return [
            'kpis' => [
                'eventsWindow' => $events->get()->getResultArray(),
                'systemAlertUnread' => (new AlertService())->unreadCount(),
            ],
        ];
    }

    public function athlete(int $userId): array
    {
        $db = db_connect();
        $today = date('Y-m-d');

        $events = $db->table('events')
            ->where('deleted_at', null)
            ->where('status', 'scheduled')
            ->where('start_datetime >=', $today . ' 00:00:00')
            ->orderBy('start_datetime', 'ASC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $notices = $db->table('notices')
            ->where('deleted_at', null)
            ->where('status', 'published')
            ->orderBy('publish_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return [
            'kpis' => [
                'upcomingEvents' => $events,
                'notices' => $notices,
                'systemAlertUnread' => (new AlertService())->unreadCount(),
            ],
        ];
    }

    protected function getAdminCharts(string $monthStart, string $monthEnd): array
    {
        $db = db_connect();

        $weeklyAttendance = $db->query(
            "SELECT YEARWEEK(e.start_datetime, 1) AS week,
                    SUM(a.status IN ('present','late','justified')) AS present_count,
                    COUNT(*) AS total
             FROM attendance a
             LEFT JOIN events e ON e.id = a.event_id
             WHERE e.start_datetime >= ? AND e.start_datetime <= ?
             GROUP BY YEARWEEK(e.start_datetime, 1)
             ORDER BY week",
            [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59']
        )->getResultArray();

        $trainingsByCategory = $db->table('training_sessions ts')
            ->select('c.name AS category_name, COUNT(*) AS total')
            ->join('categories c', 'c.id = ts.category_id', 'left')
            ->where('ts.deleted_at', null)
            ->groupBy('c.name')
            ->get()
            ->getResultArray();

        $results = $db->table('matches')
            ->select('score_for, score_against')
            ->where('status', 'completed')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        $wins = 0;
        $draws = 0;
        $losses = 0;
        foreach ($results as $row) {
            if ((int) $row['score_for'] > (int) $row['score_against']) {
                $wins++;
            } elseif ((int) $row['score_for'] < (int) $row['score_against']) {
                $losses++;
            } else {
                $draws++;
            }
        }

        return [
            'weeklyAttendance' => $weeklyAttendance,
            'trainingsByCategory' => $trainingsByCategory,
            'matchResults' => ['wins' => $wins, 'draws' => $draws, 'losses' => $losses],
        ];
    }

    protected function countLowAttendanceAthletes(array $teamIds = []): int
    {
        $db = db_connect();
        $from = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $to = date('Y-m-d 23:59:59');

        $builder = $db->table('attendance a')
            ->select('a.athlete_id, SUM(a.status IN (\'present\',\'late\',\'justified\')) AS attended, COUNT(*) AS total')
            ->join('events e', 'e.id = a.event_id', 'left')
            ->where('e.deleted_at', null)
            ->where('e.start_datetime >=', $from)
            ->where('e.start_datetime <=', $to)
            ->groupBy('a.athlete_id');

        if ($teamIds !== []) {
            $builder->whereIn('e.team_id', $teamIds);
        }

        $rows = $builder->get()->getResultArray();
        $count = 0;
        foreach ($rows as $row) {
            $total = (int) ($row['total'] ?? 0);
            if ($total <= 0) {
                continue;
            }
            $rate = ((int) ($row['attended'] ?? 0) / $total) * 100;
            if ($rate < 60) {
                $count++;
            }
        }

        return $count;
    }

    protected function getUserTeamIds(int $userId): array
    {
        $rows = db_connect()->table('user_team_links')
            ->select('team_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_map(static fn($row) => (int) $row['team_id'], $rows);
    }
}
