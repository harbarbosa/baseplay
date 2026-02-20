<?php

namespace App\Services;

use App\Models\EventModel;
use App\Models\NoticeModel;
use CodeIgniter\I18n\Time;

class NoticeNotificationService
{
    protected NoticeModel $notices;
    protected EventModel $events;

    public function __construct()
    {
        $this->notices = new NoticeModel();
        $this->events = new EventModel();
    }

    public function eventCreated(int $eventId, ?int $userId = null): ?int
    {
        return $this->createFromEvent($eventId, 'created', $userId);
    }

    public function eventUpdated(int $eventId, ?int $userId = null): ?int
    {
        return $this->createFromEvent($eventId, 'updated', $userId);
    }

    protected function createFromEvent(int $eventId, string $action, ?int $userId = null): ?int
    {
        $event = $this->events->find($eventId);
        if (!$event) {
            return null;
        }

        $label = $action === 'updated' ? 'Evento atualizado' : 'Novo evento';
        $title = $label . ': ' . $event['title'];
        $when = date('d/m/Y H:i', strtotime($event['start_datetime']));
        $message = $label . " - " . $event['title'] . "\n";
        $message .= "Data/Hora: " . $when . "\n";
        $message .= "Veja detalhes: /events/" . $eventId;

        $payload = [
            'team_id' => $event['team_id'],
            'category_id' => $event['category_id'],
            'title' => $title,
            'message' => $message,
            'created_by' => $userId,
            'priority' => 'normal',
            'publish_at' => Time::now()->toDateTimeString(),
            'status' => 'published',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        return (int) $this->notices->insert($payload);
    }
}
