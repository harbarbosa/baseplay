<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class TeamsCategoriesSeeder extends Seeder
{
    public function run()
    {
        $teamName = 'BasePlay Academy';
        $team = $this->db->table('teams')->where('name', $teamName)->get()->getRowArray();

        if (!$team) {
            $this->db->table('teams')->insert([
                'name' => $teamName,
                'short_name' => 'BasePlay',
                'description' => 'Equipe base para testes',
                'status' => 'active',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
            $teamId = $this->db->insertID();
        } else {
            $teamId = $team['id'];
        }

        $categories = [];
        for ($age = 10; $age <= 20; $age++) {
            $categories[] = ['name' => 'Sub-' . $age, 'year_from' => null, 'year_to' => null];
        }

        foreach ($categories as $category) {
            $exists = $this->db->table('categories')
                ->where('team_id', $teamId)
                ->where('name', $category['name'])
                ->get()->getRowArray();
            if ($exists) {
                continue;
            }
            $this->db->table('categories')->insert([
                'team_id' => $teamId,
                'name' => $category['name'],
                'year_from' => $category['year_from'],
                'year_to' => $category['year_to'],
                'gender' => 'mixed',
                'status' => 'active',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
        }
    }
}