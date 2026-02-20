<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatchCallups extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'match_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'athlete_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'callup_status' => ['type' => 'ENUM', 'constraint' => ['invited', 'confirmed', 'declined', 'pending'], 'default' => 'invited'],
            'is_starting' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('match_id');
        $this->forge->addKey('athlete_id');
        $this->forge->addUniqueKey(['match_id', 'athlete_id']);
        $this->forge->addForeignKey('match_id', 'matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('athlete_id', 'athletes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('match_callups');
    }

    public function down()
    {
        $this->forge->dropTable('match_callups');
    }
}
