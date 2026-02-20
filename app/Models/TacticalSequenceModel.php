<?php

namespace App\Models;

use CodeIgniter\Model;

class TacticalSequenceModel extends Model
{
    protected $table = 'tactical_sequences';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'tactical_board_id',
        'title',
        'description',
        'fps',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}

