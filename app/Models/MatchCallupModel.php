<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchCallupModel extends Model
{
    protected $table = 'match_callups';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'match_id',
        'athlete_id',
        'callup_status',
        'is_starting',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}