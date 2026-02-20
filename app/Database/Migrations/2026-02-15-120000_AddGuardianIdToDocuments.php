<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGuardianIdToDocuments extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('guardian_id', 'documents')) {
            $this->forge->addColumn('documents', [
                'guardian_id' => [
                    'type' => 'BIGINT',
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'team_id',
                ],
            ]);
            $this->db->query('CREATE INDEX `idx_documents_guardian_id` ON `documents` (`guardian_id`)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('guardian_id', 'documents')) {
            try {
                $this->db->query('DROP INDEX `idx_documents_guardian_id` ON `documents`');
            } catch (\Throwable $e) {
                // ignore if index does not exist
            }
            $this->forge->dropColumn('documents', 'guardian_id');
        }
    }
}
