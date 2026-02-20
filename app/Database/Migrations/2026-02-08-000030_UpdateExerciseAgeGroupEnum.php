<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateExerciseAgeGroupEnum extends Migration
{
    public function up()
    {
        $allowed = "'u10','u11','u12','u13','u14','u15','u16','u17','u18','u19','u20','all'";

        // Normalize any legacy values to closest valid one
        $this->db->query("UPDATE exercises SET age_group = 'u10' WHERE age_group IN ('u7','u9')");
        $this->db->query("UPDATE exercises SET age_group = 'all' WHERE age_group NOT IN ($allowed)");

        $this->db->query("ALTER TABLE exercises MODIFY age_group ENUM($allowed) NOT NULL DEFAULT 'all'");
    }

    public function down()
    {
        $allowed = "'u7','u9','u11','u13','u15','u17','u20','all'";

        // Fallback any new values to 'all' before reverting enum
        $this->db->query("UPDATE exercises SET age_group = 'all' WHERE age_group NOT IN ($allowed)");
        $this->db->query("ALTER TABLE exercises MODIFY age_group ENUM($allowed) NOT NULL DEFAULT 'all'");
    }
}