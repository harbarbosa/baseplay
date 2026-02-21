<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('PermissionsSeeder');
        $this->call('RolesSeeder');
        $this->call('RolePermissionsSeeder');

        $this->call('AdminUserSeeder');
        $this->call('TeamsCategoriesSeeder');
        $this->call('AthletesGuardiansSeeder');
        $this->call('EventsSeeder');
        $this->call('NoticesSeeder');
        $this->call('DocumentTypesSeeder');
        $this->call('DocumentsSeeder');
        $this->call('TacticalBoardsSeeder');
        $this->call('TemplatesSeeder');
    }
}
