<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'requires_expiration' => [
                'type' => 'TINYINT',
                'default' => 0,
            ],
            'default_valid_days' => [
                'type' => 'INT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
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
        $this->forge->addKey('name');
        $this->forge->createTable('document_types', true);
    }

    public function down()
    {
        $this->forge->dropTable('document_types', true);
    }
}