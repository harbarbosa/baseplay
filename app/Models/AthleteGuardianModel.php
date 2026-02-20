<?php

namespace App\Models;

use CodeIgniter\Model;

class AthleteGuardianModel extends Model
{
    protected $table = 'athlete_guardians';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'athlete_id',
        'guardian_id',
        'is_primary',
        'notes',
        'created_at',
    ];
    protected $useTimestamps = false;
}
