<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventParticipants extends Migration
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
            'invitation_status' => [
                'type'       => 'ENUM',
                'constraint' => ['invited', 'confirmed', 'declined', 'pending'],
                'default'    => 'invited',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('athlete_id');
        $this->forge->addUniqueKey(['event_id', 'athlete_id']);
        $this->forge->createTable('event_participants', true);
    }

    public function down()
    {
        $this->forge->dropTable('event_participants', true);
    }
}
