<?php

namespace App\Models;

use CodeIgniter\Model;

class NoticeModel extends Model
{
    protected $table = 'notices';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'team_id',
        'category_id',
        'title',
        'message',
        'created_by',
        'priority',
        'publish_at',
        'expires_at',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}