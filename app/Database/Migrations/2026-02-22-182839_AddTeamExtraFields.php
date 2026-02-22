<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeamExtraFields extends Migration
{
    public function up()
    {
        $fields = [
            'legal_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'trade_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'cnpj' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'foundation_date' => ['type' => 'DATE', 'null' => true],
            'president_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'vice_president_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'website' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'address_street' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'address_number' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'address_complement' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'address_neighborhood' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'address_city' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'address_state' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'address_zip' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'address_country' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
        ];

        $this->forge->addColumn('teams', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('teams', [
            'legal_name',
            'trade_name',
            'cnpj',
            'foundation_date',
            'president_name',
            'vice_president_name',
            'phone',
            'email',
            'website',
            'address_street',
            'address_number',
            'address_complement',
            'address_neighborhood',
            'address_city',
            'address_state',
            'address_zip',
            'address_country',
            'notes',
        ]);
    }
}
