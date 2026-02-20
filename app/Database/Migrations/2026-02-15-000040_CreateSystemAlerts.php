<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemAlerts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'organization_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
            ],
            'entity_type' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'entity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'severity' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'info',
            ],
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['type']);
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey(['is_read']);
        $this->forge->addKey(['created_at']);
        $this->forge->createTable('system_alerts', true);
    }

    public function down()
    {
        $this->forge->dropTable('system_alerts', true);
    }
}