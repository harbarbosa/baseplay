<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatchReports extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'match_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'strengths' => ['type' => 'TEXT', 'null' => true],
            'weaknesses' => ['type' => 'TEXT', 'null' => true],
            'next_actions' => ['type' => 'TEXT', 'null' => true],
            'coach_notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('match_id');
        $this->forge->addForeignKey('match_id', 'matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('match_reports');
    }

    public function down()
    {
        $this->forge->dropTable('match_reports');
    }
}
