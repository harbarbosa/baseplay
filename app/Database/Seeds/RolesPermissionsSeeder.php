<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Seeder legado mantido por compatibilidade com comandos já usados.
        $this->call('PermissionsSeeder');
        $this->call('RolesSeeder');
        $this->call('RolePermissionsSeeder');
    }
}
