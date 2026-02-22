<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeTacticalBoardsCategoryNullable extends Migration
{
    public function up()
    {
        // Drop FK to allow altering column
        $this->forge->dropForeignKey('tactical_boards', 'tactical_boards_category_id_foreign');

        $this->forge->modifyColumn('tactical_boards', [
            'category_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
        ]);

        // Re-add FK allowing null and using SET NULL on delete
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'RESTRICT');
    }

    public function down()
    {
        $this->forge->dropForeignKey('tactical_boards', 'tactical_boards_category_id_foreign');

        $this->forge->modifyColumn('tactical_boards', [
            'category_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => false,
            ],
        ]);

        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'RESTRICT');
    }
}
