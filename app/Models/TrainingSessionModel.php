<?php

namespace App\Models;

use CodeIgniter\Model;

class TrainingSessionModel extends Model
{
    protected $table = 'training_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'team_id','category_id','event_id','training_plan_id','title','session_date',
        'start_datetime','end_datetime','location','general_notes','created_by',
        'created_at','updated_at','deleted_at',
    ];
}