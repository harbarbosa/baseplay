<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatchEvents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'match_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'athlete_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'event_type' => ['type' => 'ENUM', 'constraint' => ['goal', 'assist', 'yellow_card', 'red_card', 'sub_in', 'sub_out', 'injury', 'other']],
            'minute' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_athlete_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('match_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('athlete_id');
        $this->forge->addForeignKey('match_id', 'matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('athlete_id', 'athletes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('related_athlete_id', 'athletes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('match_events');
    }

    public function down()
    {
        $this->forge->dropTable('match_events');
    }
}
