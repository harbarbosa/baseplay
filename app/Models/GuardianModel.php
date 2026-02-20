<?php

namespace App\Models;

use CodeIgniter\Model;

class GuardianModel extends Model
{
    protected $table = 'guardians';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'full_name',
        'phone',
        'email',
        'relation_type',
        'document_id',
        'address',
        'status',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
