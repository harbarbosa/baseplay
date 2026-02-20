<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DocumentsSeeder extends Seeder
{
    public function run()
    {
        $type = $this->db->table('document_types')->where('name', 'RG')->get()->getRowArray();
        $athlete = $this->db->table('athletes')->orderBy('id', 'ASC')->get()->getRowArray();
        $user = $this->db->table('users')->where('email', 'admin@baseplay.local')->get()->getRowArray();

        if (!$type || !$athlete) {
            return;
        }

        $exists = $this->db->table('documents')->where('document_type_id', $type['id'])->where('athlete_id', $athlete['id'])->get()->getRowArray();
        if ($exists) {
            return;
        }

        $this->db->table('documents')->insert([
            'document_type_id' => $type['id'],
            'athlete_id' => $athlete['id'],
            'team_id' => null,
            'file_path' => 'documents/sample.pdf',
            'original_name' => 'documento-exemplo.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 12345,
            'issued_at' => date('Y-m-d'),
            'expires_at' => null,
            'uploaded_by' => $user['id'] ?? null,
            'notes' => 'Documento de exemplo (arquivo não existe).',
            'status' => 'active',
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ]);
    }
}