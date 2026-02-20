<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrainingPlans extends Migration
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
            'goal' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'planned_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'total_duration_min' => [
                'type' => 'INT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft','ready','archived'],
                'default' => 'draft',
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
        $this->forge->addKey('planned_date');
        $this->forge->addKey('status');
        $this->forge->createTable('training_plans', true);
    }

    public function down()
    {
        $this->forge->dropTable('training_plans', true);
    }
}