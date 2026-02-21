<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeamBrandingFields extends Migration
{
    public function up()
    {
        $fields = [
            'primary_color' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'status',
            ],
            'secondary_color' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'primary_color',
            ],
            'logo_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'secondary_color',
            ],
        ];

        $this->forge->addColumn('teams', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('teams', ['primary_color', 'secondary_color', 'logo_path']);
    }
}
