<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTacticalSequenceFrames extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tactical_sequence_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'frame_index' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'frame_json' => [
                'type' => 'LONGTEXT',
            ],
            'duration_ms' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 500,
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
        $this->forge->addKey('tactical_sequence_id');
        $this->forge->addKey('frame_index');
        $this->forge->addUniqueKey(['tactical_sequence_id', 'frame_index']);
        $this->forge->addForeignKey('tactical_sequence_id', 'tactical_sequences', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('tactical_sequence_frames', true);
    }

    public function down()
    {
        $this->forge->dropTable('tactical_sequence_frames', true);
    }
}

