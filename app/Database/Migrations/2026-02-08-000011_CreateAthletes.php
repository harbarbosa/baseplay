<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAthletes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'birth_date' => [
                'type' => 'DATE',
            ],
            'document_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'position' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'dominant_foot' => [
                'type'       => 'ENUM',
                'constraint' => ['right', 'left', 'both'],
                'null'       => true,
            ],
            'height_cm' => [
                'type' => 'INT',
                'null' => true,
            ],
            'weight_kg' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'medical_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'internal_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->addKey('category_id');
        $this->forge->addKey(['first_name', 'last_name']);
        $this->forge->addKey('birth_date');
        $this->forge->createTable('athletes', true);
    }

    public function down()
    {
        $this->forge->dropTable('athletes', true);
    }
}
