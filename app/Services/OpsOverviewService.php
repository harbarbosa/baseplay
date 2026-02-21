<?php

namespace App\Services;

class OpsOverviewService
{
    public function overview(int $userId): array
    {
        $teamIds = $this->getScopedTeamIds($userId);

        return [
            'cards' => $this->cards($teamIds),
            'upcoming' => $this->upcomingEvents($teamIds, 10),
        ];
    }

    protected function cards(array $teamIds): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $future = date('Y-m-d', strtotime('+7 days'));

        $baseEvents = $db->table('events e')
            ->where('e.deleted_at', null)
            ->where('e.status', 'scheduled');
        if ($teamIds !== []) {
            $baseEvents->whereIn('e.team_id', $teamIds);
        }

        $upcoming = (int) (clone $baseEvents)
            ->where('e.start_datetime >=', $today . ' 00:00:00')
            ->where('e.start_datetime <=', $future . ' 23:59:59')
            ->countAllResults();

        $todayCount = (int) (clone $baseEvents)
            ->where('e.start_datetime >=', $today . ' 00:00:00')
            ->where('e.start_datetime <=', $today . ' 23:59:59')
            ->countAllResults();

        $noCallups = $this->eventsWithoutCallups($teamIds);
        $pendingAttendance = $this->eventsWithPendingAttendance($teamIds, $now);

        return [
            'upcoming' => $upcoming,
            'today' => $todayCount,
            'no_callups' => $noCallups,
            'pending_attendance' => $pendingAttendance,
        ];
    }

    protected function upcomingEvents(array $teamIds, int $limit): array
    {
        $db = db_connect();
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime('+7 days'));

        $builder = $db->table('events e')
            ->select('e.id, e.title, e.type, e.start_datetime, e.team_id, e.category_id')
            ->select('t.name AS team_name, c.name AS category_name')
            ->join('teams t', 't.id = e.team_id', 'left')
            ->join('categories c', 'c.id = e.category_id', 'left')
            ->where('e.deleted_at', null)
            ->where('e.status', 'scheduled')
            ->where('e.start_datetime >=', $today . ' 00:00:00')
            ->where('e.start_datetime <=', $future . ' 23:59:59');
        if ($teamIds !== []) {
            $builder->whereIn('e.team_id', $teamIds);
        }

        $rows = $builder->orderBy('e.start_datetime', 'ASC')->limit($limit)->get()->getResultArray();

        return $rows ?: [];
    }

    protected function eventsWithoutCallups(array $teamIds): int
    {
        $db = db_connect();
        $today = date('Y-m-d');

        $builder = $db->table('events e')
            ->select('e.id')
            ->join('event_participants ep', 'ep.event_id = e.id', 'left')
            ->where('e.deleted_at', null)
            ->where('e.status', 'scheduled')
            ->where('e.start_datetime >=', $today . ' 00:00:00')
            ->groupBy('e.id')
            ->having('COUNT(ep.id) =', 0, false);
        if ($teamIds !== []) {
            $builder->whereIn('e.team_id', $teamIds);
        }

        return count($builder->get()->getResultArray());
    }

    protected function eventsWithPendingAttendance(array $teamIds, string $now): int
    {
        $db = db_connect();

        $builder = $db->table('events e')
            ->select('e.id')
            ->join('attendance a', 'a.event_id = e.id', 'left')
            ->where('e.deleted_at', null)
            ->where('e.start_datetime <', $now)
            ->groupBy('e.id')
            ->having('COUNT(a.id) =', 0, false);
        if ($teamIds !== []) {
            $builder->whereIn('e.team_id', $teamIds);
        }

        return count($builder->get()->getResultArray());
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
