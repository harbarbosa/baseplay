<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMediaToTrainingPlanBlocks extends Migration
{
    public function up()
    {
        $fields = [
            'media_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'order_index',
            ],
            'media_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'media_url',
            ],
            'media_name' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
                'null' => true,
                'after' => 'media_path',
            ],
            'media_mime' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'media_name',
            ],
        ];

        $this->forge->addColumn('training_plan_blocks', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('training_plan_blocks', ['media_url', 'media_path', 'media_name', 'media_mime']);
    }
}
