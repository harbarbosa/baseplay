<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolePermissions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'role_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'permission_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['role_id', 'permission_id'], true);
        $this->forge->addKey('permission_id');
        $this->forge->createTable('role_permissions', true);
    }

    public function down()
    {
        $this->forge->dropTable('role_permissions', true);
    }
}
