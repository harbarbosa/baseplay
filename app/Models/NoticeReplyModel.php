<?php

namespace App\Models;

use CodeIgniter\Model;

class NoticeReplyModel extends Model
{
    protected $table = 'notice_replies';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'notice_id',
        'user_id',
        'message',
        'created_at',
    ];
}