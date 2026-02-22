<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatchTacticalBoards extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'match_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'tactical_board_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['match_id', 'tactical_board_id']);
        $this->forge->addKey('match_id');
        $this->forge->addKey('tactical_board_id');
        $this->forge->addForeignKey('match_id', 'matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tactical_board_id', 'tactical_boards', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('match_tactical_boards', true);
    }

    public function down()
    {
        $this->forge->dropTable('match_tactical_boards', true);
    }
}
