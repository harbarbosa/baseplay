<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMatchesIdToBigint extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE matches MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE matches MODIFY id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT");
    }
}