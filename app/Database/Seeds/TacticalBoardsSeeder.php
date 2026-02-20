<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class TacticalBoardsSeeder extends Seeder
{
    public function run()
    {
        $team = $this->db->table('teams')->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getRowArray();
        $category = $team
            ? $this->db->table('categories')->where('team_id', $team['id'])->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getRowArray()
            : null;
        $user = $this->db->table('users')->orderBy('id', 'ASC')->get()->getRowArray();

        if (!$team || !$category || !$user) {
            return;
        }

        $existing = $this->db->table('tactical_boards')->where('title', 'Prancheta exemplo 4-3-3')->get()->getRowArray();
        if ($existing) {
            return;
        }

        $now = Time::now()->toDateTimeString();
        $this->db->table('tactical_boards')->insert([
            'team_id' => $team['id'],
            'category_id' => $category['id'],
            'title' => 'Prancheta exemplo 4-3-3',
            'description' => 'Exemplo inicial com jogadores e cones.',
            'created_by' => $user['id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $boardId = (int) $this->db->insertID();
        if ($boardId <= 0) {
            return;
        }

        $items = [];
        $positions = [
            [8, 50], [22, 20], [22, 38], [22, 62], [22, 80],
            [42, 25], [42, 50], [42, 75],
            [65, 30], [65, 50], [65, 70],
        ];
        foreach ($positions as $index => [$x, $y]) {
            $items[] = [
                'id' => 'p' . ($index + 1),
                'type' => 'player',
                'x' => $x,
                'y' => $y,
                'number' => $index + 1,
                'label' => '',
                'color' => 'wine',
                'size' => 44,
            ];
        }

        for ($i = 1; $i <= 6; $i++) {
            $items[] = [
                'id' => 'c' . $i,
                'type' => 'cone',
                'x' => 20 + ($i * 10),
                'y' => 15 + (($i % 3) * 20),
                'number' => null,
                'label' => '',
                'color' => 'wine',
                'size' => 26,
            ];
        }

        $state = [
            'field' => ['background' => 'soccer_field_v1', 'aspectRatio' => 1.6],
            'items' => $items,
            'meta' => ['notes' => 'Exemplo seedado.', 'formation' => '4-3-3'],
        ];

        $this->db->table('tactical_board_states')->insert([
            'tactical_board_id' => $boardId,
            'state_json' => json_encode($state, JSON_UNESCAPED_UNICODE),
            'version' => 1,
            'created_by' => $user['id'],
            'created_at' => $now,
        ]);
    }
}

