<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemAlertModel extends Model
{
    protected $table = 'system_alerts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'organization_id',
        'type',
        'entity_type',
        'entity_id',
        'title',
        'description',
        'severity',
        'is_read',
        'created_at',
        'read_at',
    ];

    protected $useTimestamps = false;
}