<?php

namespace App\Models;

use CodeIgniter\Model;

class TacticalBoardModel extends Model
{
    protected $table = 'tactical_boards';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'team_id',
        'category_id',
        'title',
        'description',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}

