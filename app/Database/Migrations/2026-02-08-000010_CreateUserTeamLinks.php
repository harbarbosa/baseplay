<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTeamLinks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'team_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'role_in_team' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('team_id');
        $this->forge->createTable('user_team_links', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_team_links', true);
    }
}
