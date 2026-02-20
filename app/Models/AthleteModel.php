<?php

namespace App\Models;

use CodeIgniter\Model;

class AthleteModel extends Model
{
    protected $table = 'athletes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'category_id',
        'first_name',
        'last_name',
        'birth_date',
        'document_id',
        'position',
        'dominant_foot',
        'height_cm',
        'weight_kg',
        'medical_notes',
        'internal_notes',
        'status',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
