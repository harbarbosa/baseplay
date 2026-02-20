<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastActivityIndexes extends Migration
{
    public function up()
    {
        $this->forge->addKey(['athlete_id', 'training_session_id'], false, false, 'idx_tsa_athlete_session');
        $this->forge->processIndexes('training_session_athletes');

        $this->forge->addKey(['session_date', 'category_id'], false, false, 'idx_ts_date_category');
        $this->forge->processIndexes('training_sessions');

        $this->forge->addKey(['athlete_id', 'event_id'], false, false, 'idx_attendance_athlete_event');
        $this->forge->processIndexes('attendance');

        $this->forge->addKey(['type', 'start_datetime'], false, false, 'idx_events_type_start');
        $this->forge->processIndexes('events');

        $this->forge->addKey(['athlete_id', 'match_id'], false, false, 'idx_match_callups_athlete_match');
        $this->forge->processIndexes('match_callups');
    }

    public function down()
    {
        $this->forge->dropKey('training_session_athletes', 'idx_tsa_athlete_session');
        $this->forge->dropKey('training_sessions', 'idx_ts_date_category');
        $this->forge->dropKey('attendance', 'idx_attendance_athlete_event');
        $this->forge->dropKey('events', 'idx_events_type_start');
        $this->forge->dropKey('match_callups', 'idx_match_callups_athlete_match');
    }
}
