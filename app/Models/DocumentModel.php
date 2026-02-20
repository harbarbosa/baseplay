<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'document_type_id',
        'athlete_id',
        'team_id',
        'guardian_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'issued_at',
        'expires_at',
        'uploaded_by',
        'notes',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
