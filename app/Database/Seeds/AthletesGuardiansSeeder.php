<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class AthletesGuardiansSeeder extends Seeder
{
    public function run()
    {
        $categories = $this->db->table('categories')->where('deleted_at', null)->get()->getResultArray();
        if (!$categories) {
            return;
        }

        foreach ($categories as $category) {
            $exists = $this->db->table('athletes')
                ->where('category_id', $category['id'])
                ->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $this->db->table('athletes')->insert([
                'category_id' => $category['id'],
                'first_name' => 'Atleta',
                'last_name' => $category['name'],
                'birth_date' => '2012-01-01',
                'status' => 'active',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
        }

        $guardian1 = $this->db->table('guardians')->where('email', 'responsavel1@baseplay.local')->get()->getRowArray();
        if (!$guardian1) {
            $this->db->table('guardians')->insert([
                'full_name' => 'ResponsÃ¡vel Principal',
                'phone' => '11 99999-0001',
                'email' => 'responsavel1@baseplay.local',
                'relation_type' => 'Pai',
                'status' => 'active',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
            $guardian1Id = $this->db->insertID();
        } else {
            $guardian1Id = $guardian1['id'];
        }

        $guardian2 = $this->db->table('guardians')->where('email', 'responsavel2@baseplay.local')->get()->getRowArray();
        if (!$guardian2) {
            $this->db->table('guardians')->insert([
                'full_name' => 'ResponsÃ¡vel SecundÃ¡rio',
                'phone' => '11 99999-0002',
                'email' => 'responsavel2@baseplay.local',
                'relation_type' => 'Mï¿½fÂ¯ï¿½,Â¿ï¿½,Â½e',
                'status' => 'active',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
            $guardian2Id = $this->db->insertID();
        } else {
            $guardian2Id = $guardian2['id'];
        }

        $athlete = $this->db->table('athletes')->orderBy('id', 'ASC')->get()->getRowArray();
        if ($athlete) {
            $link = $this->db->table('athlete_guardians')
                ->where('athlete_id', $athlete['id'])
                ->where('guardian_id', $guardian1Id)
                ->get()->getRowArray();
            if (!$link) {
                $this->db->table('athlete_guardians')->insert([
                    'athlete_id' => $athlete['id'],
                    'guardian_id' => $guardian1Id,
                    'is_primary' => 1,
                    'created_at' => Time::now()->toDateTimeString(),
                ]);
            }

            $link2 = $this->db->table('athlete_guardians')
                ->where('athlete_id', $athlete['id'])
                ->where('guardian_id', $guardian2Id)
                ->get()->getRowArray();
            if (!$link2) {
                $this->db->table('athlete_guardians')->insert([
                    'athlete_id' => $athlete['id'],
                    'guardian_id' => $guardian2Id,
                    'is_primary' => 0,
                    'created_at' => Time::now()->toDateTimeString(),
                ]);
            }
        }
    }
}
