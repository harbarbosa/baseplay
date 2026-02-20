<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'event_id',
        'athlete_id',
        'status',
        'checkin_time',
        'notes',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;
}
