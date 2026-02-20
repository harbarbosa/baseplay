<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTacticalSequences extends Migration
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fps' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 2,
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
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tactical_board_id');
        $this->forge->addKey('fps');
        $this->forge->addForeignKey('tactical_board_id', 'tactical_boards', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('tactical_sequences', true);
    }

    public function down()
    {
        $this->forge->dropTable('tactical_sequences', true);
    }
}

