<?php

namespace App\Services;

use App\Models\EventRecipientModel;
use CodeIgniter\I18n\Time;

class EventRecipientService
{
    protected EventRecipientModel $recipients;

    public function __construct()
    {
        $this->recipients = new EventRecipientModel();
    }

    public function addRecipientsBulk(int $eventId, string $type, array $recipientIds): int
    {
        $count = 0;
        foreach ($recipientIds as $recipientId) {
            $recipientId = (int) $recipientId;
            if ($recipientId <= 0) {
                continue;
            }

            $exists = $this->recipients
                ->where('event_id', $eventId)
                ->where('recipient_type', $type)
                ->where('recipient_id', $recipientId)
                ->first();

            if ($exists) {
                continue;
            }

            $this->recipients->insert([
                'event_id' => $eventId,
                'recipient_type' => $type,
                'recipient_id' => $recipientId,
                'created_at' => Time::now()->toDateTimeString(),
            ]);
            $count++;
        }

        return $count;
    }

    public function listByEvent(int $eventId): array
    {
        return $this->recipients->where('event_id', $eventId)->findAll();
    }

    public function clearByEvent(int $eventId): void
    {
        $this->recipients->where('event_id', $eventId)->delete();
    }

    public function addGuardiansByCategory(int $eventId, int $categoryId): int
    {
        if ($categoryId <= 0) {
            return 0;
        }

        $rows = db_connect()->table('athlete_guardians ag')
            ->select('ag.guardian_id')
            ->join('athletes a', 'a.id = ag.athlete_id', 'left')
            ->where('a.category_id', $categoryId)
            ->where('a.deleted_at', null)
            ->groupBy('ag.guardian_id')
            ->get()
            ->getResultArray();

        $ids = array_map('intval', array_column($rows, 'guardian_id'));
        return $this->addRecipientsBulk($eventId, 'guardian', $ids);
    }

    public function addGuardiansByTeam(int $eventId, int $teamId, array $categoryIds = []): int
    {
        if ($teamId <= 0) {
            return 0;
        }

        $builder = db_connect()->table('athlete_guardians ag')
            ->select('ag.guardian_id')
            ->join('athletes a', 'a.id = ag.athlete_id', 'left')
            ->join('categories c', 'c.id = a.category_id', 'left')
            ->where('a.deleted_at', null)
            ->where('c.team_id', $teamId)
            ->groupBy('ag.guardian_id');

        if ($categoryIds !== []) {
            $ids = array_values(array_filter(array_map('intval', $categoryIds)));
            if ($ids !== []) {
                $builder->whereIn('c.id', $ids);
            }
        }

        $rows = $builder->get()->getResultArray();
        $ids = array_map('intval', array_column($rows, 'guardian_id'));
        return $this->addRecipientsBulk($eventId, 'guardian', $ids);
    }

    public function addStaffByTeam(int $eventId, int $teamId, array $roleNames): int
    {
        if ($teamId <= 0 || $roleNames === []) {
            return 0;
        }

        $roles = db_connect()->table('roles')
            ->select('id')
            ->whereIn('name', $roleNames)
            ->get()
            ->getResultArray();
        if ($roles === []) {
            return 0;
        }

        $roleIds = array_map('intval', array_column($roles, 'id'));

        $rows = db_connect()->table('users u')
            ->select('u.id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'inner')
            ->join('user_team_links utl', 'utl.user_id = u.id', 'left')
            ->where('u.deleted_at', null)
            ->where('utl.team_id', $teamId)
            ->whereIn('ur.role_id', $roleIds)
            ->groupBy('u.id')
            ->get()
            ->getResultArray();

        $ids = array_map('intval', array_column($rows, 'id'));
        return $this->addRecipientsBulk($eventId, 'staff', $ids);
    }
}
