<?php

namespace App\Models;

use CodeIgniter\Model;

class UserTeamLinkModel extends Model
{
    protected $table = 'user_team_links';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'team_id',
        'role_in_team',
        'created_at',
    ];
    protected $useTimestamps = false;
}
