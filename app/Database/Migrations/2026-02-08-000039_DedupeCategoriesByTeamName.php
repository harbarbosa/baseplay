<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DedupeCategoriesByTeamName extends Migration
{
    public function up()
    {
        $sql = "UPDATE categories c\n" .
            "JOIN (\n" .
            "  SELECT team_id, name, MIN(id) AS keep_id\n" .
            "  FROM categories\n" .
            "  WHERE deleted_at IS NULL\n" .
            "  GROUP BY team_id, name\n" .
            "  HAVING COUNT(*) > 1\n" .
            ") d ON c.team_id = d.team_id AND c.name = d.name AND c.id <> d.keep_id\n" .
            "SET c.deleted_at = NOW(), c.updated_at = NOW()";

        $this->db->query($sql);
    }

    public function down()
    {
        // No safe rollback for dedupe
    }
}