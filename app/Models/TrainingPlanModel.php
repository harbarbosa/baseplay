<?php

namespace App\Models;

use CodeIgniter\Model;

class TrainingPlanModel extends Model
{
    protected $table = 'training_plans';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'team_id','category_id','title','goal','planned_date','total_duration_min',
        'status','created_by','created_at','updated_at','deleted_at',
    ];
}