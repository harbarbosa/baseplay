<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExercises extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'objective' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'age_group' => [
                'type' => 'ENUM',
                'constraint' => ['u10','u11','u12','u13','u14','u15','u16','u17','u18','u19','u20','all'],
                'default' => 'all',
            ],
            'intensity' => [
                'type' => 'ENUM',
                'constraint' => ['low','medium','high'],
                'default' => 'medium',
            ],
            'duration_min' => [
                'type' => 'INT',
                'null' => true,
            ],
            'players_min' => [
                'type' => 'INT',
                'null' => true,
            ],
            'players_max' => [
                'type' => 'INT',
                'null' => true,
            ],
            'materials' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'video_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active','inactive'],
                'default' => 'active',
            ],
            'created_by' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addKey('title');
        $this->forge->addKey('objective');
        $this->forge->addKey('age_group');
        $this->forge->addKey('intensity');
        $this->forge->createTable('exercises', true);
    }

    public function down()
    {
        $this->forge->dropTable('exercises', true);
    }
}
