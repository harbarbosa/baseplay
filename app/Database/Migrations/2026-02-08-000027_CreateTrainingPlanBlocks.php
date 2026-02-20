<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrainingPlanBlocks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'training_plan_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'block_type' => [
                'type' => 'ENUM',
                'constraint' => ['warmup','technical','tactical','physical','small_sided','match','other'],
                'default' => 'other',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'duration_min' => [
                'type' => 'INT',
            ],
            'exercise_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'instructions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'order_index' => [
                'type' => 'INT',
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
        $this->forge->addKey('training_plan_id');
        $this->forge->addKey('order_index');
        $this->forge->createTable('training_plan_blocks', true);
    }

    public function down()
    {
        $this->forge->dropTable('training_plan_blocks', true);
    }
}