<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchTacticalBoardModel extends Model
{
    protected $table = 'match_tactical_boards';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'match_id',
        'tactical_board_id',
        'created_at',
    ];
    public $useTimestamps = false;
}
