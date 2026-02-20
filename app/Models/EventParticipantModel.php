<?php

namespace App\Models;

use CodeIgniter\Model;

class EventParticipantModel extends Model
{
    protected $table = 'event_participants';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'event_id',
        'athlete_id',
        'invitation_status',
        'notes',
        'created_at',
    ];
    protected $useTimestamps = false;
}
