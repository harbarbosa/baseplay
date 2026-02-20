<?php

namespace App\Models;

use CodeIgniter\Model;

class TacticalSequenceFrameModel extends Model
{
    protected $table = 'tactical_sequence_frames';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'tactical_sequence_id',
        'frame_index',
        'frame_json',
        'duration_ms',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

