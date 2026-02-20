<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNoticeReplies extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'notice_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'message' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('notice_id');
        $this->forge->addKey('user_id');
        $this->forge->createTable('notice_replies', true);
    }

    public function down()
    {
        $this->forge->dropTable('notice_replies', true);
    }
}