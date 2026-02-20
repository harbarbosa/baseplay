<?php

namespace App\Services;

class AlertGeneratorService
{
    protected AlertService $alerts;

    public function __construct()
    {
        $this->alerts = new AlertService();
    }

    public function generateAll(): array
    {
        return [
            'document_expiring' => $this->checkExpiringDocuments(),
            'low_attendance' => $this->checkLowAttendance(),
            'upcoming_event' => $this->checkUpcomingEvents(),
            'missing_document' => $this->checkMissingRequiredDocuments(),
        ];
    }

    public function checkExpiringDocuments(): int
    {
        $today = date('Y-m-d');
        $limit = date('Y-m-d', strtotime('+7 days'));

        $rows = db_connect()->table('documents')
            ->select('documents.id, documents.expires_at, document_types.name AS type_name, athletes.first_name, athletes.last_name')
            ->join('document_types', 'document_types.id = documents.document_type_id', 'left')
            ->join('athletes', 'athletes.id = documents.athlete_id', 'left')
            ->where('documents.deleted_at', null)
            ->where('documents.status', 'active')
            ->where('documents.expires_at >=', $today)
            ->where('documents.expires_at <=', $limit)
            ->get()
            ->getResultArray();

        $created = 0;
        foreach ($rows as $row) {
            $athleteName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $title = 'Documento vencendo em breve';
            $description = ($row['type_name'] ?? 'Documento') . ' de ' . ($athleteName !== '' ? $athleteName : 'atleta')
                . ' vence em ' . ($row['expires_at'] ?? '-');

            $id = $this->alerts->createIfNotExistsToday([
                'type' => 'document_expiring',
                'entity_type' => 'document',
                'entity_id' => (int) $row['id'],
                'title' => $title,
                'description' => $description,
                'severity' => 'warning',
            ]);

            if ($id !== null) {
                $created++;
            }
        }

        return $created;
    }

    public function checkLowAttendance(): int
    {
        $from = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $to = date('Y-m-d 23:59:59');

        $rows = db_connect()->query(
            "SELECT a.athlete_id,
                    at.first_name,
                    at.last_name,
                    SUM(a.status IN ('present','late','justified')) AS attended,
                    COUNT(*) AS total
             FROM attendance a
             LEFT JOIN events e ON e.id = a.event_id
             LEFT JOIN athletes at ON at.id = a.athlete_id
             WHERE e.deleted_at IS NULL
               AND e.start_datetime >= ?
               AND e.start_datetime <= ?
             GROUP BY a.athlete_id, at.first_name, at.last_name",
            [$from, $to]
        )->getResultArray();

        $created = 0;
        foreach ($rows as $row) {
            $total = (int) ($row['total'] ?? 0);
            if ($total <= 0) {
                continue;
            }

            $attended = (int) ($row['attended'] ?? 0);
            $rate = ($attended / $total) * 100;
            if ($rate >= 60) {
                continue;
            }

            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $title = 'Baixa frequencia de presenca';
            $description = ($name !== '' ? $name : 'Atleta') . ' com presenca de ' . round($rate, 1) . '% nos ultimos 30 dias.';

            $id = $this->alerts->createIfNotExistsToday([
                'type' => 'low_attendance',
                'entity_type' => 'athlete',
                'entity_id' => (int) $row['athlete_id'],
                'title' => $title,
                'description' => $description,
                'severity' => 'critical',
            ]);

            if ($id !== null) {
                $created++;
            }
        }

        return $created;
    }

    public function checkUpcomingEvents(): int
    {
        $from = date('Y-m-d H:i:s');
        $to = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $rows = db_connect()->table('events')
            ->select('id, title, start_datetime, type')
            ->where('deleted_at', null)
            ->where('status', 'scheduled')
            ->where('start_datetime >=', $from)
            ->where('start_datetime <=', $to)
            ->get()
            ->getResultArray();

        $created = 0;
        foreach ($rows as $row) {
            $title = 'Evento em ate 24h';
            $description = ($row['type'] ?? 'EVENT') . ' - ' . ($row['title'] ?? '-')
                . ' em ' . ($row['start_datetime'] ?? '-');

            $id = $this->alerts->createIfNotExistsToday([
                'type' => 'upcoming_event',
                'entity_type' => 'event',
                'entity_id' => (int) $row['id'],
                'title' => $title,
                'description' => $description,
                'severity' => 'info',
            ]);

            if ($id !== null) {
                $created++;
            }
        }

        return $created;
    }

    public function checkMissingRequiredDocuments(): int
    {
        $db = db_connect();

        // Optional support: if this table exists, category can define required doc types.
        if (!$db->tableExists('category_required_documents')) {
            return 0;
        }

        $requirements = $db->table('category_required_documents')
            ->select('category_id, document_type_id')
            ->get()
            ->getResultArray();

        if (empty($requirements)) {
            return 0;
        }

        $created = 0;

        foreach ($requirements as $req) {
            $categoryId = (int) ($req['category_id'] ?? 0);
            $typeId = (int) ($req['document_type_id'] ?? 0);
            if ($categoryId <= 0 || $typeId <= 0) {
                continue;
            }

            $athletes = $db->table('athletes')
                ->select('id, first_name, last_name')
                ->where('deleted_at', null)
                ->where('status', 'active')
                ->where('category_id', $categoryId)
                ->get()
                ->getResultArray();

            $type = $db->table('document_types')
                ->select('name')
                ->where('id', $typeId)
                ->get()
                ->getRowArray();

            foreach ($athletes as $athlete) {
                $exists = $db->table('documents')
                    ->where('deleted_at', null)
                    ->where('athlete_id', (int) $athlete['id'])
                    ->where('document_type_id', $typeId)
                    ->countAllResults() > 0;

                if ($exists) {
                    continue;
                }

                $name = trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''));
                $title = 'Documento obrigatorio ausente';
                $description = ($name !== '' ? $name : 'Atleta') . ' sem documento obrigatorio: ' . ($type['name'] ?? 'documento');

                $id = $this->alerts->createIfNotExistsToday([
                    'type' => 'missing_document',
                    'entity_type' => 'athlete',
                    'entity_id' => (int) $athlete['id'],
                    'title' => $title,
                    'description' => $description,
                    'severity' => 'warning',
                ]);

                if ($id !== null) {
                    $created++;
                }
            }
        }

        return $created;
    }
}