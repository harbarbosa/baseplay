<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class RolesSeeder extends Seeder
{
    /**
     * @return list<array{name:string,description:string}>
     */
    public static function definitions(): array
    {
        return [
            ['name' => 'admin', 'description' => 'Administrador'],
            ['name' => 'admin_equipe', 'description' => 'Administrador de equipe'],
            ['name' => 'treinador', 'description' => 'Treinador'],
            ['name' => 'auxiliar', 'description' => 'Auxiliar'],
            ['name' => 'atleta', 'description' => 'Atleta'],
            ['name' => 'responsavel', 'description' => 'Responsável'],
        ];
    }

    public function run()
    {
        $now = Time::now()->toDateTimeString();

        foreach (self::definitions() as $role) {
            $exists = $this->db->table('roles')->where('name', $role['name'])->get()->getRowArray();
            if ($exists) {
                $this->db->table('roles')->where('id', (int) $exists['id'])->update([
                    'description' => $role['description'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            $this->db->table('roles')->insert([
                'name' => $role['name'],
                'description' => $role['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Migração de nomes legados para o papel admin.
        $adminRole = $this->db->table('roles')->where('name', 'admin')->get()->getRowArray();
        if (!$adminRole) {
            return;
        }

        $adminRoleId = (int) $adminRole['id'];
        $legacyRoleIds = $this->db->table('roles')
            ->select('id')
            ->whereIn('name', ['cordenador', 'coordenador', 'superadmin'])
            ->get()
            ->getResultArray();

        foreach ($legacyRoleIds as $legacy) {
            $legacyId = (int) $legacy['id'];

            $rows = $this->db->table('user_roles')->where('role_id', $legacyId)->get()->getResultArray();
            foreach ($rows as $row) {
                $exists = $this->db->table('user_roles')
                    ->where('user_id', (int) $row['user_id'])
                    ->where('role_id', $adminRoleId)
                    ->get()
                    ->getRowArray();

                if (!$exists) {
                    $this->db->table('user_roles')->insert([
                        'user_id' => (int) $row['user_id'],
                        'role_id' => $adminRoleId,
                        'created_at' => $now,
                    ]);
                }
            }

            $this->db->table('role_permissions')->where('role_id', $legacyId)->delete();
            $this->db->table('user_roles')->where('role_id', $legacyId)->delete();
            $this->db->table('roles')->where('id', $legacyId)->delete();
        }
    }
}
