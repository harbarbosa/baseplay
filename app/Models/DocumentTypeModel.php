<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentTypeModel extends Model
{
    protected $table = 'document_types';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'requires_expiration',
        'default_valid_days',
        'status',
        'created_at',
        'updated_at',
    ];
}