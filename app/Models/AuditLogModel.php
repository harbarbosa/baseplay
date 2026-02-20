<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'action', 'ip_address', 'user_agent', 'metadata', 'created_at'];
    protected $useTimestamps = false;
}
