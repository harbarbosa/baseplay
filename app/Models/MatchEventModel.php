<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchEventModel extends Model
{
    protected $table = 'match_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'match_id',
        'athlete_id',
        'event_type',
        'minute',
        'related_athlete_id',
        'notes',
    ];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
}