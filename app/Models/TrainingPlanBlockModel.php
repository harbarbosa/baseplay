<?php

namespace App\Models;

use CodeIgniter\Model;

class TrainingPlanBlockModel extends Model
{
    protected $table = 'training_plan_blocks';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'training_plan_id','block_type','title','duration_min','exercise_id',
        'instructions','order_index','media_url','media_path','media_name','media_mime','created_at','updated_at',
    ];
}
