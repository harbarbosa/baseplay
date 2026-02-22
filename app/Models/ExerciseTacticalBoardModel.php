<?php

namespace App\Models;

use CodeIgniter\Model;

class ExerciseTacticalBoardModel extends Model
{
    protected $table = 'exercise_tactical_boards';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'exercise_id',
        'tactical_board_id',
        'created_at',
    ];
    public $useTimestamps = false;
}
