<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureUtf8mb4Events extends Migration
{
    public function up()
    {
        $db = db_connect();
        $dbName = $db->database ?? null;
        if ($dbName) {
            $db->query("ALTER DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        $db->query('ALTER TABLE `events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $db->query('ALTER TABLE `matches` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $db->query("UPDATE `events` SET `title` = REPLACE(`title`, CONCAT(CHAR(226),CHAR(128),CHAR(148)), CHAR(8212))");
    }

    public function down()
    {
    }
}
