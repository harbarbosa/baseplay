<?php

namespace App\Models;

use CodeIgniter\Model;

class NoticeReadModel extends Model
{
    protected $table = 'notice_reads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'notice_id',
        'user_id',
        'read_at',
    ];
}