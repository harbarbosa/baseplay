<?php

namespace App\Models;

use CodeIgniter\Model;

class ExerciseTagModel extends Model
{
    protected $table = 'exercise_tags';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['name','created_at'];
}