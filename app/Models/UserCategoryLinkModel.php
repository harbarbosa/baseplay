<?php

namespace App\Models;

use CodeIgniter\Model;

class UserCategoryLinkModel extends Model
{
    protected $table = 'user_category_links';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'category_id',
        'created_at',
    ];
}
