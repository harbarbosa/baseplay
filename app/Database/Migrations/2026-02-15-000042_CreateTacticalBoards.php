<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTacticalBoards extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'team_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'category_id' => [
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
        $this->forge->addKey('team_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('title');
        $this->forge->addForeignKey('team_id', 'teams', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('tactical_boards', true);
    }

    public function down()
    {
        $this->forge->dropTable('tactical_boards', true);
    }
}

