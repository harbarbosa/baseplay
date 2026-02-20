<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocuments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'document_type_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'athlete_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'team_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'original_name' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
                'null' => true,
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'file_size' => [
                'type' => 'INT',
                'null' => true,
            ],
            'issued_at' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'uploaded_by' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],
            'notes' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'expired', 'archived'],
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('athlete_id');
        $this->forge->addKey('team_id');
        $this->forge->addKey('document_type_id');
        $this->forge->addKey('expires_at');
        $this->forge->addKey('status');
        $this->forge->createTable('documents', true);
    }

    public function down()
    {
        $this->forge->dropTable('documents', true);
    }
}