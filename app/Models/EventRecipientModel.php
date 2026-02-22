<?php

namespace App\Models;

use CodeIgniter\Model;

class EventRecipientModel extends Model
{
    protected $table = 'event_recipients';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'event_id',
        'recipient_type',
        'recipient_id',
        'created_at',
    ];
}
