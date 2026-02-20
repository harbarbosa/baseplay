<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatches extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'team_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'category_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'event_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'opponent_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'competition_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'round_name' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'match_date' => ['type' => 'DATE'],
            'start_time' => ['type' => 'TIME', 'null' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'home_away' => ['type' => 'ENUM', 'constraint' => ['home', 'away', 'neutral'], 'default' => 'neutral'],
            'status' => ['type' => 'ENUM', 'constraint' => ['scheduled', 'completed', 'cancelled'], 'default' => 'scheduled'],
            'score_for' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'score_against' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('team_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('match_date');
        $this->forge->addKey('status');
        $this->forge->addKey('opponent_name');
        $this->forge->addForeignKey('team_id', 'teams', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('event_id', 'events', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('matches');
    }

    public function down()
    {
        $this->forge->dropTable('matches');
    }
}
