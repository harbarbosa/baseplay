<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserCategoryLinks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'category_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('category_id');
        $this->forge->addUniqueKey(['user_id', 'category_id']);
        $this->forge->createTable('user_category_links', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_category_links', true);
    }
}
