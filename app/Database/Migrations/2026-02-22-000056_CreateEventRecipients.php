<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventRecipients extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'recipient_type' => [
                'type' => 'ENUM',
                'constraint' => ['guardian', 'staff'],
            ],
            'recipient_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('recipient_type');
        $this->forge->addKey('recipient_id');
        $this->forge->addUniqueKey(['event_id', 'recipient_type', 'recipient_id']);
        $this->forge->createTable('event_recipients', true);
    }

    public function down()
    {
        $this->forge->dropTable('event_recipients', true);
    }
}
