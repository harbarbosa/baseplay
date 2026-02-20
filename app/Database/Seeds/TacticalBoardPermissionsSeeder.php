<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TacticalBoardPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Compatibilidade: as permissões táticas agora são consolidadas
        // em PermissionsSeeder + RolePermissionsSeeder.
        $this->call('PermissionsSeeder');
        $this->call('RolePermissionsSeeder');
    }
}
