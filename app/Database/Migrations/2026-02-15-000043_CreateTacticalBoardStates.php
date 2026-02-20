<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTacticalBoardStates extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tactical_board_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'state_json' => [
                'type' => 'LONGTEXT',
            ],
            'version' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ],
            'created_by' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tactical_board_id');
        $this->forge->addKey('version');
        $this->forge->addUniqueKey(['tactical_board_id', 'version']);
        $this->forge->addForeignKey('tactical_board_id', 'tactical_boards', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('tactical_board_states', true);
    }

    public function down()
    {
        $this->forge->dropTable('tactical_board_states', true);
    }
}

