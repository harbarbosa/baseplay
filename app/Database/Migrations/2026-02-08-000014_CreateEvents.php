<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'team_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'category_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['TRAINING', 'MATCH', 'MEETING', 'EVALUATION', 'TRAVEL'],
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'start_datetime' => [
                'type' => 'DATETIME',
            ],
            'end_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'cancelled', 'completed'],
                'default'    => 'scheduled',
            ],
            'created_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('start_datetime');
        $this->forge->addKey('type');
        $this->forge->createTable('events', true);
    }

    public function down()
    {
        $this->forge->dropTable('events', true);
    }
}
