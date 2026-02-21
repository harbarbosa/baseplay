<?php

namespace App\Models;

use CodeIgniter\Model;

class TacticalBoardTemplateModel extends Model
{
    protected $table = 'tactical_board_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'title',
        'description',
        'field_type',
        'tags',
        'is_default',
        'is_active',
        'preview_image',
        'template_json',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
