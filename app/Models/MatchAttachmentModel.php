<?php

namespace App\Models;

use CodeIgniter\Model;

class MatchAttachmentModel extends Model
{
    protected $table = 'match_attachments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'match_id',
        'file_path',
        'url',
        'original_name',
        'type',
    ];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
}