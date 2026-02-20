<?php

namespace App\Models;

use CodeIgniter\Model;

class TacticalBoardStateModel extends Model
{
    protected $table = 'tactical_board_states';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'tactical_board_id',
        'state_json',
        'version',
        'created_by',
        'created_at',
    ];
    protected $useTimestamps = false;
}

