<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNoticeReads extends Migration
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
            'read_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('notice_id');
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey(['notice_id', 'user_id']);
        $this->forge->createTable('notice_reads', true);
    }

    public function down()
    {
        $this->forge->dropTable('notice_reads', true);
    }
}