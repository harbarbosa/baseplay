<?php

namespace App\Models;

use CodeIgniter\Model;

class TrainingSessionAthleteModel extends Model
{
    protected $table = 'training_session_athletes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'training_session_id','athlete_id','attendance_status','performance_note',
        'rating','created_at','updated_at',
    ];
}