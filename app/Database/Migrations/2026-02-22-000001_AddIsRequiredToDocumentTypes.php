<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsRequiredToDocumentTypes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('document_types', [
            'is_required' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'default_valid_days',
            ],
        ]);

        $db = db_connect();
        if ($db->tableExists('category_required_documents')) {
            $db->query(
                'UPDATE document_types dt
                 SET dt.is_required = 1
                 WHERE EXISTS (
                    SELECT 1 FROM category_required_documents crd
                    WHERE crd.document_type_id = dt.id
                      AND crd.deleted_at IS NULL
                 )'
            );
        }
    }

    public function down()
    {
        $this->forge->dropColumn('document_types', 'is_required');
    }
}
