<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotices extends Migration
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
                'null' => true,
            ],
            'category_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'created_by' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'priority' => [
                'type' => 'ENUM',
                'constraint' => ['normal', 'important', 'urgent'],
                'default' => 'normal',
            ],
            'publish_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'published', 'archived'],
                'default' => 'published',
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
        $this->forge->addKey('priority');
        $this->forge->addKey('publish_at');
        $this->forge->createTable('notices', true);
    }

    public function down()
    {
        $this->forge->dropTable('notices', true);
    }
}