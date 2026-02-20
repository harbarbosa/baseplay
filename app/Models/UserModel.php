<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'name',
        'email',
        'password_hash',
        'status',
        'api_token_hash',
        'api_token_expires_at',
        'last_login_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function findByEmail(string $email): ?array
    {
        $user = $this->where('email', trim($email))->first();

        return $user ?: null;
    }
}
