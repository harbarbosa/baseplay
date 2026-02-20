<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatchAttachments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'match_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'type' => ['type' => 'ENUM', 'constraint' => ['file', 'link'], 'default' => 'file'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('match_id');
        $this->forge->addForeignKey('match_id', 'matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('match_attachments');
    }

    public function down()
    {
        $this->forge->dropTable('match_attachments');
    }
}
