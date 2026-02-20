<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendance extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'athlete_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'late', 'absent', 'justified'],
            ],
            'checkin_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addKey('event_id');
        $this->forge->addKey('athlete_id');
        $this->forge->addKey('status');
        $this->forge->addUniqueKey(['event_id', 'athlete_id']);
        $this->forge->createTable('attendance', true);
    }

    public function down()
    {
        $this->forge->dropTable('attendance', true);
    }
}
