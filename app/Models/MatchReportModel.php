<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchReportModel extends Model
{
    protected $table = 'match_reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'match_id',
        'summary',
        'strengths',
        'weaknesses',
        'next_actions',
        'coach_notes',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}