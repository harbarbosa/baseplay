<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeamIdToRoles extends Migration
{
    public function up()
    {
        $fields = [
            'team_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'description',
            ],
        ];

        $this->forge->addColumn('roles', $fields);
        $this->forge->addKey('team_id');
    }

    public function down()
    {
        $this->forge->dropColumn('roles', 'team_id');
    }
}
