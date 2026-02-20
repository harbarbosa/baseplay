<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatchLineupPositions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'match_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'athlete_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'lineup_role' => ['type' => 'ENUM', 'constraint' => ['starting', 'bench'], 'default' => 'starting'],
            'position_code' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'shirt_number' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'x' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'y' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'order_index' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('match_id');
        $this->forge->addKey('athlete_id');
        $this->forge->addUniqueKey(['match_id', 'athlete_id']);
        $this->forge->addForeignKey('match_id', 'matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('athlete_id', 'athletes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('match_lineup_positions');
    }

    public function down()
    {
        $this->forge->dropTable('match_lineup_positions');
    }
}
