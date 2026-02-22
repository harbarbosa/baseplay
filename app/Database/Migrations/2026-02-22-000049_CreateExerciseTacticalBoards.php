<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExerciseTacticalBoards extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'exercise_id' => [
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
        $this->forge->addUniqueKey(['exercise_id', 'tactical_board_id']);
        $this->forge->addKey('exercise_id');
        $this->forge->addKey('tactical_board_id');
        $this->forge->addForeignKey('exercise_id', 'exercises', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tactical_board_id', 'tactical_boards', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('exercise_tactical_boards', true);
    }

    public function down()
    {
        $this->forge->dropTable('exercise_tactical_boards', true);
    }
}
