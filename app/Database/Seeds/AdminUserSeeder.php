<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = 'admin@baseplay.local';

        $existing = $this->db->table('users')->where('email', $adminEmail)->get()->getRowArray();
        if ($existing) {
            return;
        }

        $this->db->table('users')->insert([
            'name'          => 'Administrador',
            'email'         => $adminEmail,
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'status'        => 'active',
            'created_at'    => Time::now()->toDateTimeString(),
            'updated_at'    => Time::now()->toDateTimeString(),
        ]);

        $userId = $this->db->insertID();
        $role = $this->db->table('roles')->where('name', 'admin')->get()->getRowArray();

        if ($role) {
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $role['id'],
                'created_at' => Time::now()->toDateTimeString(),
            ]);
        }
    }
}
