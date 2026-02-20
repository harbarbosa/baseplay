<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoryRequiredDocuments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'document_type_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'is_required' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('category_id');
        $this->forge->addKey('document_type_id');
        $this->forge->addUniqueKey(['category_id', 'document_type_id']);
        $this->forge->createTable('category_required_documents', true);
    }

    public function down()
    {
        $this->forge->dropTable('category_required_documents', true);
    }
}
