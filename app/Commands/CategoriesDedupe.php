<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use Config\Database;

class CategoriesDedupe extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'categories:dedupe';
    protected $description = 'Remove duplicate categories (same team_id + name) by soft-deleting extras.';

    public function run(array $params)
    {
        $db = Database::connect();
        $builder = $db->table('categories');

        $duplicates = $builder
            ->select('team_id, name, COUNT(*) AS total, MIN(id) AS keep_id')
            ->where('deleted_at', null)
            ->groupBy('team_id, name')
            ->having('COUNT(*) >', 1)
            ->get()
            ->getResultArray();

        if (empty($duplicates)) {
            CLI::write('No duplicate categories found.', 'green');
            return;
        }

        $now = Time::now()->toDateTimeString();
        $removed = 0;

        foreach ($duplicates as $dup) {
            $rows = $db->table('categories')
                ->select('id')
                ->where('deleted_at', null)
                ->where('team_id', (int) $dup['team_id'])
                ->where('name', $dup['name'])
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            $keepId = (int) $dup['keep_id'];
            foreach ($rows as $row) {
                $id = (int) $row['id'];
                if ($id === $keepId) {
                    continue;
                }
                $db->table('categories')
                    ->where('id', $id)
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);
                $removed++;
            }
        }

        CLI::write('Duplicate categories removed: ' . $removed, 'yellow');
    }
}
