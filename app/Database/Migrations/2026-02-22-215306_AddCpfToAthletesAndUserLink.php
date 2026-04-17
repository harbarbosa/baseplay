<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCpfToAthletesAndUserLink extends Migration
{
    public function up()
    {
        $this->forge->addColumn('athletes', [
            'cpf' => ['type' => 'VARCHAR', 'constraint' => 14, 'null' => true],
        ]);

        $this->forge->addColumn('users', [
            'athlete_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'must_change_password' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);

        $this->forge->addKey('cpf', false, false, 'idx_athletes_cpf');
        $this->forge->addUniqueKey('cpf', 'uk_athletes_cpf');
        $this->forge->processIndexes('athletes');

        $this->forge->addKey('athlete_id', false, false, 'idx_users_athlete');
        $this->forge->addUniqueKey('athlete_id', 'uk_users_athlete');
        $this->forge->processIndexes('users');
    }

    public function down()
    {
        $this->forge->dropKey('athletes', 'uk_athletes_cpf');
        $this->forge->dropKey('athletes', 'idx_athletes_cpf');
        $this->forge->dropKey('users', 'uk_users_athlete');
        $this->forge->dropKey('users', 'idx_users_athlete');
        $this->forge->dropColumn('users', ['athlete_id', 'must_change_password']);
        $this->forge->dropColumn('athletes', ['cpf']);
    }
}
