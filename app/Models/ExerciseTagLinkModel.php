<?php

namespace App\Models;

use CodeIgniter\Model;

class ExerciseTagLinkModel extends Model
{
    protected $table = 'exercise_tag_links';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['exercise_id','tag_id'];
}