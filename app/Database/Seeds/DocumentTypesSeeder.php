<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DocumentTypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'RG', 'requires_expiration' => 0, 'default_valid_days' => null],
            ['name' => 'Certidão de nascimento', 'requires_expiration' => 0, 'default_valid_days' => null],
            ['name' => 'Atestado médico', 'requires_expiration' => 1, 'default_valid_days' => 365],
            ['name' => 'Autorização de viagem', 'requires_expiration' => 0, 'default_valid_days' => null],
            ['name' => 'Termo de imagem', 'requires_expiration' => 0, 'default_valid_days' => null],
        ];

        foreach ($types as $type) {
            $exists = $this->db->table('document_types')->where('name', $type['name'])->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $this->db->table('document_types')->insert([
                'name' => $type['name'],
                'requires_expiration' => $type['requires_expiration'],
                'default_valid_days' => $type['default_valid_days'],
                'status' => 'active',
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]);
        }
    }
}