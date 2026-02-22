<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTravelToTrainingSessions extends Migration
{
    public function up()
    {
        $fields = [
            'travel_required' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'general_notes',
            ],
            'travel_event_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'travel_required',
            ],
            'travel_departure_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'travel_event_id',
            ],
            'travel_return_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'travel_departure_datetime',
            ],
            'travel_location' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
                'null' => true,
                'after' => 'travel_return_datetime',
            ],
            'travel_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'travel_location',
            ],
        ];

        $this->forge->addColumn('training_sessions', $fields);
        $this->forge->addKey('travel_event_id');
    }

    public function down()
    {
        $this->forge->dropColumn('training_sessions', [
            'travel_required',
            'travel_event_id',
            'travel_departure_datetime',
            'travel_return_datetime',
            'travel_location',
            'travel_notes',
        ]);
    }
}
