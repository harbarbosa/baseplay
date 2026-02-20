<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class NoticesSeeder extends Seeder
{
    public function run()
    {
        $admin = $this->db->table('users')->where('email', 'admin@baseplay.local')->get()->getRowArray();
        $team = $this->db->table('teams')->where('name', 'BasePlay Academy')->get()->getRowArray();
        $category = null;
        if ($team) {
            $category = $this->db->table('categories')->where('team_id', $team['id'])->get()->getRowArray();
        }

        $now = Time::now()->toDateTimeString();

        $notices = [
            [
                'title' => 'Boas-vindas ao BASEPLAY',
                'message' => "Bem-vindo ao módulo de comunicação. Este é um aviso geral para todos os perfis.",
                'priority' => 'normal',
                'status' => 'published',
                'team_id' => null,
                'category_id' => null,
            ],
            [
                'title' => 'Reunião obrigatória - Comissão técnica',
                'message' => "Aviso urgente: reunião com a comissão técnica amanhã às 19h.\nLocal: sala de reuniões.",
                'priority' => 'urgent',
                'status' => 'published',
                'team_id' => $team['id'] ?? null,
                'category_id' => $category['id'] ?? null,
            ],
        ];

        foreach ($notices as $notice) {
            $exists = $this->db->table('notices')->where('title', $notice['title'])->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $this->db->table('notices')->insert([
                'team_id' => $notice['team_id'],
                'category_id' => $notice['category_id'],
                'title' => $notice['title'],
                'message' => $notice['message'],
                'created_by' => $admin['id'] ?? null,
                'priority' => $notice['priority'],
                'publish_at' => $now,
                'status' => $notice['status'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $first = $this->db->table('notices')->orderBy('id', 'ASC')->get()->getRowArray();
        if ($first && $admin) {
            $existsRead = $this->db->table('notice_reads')
                ->where('notice_id', $first['id'])
                ->where('user_id', $admin['id'])
                ->get()->getRowArray();
            if (!$existsRead) {
                $this->db->table('notice_reads')->insert([
                    'notice_id' => $first['id'],
                    'user_id' => $admin['id'],
                    'read_at' => $now,
                ]);
            }
        }
    }
}