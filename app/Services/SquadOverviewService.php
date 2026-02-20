<?php

namespace App\Services;

class SquadOverviewService
{
    public function overview(int $userId, array $params = []): array
    {
        $perPage = (int) ($params['per_page'] ?? 10);
        $pendingPage = max(1, (int) ($params['pending_page'] ?? 1));
        $lowPage = max(1, (int) ($params['low_page'] ?? 1));

        $teamIds = $this->getScopedTeamIds($userId);

        return [
            'kpis' => $this->kpis($teamIds),
            'pending' => $this->athletesWithPendingDocs($teamIds, $perPage, $pendingPage),
            'low_attendance' => $this->athletesWithLowAttendance($teamIds, $perPage, $lowPage),
            'paging' => [
                'per_page' => $perPage,
                'pending_page' => $pendingPage,
                'low_page' => $lowPage,
            ],
        ];
    }

    protected function kpis(array $teamIds): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime('+7 days'));
        $from = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $to = date('Y-m-d 23:59:59');

        $athletes = $db->table('athletes a')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('a.deleted_at', null)
            ->where('a.status', 'active');
        if ($teamIds !== []) {
            $athletes->whereIn('c.team_id', $teamIds);
        }
        $activeAthletes = (int) $athletes->countAllResults();

        $docs = $db->table('documents d')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('d.deleted_at', null)
            ->groupStart()
                ->where('d.expires_at <', $today)
                ->orGroupStart()
                    ->where('d.expires_at >=', $today)
                    ->where('d.expires_at <=', $future)
                ->groupEnd()
            ->groupEnd();
        if ($teamIds !== []) {
            $docs->groupStart()
                ->whereIn('c.team_id', $teamIds)
                ->orWhereIn('d.team_id', $teamIds)
                ->groupEnd();
        }
        $pendingDocs = (int) $docs->countAllResults();

        $lowAttendance = $this->countLowAttendanceAthletes($teamIds, $from, $to);

        $events = $db->table('events e')
            ->where('e.deleted_at', null)
            ->where('e.status', 'scheduled')
            ->where('e.start_datetime >=', $today . ' 00:00:00')
            ->where('e.start_datetime <=', $future . ' 23:59:59');
        if ($teamIds !== []) {
            $events->whereIn('e.team_id', $teamIds);
        }
        $upcomingEvents = (int) $events->countAllResults();

        return [
            'active_athletes' => $activeAthletes,
            'pending_documents' => $pendingDocs,
            'low_attendance' => $lowAttendance,
            'upcoming_events' => $upcomingEvents,
        ];
    }

    protected function athletesWithPendingDocs(array $teamIds, int $perPage, int $page): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime('+7 days'));
        $offset = ($page - 1) * $perPage;

        $builder = $db->table('documents d')
            ->select('a.id AS athlete_id, a.first_name, a.last_name')
            ->select('c.name AS category_name, t.name AS team_name')
            ->select('MIN(d.expires_at) AS next_expiry')
            ->select('COUNT(*) AS pending_count')
            ->join('athletes a', 'a.id = d.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('d.deleted_at', null)
            ->where('d.athlete_id IS NOT NULL', null, false)
            ->groupStart()
                ->where('d.expires_at <', $today)
                ->orGroupStart()
                    ->where('d.expires_at >=', $today)
                    ->where('d.expires_at <=', $future)
                ->groupEnd()
            ->groupEnd();
        if ($teamIds !== []) {
            $builder->groupStart()
                ->whereIn('c.team_id', $teamIds)
                ->orWhereIn('d.team_id', $teamIds)
                ->groupEnd();
        }
        $builder->groupBy('a.id');
        $builder->orderBy('next_expiry', 'ASC');
        $builder->orderBy('pending_count', 'DESC');

        $totalBuilder = clone $builder;
        $total = count($totalBuilder->get()->getResultArray());
        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        return [
            'items' => $rows,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function athletesWithLowAttendance(array $teamIds, int $perPage, int $page): array
    {
        $db = db_connect();
        $from = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $to = date('Y-m-d 23:59:59');
        $offset = ($page - 1) * $perPage;

        $builder = $db->table('attendance at')
            ->select('a.id AS athlete_id, a.first_name, a.last_name')
            ->select('c.name AS category_name, t.name AS team_name')
            ->select("SUM(at.status IN ('present','late','justified')) AS attended")
            ->select('COUNT(*) AS total')
            ->join('events e', 'e.id = at.event_id', 'left')
            ->join('athletes a', 'a.id = at.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->join('teams t', 't.id = c.team_id', 'left')
            ->where('e.deleted_at', null)
            ->where('a.deleted_at', null)
            ->where('a.status', 'active')
            ->where('e.start_datetime >=', $from)
            ->where('e.start_datetime <=', $to)
            ->groupBy('a.id')
            ->having('total >', 0)
            ->having('attended / total <', 0.6, false)
            ->orderBy('attended / total', 'ASC', false)
            ->orderBy('total', 'DESC');
        if ($teamIds !== []) {
            $builder->whereIn('t.id', $teamIds);
        }

        $totalBuilder = clone $builder;
        $total = count($totalBuilder->get()->getResultArray());
        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        return [
            'items' => $rows,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function countLowAttendanceAthletes(array $teamIds, string $from, string $to): int
    {
        $db = db_connect();
        $builder = $db->table('attendance at')
            ->select('a.id AS athlete_id')
            ->select("SUM(at.status IN ('present','late','justified')) AS attended")
            ->select('COUNT(*) AS total')
            ->join('events e', 'e.id = at.event_id', 'left')
            ->join('athletes a', 'a.id = at.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('e.deleted_at', null)
            ->where('a.deleted_at', null)
            ->where('a.status', 'active')
            ->where('e.start_datetime >=', $from)
            ->where('e.start_datetime <=', $to)
            ->groupBy('a.id')
            ->having('total >', 0)
            ->having('attended / total <', 0.6, false);
        if ($teamIds !== []) {
            $builder->whereIn('c.team_id', $teamIds);
        }

        $rows = $builder->get()->getResultArray();
        return count($rows);
    }

    protected function getScopedTeamIds(int $userId): array
    {
        if (\Config\Services::rbac()->userHasPermission($userId, 'admin.access')) {
            return [];
        }

        $rows = db_connect()->table('user_team_links')
            ->select('team_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_map(static fn(array $row): int => (int) $row['team_id'], $rows);
    }
}
