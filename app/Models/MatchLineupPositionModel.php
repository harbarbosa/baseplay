<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchLineupPositionModel extends Model
{
    protected $table = 'match_lineup_positions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'match_id',
        'athlete_id',
        'lineup_role',
        'position_code',
        'shirt_number',
        'x',
        'y',
        'order_index',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}