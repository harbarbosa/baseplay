<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrainingSessions extends Migration
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
            'event_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'training_plan_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'session_date' => [
                'type' => 'DATE',
            ],
            'start_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
                'null' => true,
            ],
            'general_notes' => [
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
        $this->forge->addKey('session_date');
        $this->forge->createTable('training_sessions', true);
    }

    public function down()
    {
        $this->forge->dropTable('training_sessions', true);
    }
}