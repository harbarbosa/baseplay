<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class MatchesSeeder extends Seeder
{
    public function run()
    {
        $event = $this->db->table('events')->where('type', 'MATCH')->orderBy('id', 'DESC')->get()->getRowArray();

        if ($event) {
            $teamId = (int) $event['team_id'];
            $categoryId = (int) $event['category_id'];
            $matchDate = substr($event['start_datetime'], 0, 10);
            $startTime = substr($event['start_datetime'], 11, 5);
            $location = $event['location'] ?? null;
            $eventId = (int) $event['id'];
            $opponent = $event['title'];
        } else {
            $team = $this->db->table('teams')->orderBy('id', 'ASC')->get()->getRowArray();
            $category = $this->db->table('categories')->orderBy('id', 'ASC')->get()->getRowArray();
            if (!$team || !$category) {
                return;
            }
            $teamId = (int) $team['id'];
            $categoryId = (int) $category['id'];
            $matchDate = Time::now()->toDateString();
            $startTime = '10:00';
            $location = 'Estádio principal';
            $eventId = null;
            $opponent = 'Adversário FC';
        }

        $existing = $this->db->table('matches')
            ->where('team_id', $teamId)
            ->where('category_id', $categoryId)
            ->where('match_date', $matchDate)
            ->get()->getRowArray();
        if ($existing) {
            return;
        }

        $user = $this->db->table('users')->orderBy('id', 'ASC')->get()->getRowArray();
        $userId = $user  (int) $user['id'] : 1;
        $now = Time::now()->toDateTimeString();

        $this->db->table('matches')->insert([
            'team_id' => $teamId,
            'category_id' => $categoryId,
            'event_id' => $eventId,
            'opponent_name' => $opponent,
            'competition_name' => 'Copa BasePlay',
            'round_name' => 'Rodada 1',
            'match_date' => $matchDate,
            'start_time' => $startTime,
            'location' => $location,
            'home_away' => 'home',
            'status' => 'completed',
            'score_for' => 2,
            'score_against' => 1,
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $matchId = $this->db->insertID();

        $athletes = $this->db->table('athletes')
            ->where('category_id', $categoryId)
            ->where('deleted_at', null)
            ->limit(10)
            ->get()->getResultArray();

        $index = 0;
        foreach ($athletes as $athlete) {
            $this->db->table('match_callups')->insert([
                'match_id' => $matchId,
                'athlete_id' => (int) $athlete['id'],
                'callup_status' => 'confirmed',
                'is_starting' => $index < 7  1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->db->table('match_lineup_positions')->insert([
                'match_id' => $matchId,
                'athlete_id' => (int) $athlete['id'],
                'lineup_role' => $index < 7  'starting' : 'bench',
                'position_code' => $index === 0  'GK' : null,
                'shirt_number' => $index + 1,
                'order_index' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $index++;
        }

        if (count($athletes) >= 2) {
            $this->db->table('match_events')->insert([
                'match_id' => $matchId,
                'athlete_id' => (int) $athletes[0]['id'],
                'event_type' => 'goal',
                'minute' => 12,
                'notes' => 'Gol de cabeça',
                'created_at' => $now,
            ]);
            $this->db->table('match_events')->insert([
                'match_id' => $matchId,
                'athlete_id' => (int) $athletes[1]['id'],
                'event_type' => 'goal',
                'minute' => 55,
                'notes' => 'Chute de fora da área',
                'created_at' => $now,
            ]);
            $this->db->table('match_events')->insert([
                'match_id' => $matchId,
                'athlete_id' => (int) $athletes[0]['id'],
                'event_type' => 'assist',
                'minute' => 55,
                'related_athlete_id' => (int) $athletes[1]['id'],
                'created_at' => $now,
            ]);
            $this->db->table('match_events')->insert([
                'match_id' => $matchId,
                'athlete_id' => (int) $athletes[0]['id'],
                'event_type' => 'yellow_card',
                'minute' => 70,
                'notes' => 'Falta tática',
                'created_at' => $now,
            ]);
        }

        $this->db->table('match_reports')->insert([
            'match_id' => $matchId,
            'summary' => 'Jogo equilibrado, com boa postura defensiva.',
            'strengths' => 'Pressão pós-perda e transição rápida.',
            'weaknesses' => 'Bola parada defensiva.',
            'next_actions' => 'Treinar marcação em escanteios e saídas rápidas.',
            'coach_notes' => 'Equipe evoluiu no segundo tempo.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->db->table('match_attachments')->insert([
            'match_id' => $matchId,
            'url' => 'https://example.com/video-jogo',
            'original_name' => 'Vídeo do jogo',
            'type' => 'link',
            'created_at' => $now,
        ]);
    }
}