<?php

namespace App\Services;

use App\Models\AuditLogModel;
use CodeIgniter\I18n\Time;

class AuditService
{
    protected AuditLogModel $auditLogs;

    public function __construct()
    {
        $this->auditLogs = new AuditLogModel();
    }

    public function log(int $userId, string $action, array $metadata = []): void
    {
        $request = service('request');
        $this->auditLogs->insert([
            'user_id'    => $userId,
            'action'     => $action,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => substr((string) $request->getUserAgent(), 0, 255),
            'metadata'   => !empty($metadata) ? json_encode($metadata) : null,
            'created_at' => Time::now()->toDateTimeString(),
        ]);
    }
}
