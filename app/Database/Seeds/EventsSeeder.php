<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class EventsSeeder extends Seeder
{
    public function run()
    {
        $category = $this->db->table('categories')->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getRowArray();
        if (!$category) {
            return;
        }

        $teamId = $category['team_id'];
        $now = Time::now();

        $events = [
            [
                'team_id' => $teamId,
                'category_id' => $category['id'],
                'type' => 'TRAINING',
                'title' => 'Treino Técnico',
                'description' => 'Treino de fundamentos e posse de bola.',
                'start_datetime' => $now->addDays(1)->setTime(18, 0)->toDateTimeString(),
                'end_datetime' => $now->addDays(1)->setTime(19, 30)->toDateTimeString(),
                'location' => 'Campo 1',
                'status' => 'scheduled',
            ],
            [
                'team_id' => $teamId,
                'category_id' => $category['id'],
                'type' => 'TRAINING',
                'title' => 'Treino Tático',
                'description' => 'Treino de organização defensiva.',
                'start_datetime' => $now->addDays(3)->setTime(18, 0)->toDateTimeString(),
                'end_datetime' => $now->addDays(3)->setTime(19, 30)->toDateTimeString(),
                'location' => 'Campo 2',
                'status' => 'scheduled',
            ],
            [
                'team_id' => $teamId,
                'category_id' => $category['id'],
                'type' => 'MATCH',
                'title' => 'Jogo Amistoso',
                'description' => 'Partida amistosa local.',
                'start_datetime' => $now->addDays(5)->setTime(9, 0)->toDateTimeString(),
                'end_datetime' => $now->addDays(5)->setTime(11, 0)->toDateTimeString(),
                'location' => 'Estádio Municipal',
                'status' => 'scheduled',
            ],
        ];

        foreach ($events as $event) {
            $exists = $this->db->table('events')
                ->where('title', $event['title'])
                ->where('category_id', $event['category_id'])
                ->get()->getRowArray();
            if ($exists) {
                continue;
            }
            $this->db->table('events')->insert(array_merge($event, [
                'created_at' => Time::now()->toDateTimeString(),
                'updated_at' => Time::now()->toDateTimeString(),
            ]));
        }

        $event = $this->db->table('events')->orderBy('id', 'ASC')->get()->getRowArray();
        if (!$event) {
            return;
        }

        $athletes = $this->db->table('athletes')->where('category_id', $event['category_id'])->get()->getResultArray();
        foreach ($athletes as $athlete) {
            $link = $this->db->table('event_participants')
                ->where('event_id', $event['id'])
                ->where('athlete_id', $athlete['id'])
                ->get()->getRowArray();
            if ($link) {
                continue;
            }
            $this->db->table('event_participants')->insert([
                'event_id' => $event['id'],
                'athlete_id' => $athlete['id'],
                'invitation_status' => 'invited',
                'created_at' => Time::now()->toDateTimeString(),
            ]);
        }

        $firstAthlete = $athletes[0] ?? null;
        if ($firstAthlete) {
            $attendance = $this->db->table('attendance')
                ->where('event_id', $event['id'])
                ->where('athlete_id', $firstAthlete['id'])
                ->get()->getRowArray();
            if (!$attendance) {
                $this->db->table('attendance')->insert([
                    'event_id' => $event['id'],
                    'athlete_id' => $firstAthlete['id'],
                    'status' => 'present',
                    'checkin_time' => Time::now()->toDateTimeString(),
                    'created_at' => Time::now()->toDateTimeString(),
                    'updated_at' => Time::now()->toDateTimeString(),
                ]);
            }
        }
    }
}
