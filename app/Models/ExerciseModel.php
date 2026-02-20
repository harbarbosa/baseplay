<?php

namespace App\Models;

use CodeIgniter\Model;

class ExerciseModel extends Model
{
    protected $table = 'exercises';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'title','objective','description','age_group','intensity','duration_min',
        'players_min','players_max','materials','video_url','status','created_by',
        'created_at','updated_at','deleted_at',
    ];
}