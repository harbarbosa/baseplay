<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentAlerts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'document_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'alert_date' => [
                'type' => 'DATE',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('document_id');
        $this->forge->addKey('alert_date');
        $this->forge->createTable('document_alerts', true);
    }

    public function down()
    {
        $this->forge->dropTable('document_alerts', true);
    }
}