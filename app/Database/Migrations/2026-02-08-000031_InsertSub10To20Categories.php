<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\I18n\Time;

class InsertSub10To20Categories extends Migration
{
    public function up()
    {
        $teams = $this->db->table('teams')->select('id')->get()->getResultArray();
        if (!$teams) {
            return;
        }

        $now = Time::now()->toDateTimeString();

        foreach ($teams as $team) {
            $teamId = (int) $team['id'];
            for ($age = 10; $age <= 20; $age++) {
                $name = 'Sub-' . $age;
                $exists = $this->db->table('categories')
                    ->where('team_id', $teamId)
                    ->where('name', $name)
                    ->where('deleted_at', null)
                    ->get()->getRowArray();
                if ($exists) {
                    continue;
                }

                $this->db->table('categories')->insert([
                    'team_id' => $teamId,
                    'name' => $name,
                    'year_from' => null,
                    'year_to' => null,
                    'gender' => 'mixed',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        for ($age = 10; $age <= 20; $age++) {
            $name = 'Sub-' . $age;
            $this->db->table('categories')->where('name', $name)->delete();
        }
    }
}