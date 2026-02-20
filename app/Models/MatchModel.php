<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchModel extends Model
{
    protected $table = 'matches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'team_id',
        'category_id',
        'event_id',
        'opponent_name',
        'competition_name',
        'round_name',
        'match_date',
        'start_time',
        'location',
        'home_away',
        'status',
        'score_for',
        'score_against',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}