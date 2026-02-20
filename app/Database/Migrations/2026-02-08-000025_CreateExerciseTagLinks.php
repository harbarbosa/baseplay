<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExerciseTagLinks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'exercise_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'tag_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('exercise_id');
        $this->forge->addKey('tag_id');
        $this->forge->addUniqueKey(['exercise_id','tag_id']);
        $this->forge->createTable('exercise_tag_links', true);
    }

    public function down()
    {
        $this->forge->dropTable('exercise_tag_links', true);
    }
}