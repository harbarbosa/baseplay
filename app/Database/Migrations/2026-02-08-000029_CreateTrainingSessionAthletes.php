<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrainingSessionAthletes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'training_session_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'athlete_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'attendance_status' => [
                'type' => 'ENUM',
                'constraint' => ['present','late','absent','justified'],
                'default' => 'present',
            ],
            'performance_note' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'rating' => [
                'type' => 'TINYINT',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('training_session_id');
        $this->forge->addKey('athlete_id');
        $this->forge->addUniqueKey(['training_session_id','athlete_id']);
        $this->forge->createTable('training_session_athletes', true);
    }

    public function down()
    {
        $this->forge->dropTable('training_session_athletes', true);
    }
}