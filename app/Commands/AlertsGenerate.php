<?php

namespace App\Commands;

use App\Services\AlertGeneratorService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AlertsGenerate extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'alerts:generate';
    protected $description = 'Generate intelligent system alerts.';

    public function run(array $params)
    {
        $service = new AlertGeneratorService();
        $result = $service->generateAll();

        $total = 0;
        foreach ($result as $key => $count) {
            $total += (int) $count;
            CLI::write($key . ': ' . (int) $count, 'yellow');
        }

        CLI::write('Total alerts generated: ' . $total, 'green');
    }
}