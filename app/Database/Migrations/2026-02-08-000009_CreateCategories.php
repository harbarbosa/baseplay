<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategories extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'team_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'year_from' => [
                'type' => 'INT',
                'null' => true,
            ],
            'year_to' => [
                'type' => 'INT',
                'null' => true,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['mixed', 'male', 'female'],
                'default'    => 'mixed',
            ],
            'training_days' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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
        $this->forge->addKey('team_id');
        $this->forge->addKey('name');
        $this->forge->createTable('categories', true);
    }

    public function down()
    {
        $this->forge->dropTable('categories', true);
    }
}
