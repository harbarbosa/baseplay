<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAthleteGuardians extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'athlete_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'guardian_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'is_primary' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('athlete_id');
        $this->forge->addKey('guardian_id');
        $this->forge->addUniqueKey(['athlete_id', 'guardian_id']);
        $this->forge->createTable('athlete_guardians', true);
    }

    public function down()
    {
        $this->forge->dropTable('athlete_guardians', true);
    }
}
