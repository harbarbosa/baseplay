<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Services\TacticalBoardTemplateService;

class TemplatesSeeder extends Seeder
{
    public function run()
    {
        $service = new TacticalBoardTemplateService();
        $service->seedTemplates($this->templates());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function templates(): array
    {
        $fullField = 'soccer_field_v1';
        $halfBottom = 'soccer_field_half_vertical_down';
        $halfTop = 'soccer_field_half_vertical_up';

        $baseField = fn(string $bg) => [
            'field' => [
                'background' => $bg,
                'aspectRatio' => 1.6,
            ],
            'items' => [],
            'meta' => [
                'notes' => '',
                'formation' => '',
            ],
        ];

        $player = fn(string $id, int $number, float $x, float $y, string $color = 'wine') => [
            'id' => $id,
            'type' => 'player',
            'number' => $number,
            'x' => $x,
            'y' => $y,
            'color' => $color,
        ];

        $cone = fn(string $id, float $x, float $y) => [
            'id' => $id,
            'type' => 'cone',
            'x' => $x,
            'y' => $y,
            'color' => 'wine',
        ];

        $ball = fn(string $id, float $x, float $y) => [
            'id' => $id,
            'type' => 'ball',
            'x' => $x,
            'y' => $y,
            'color' => 'white',
        ];

        $withSteps = function (array $baseItems, array $frames): array {
            $steps = [];
            foreach ($frames as $frame) {
                $items = [];
                foreach ($baseItems as $item) {
                    $copy = $item;
                    if (isset($frame[$item['id']])) {
                        $copy['x'] = $frame[$item['id']][0];
                        $copy['y'] = $frame[$item['id']][1];
                    }
                    $items[] = $copy;
                }
                $steps[] = ['items' => $items];
            }
            return $steps;
        };

        $templates = [];

        // 1) 4-3-3
        $state433 = $baseField($fullField);
        $state433['meta']['formation'] = '4-3-3';
        $state433['items'] = [
            $player('p1', 1, 8, 50),
            $player('p2', 2, 25, 18),
            $player('p3', 3, 25, 38),
            $player('p4', 4, 25, 62),
            $player('p5', 5, 25, 82),
            $player('p6', 6, 50, 30),
            $player('p7', 7, 50, 50),
            $player('p8', 8, 50, 70),
            $player('p9', 9, 75, 25),
            $player('p10', 10, 75, 50),
            $player('p11', 11, 75, 75),
        ];
        $state433['meta']['steps'] = $withSteps($state433['items'], [
            [],
            [
                'p9' => [85, 22],
                'p10' => [83, 50],
                'p11' => [85, 78],
                'p6' => [55, 28],
                'p7' => [55, 50],
                'p8' => [55, 72],
                'p2' => [30, 18],
                'p5' => [30, 82],
            ],
            [
                'p9' => [90, 20],
                'p10' => [88, 50],
                'p11' => [90, 80],
                'p6' => [60, 30],
                'p7' => [60, 52],
                'p8' => [60, 74],
            ],
        ]);

        $templates[] = [
            'title' => 'Formação 4-3-3',
            'description' => 'Distribuição clássica com três atacantes.',
            'field_type' => 'full',
            'tags' => 'formacao,4-3-3',
            'template_json' => json_encode($state433, JSON_UNESCAPED_UNICODE),
        ];

        // 2) 4-4-2
        $state442 = $baseField($fullField);
        $state442['meta']['formation'] = '4-4-2';
        $state442['items'] = [
            $player('p1', 1, 8, 50),
            $player('p2', 2, 25, 20),
            $player('p3', 3, 25, 40),
            $player('p4', 4, 25, 60),
            $player('p5', 5, 25, 80),
            $player('p6', 6, 50, 20),
            $player('p7', 7, 50, 40),
            $player('p8', 8, 50, 60),
            $player('p9', 9, 50, 80),
            $player('p10', 10, 75, 40),
            $player('p11', 11, 75, 60),
        ];
        $state442['meta']['steps'] = $withSteps($state442['items'], [
            [],
            [
                'p10' => [83, 42],
                'p11' => [83, 58],
                'p6' => [55, 25],
                'p7' => [55, 42],
                'p8' => [55, 58],
                'p9' => [55, 75],
            ],
            [
                'p10' => [88, 40],
                'p11' => [88, 60],
                'p6' => [60, 28],
                'p7' => [60, 45],
                'p8' => [60, 55],
                'p9' => [60, 72],
            ],
        ]);

        $templates[] = [
            'title' => 'Formação 4-4-2',
            'description' => 'Linha de quatro no meio e dois atacantes.',
            'field_type' => 'full',
            'tags' => 'formacao,4-4-2',
            'template_json' => json_encode($state442, JSON_UNESCAPED_UNICODE),
        ];

        // 3) 3-5-2
        $state352 = $baseField($fullField);
        $state352['meta']['formation'] = '3-5-2';
        $state352['items'] = [
            $player('p1', 1, 8, 50),
            $player('p2', 2, 25, 25),
            $player('p3', 3, 25, 50),
            $player('p4', 4, 25, 75),
            $player('p5', 5, 50, 20),
            $player('p6', 6, 50, 35),
            $player('p7', 7, 50, 50),
            $player('p8', 8, 50, 65),
            $player('p9', 9, 50, 80),
            $player('p10', 10, 75, 40),
            $player('p11', 11, 75, 60),
        ];
        $state352['meta']['steps'] = $withSteps($state352['items'], [
            [],
            [
                'p10' => [82, 40],
                'p11' => [82, 60],
                'p5' => [55, 20],
                'p9' => [55, 78],
                'p2' => [28, 28],
                'p4' => [28, 72],
            ],
            [
                'p10' => [88, 42],
                'p11' => [88, 58],
                'p5' => [60, 22],
                'p9' => [60, 76],
            ],
        ]);

        $templates[] = [
            'title' => 'Formação 3-5-2',
            'description' => 'Controle do meio-campo com dois atacantes.',
            'field_type' => 'full',
            'tags' => 'formacao,3-5-2',
            'template_json' => json_encode($state352, JSON_UNESCAPED_UNICODE),
        ];

        // 4) Finalização
        $final = $baseField($halfBottom);
        $final['meta']['formation'] = 'finalizacao';
        $final['items'] = [
            $ball('b1', 55, 55),
            $cone('c1', 35, 40),
            $cone('c2', 45, 35),
            $cone('c3', 55, 30),
            $cone('c4', 65, 35),
            $cone('c5', 75, 40),
        ];
        $final['meta']['steps'] = $withSteps($final['items'], [
            [],
            [
                'b1' => [62, 40],
            ],
            [
                'b1' => [70, 25],
            ],
        ]);
        $templates[] = [
            'title' => 'Treino de finalização',
            'description' => 'Cones e bola para chutes rápidos.',
            'field_type' => 'half_bottom_goal',
            'tags' => 'treino,finalizacao,bola',
            'template_json' => json_encode($final, JSON_UNESCAPED_UNICODE),
        ];

        // 5) Saída de bola
        $saida = $baseField($halfBottom);
        $saida['meta']['formation'] = 'saida-de-bola';
        $saida['items'] = [
            $player('p1', 1, 12, 50),
            $player('p2', 2, 25, 30),
            $player('p3', 3, 25, 50),
            $player('p4', 4, 25, 70),
            $player('p5', 5, 40, 40),
            $player('p6', 6, 40, 60),
            $ball('b1', 18, 50),
        ];
        $saida['meta']['steps'] = $withSteps($saida['items'], [
            [],
            [
                'b1' => [28, 50],
                'p5' => [45, 40],
                'p6' => [45, 60],
            ],
            [
                'b1' => [45, 50],
                'p5' => [50, 38],
                'p6' => [50, 62],
            ],
        ]);
        $templates[] = [
            'title' => 'Saída de bola',
            'description' => 'Posicionamento base para iniciar jogadas.',
            'field_type' => 'half_bottom_goal',
            'tags' => 'treino,saida-de-bola,construcao',
            'template_json' => json_encode($saida, JSON_UNESCAPED_UNICODE),
        ];

        // 6) Bola parada (escanteio ofensivo)
        $corner = $baseField($halfTop);
        $corner['meta']['formation'] = 'escanteio';
        $corner['items'] = [
            $ball('b1', 10, 10),
            $player('p9', 9, 55, 25),
            $player('p10', 10, 60, 35),
            $player('p11', 11, 45, 35),
            $player('p5', 5, 50, 50),
            $player('p6', 6, 40, 50),
        ];
        $corner['meta']['steps'] = $withSteps($corner['items'], [
            [],
            [
                'b1' => [30, 22],
                'p9' => [58, 28],
                'p10' => [64, 38],
                'p11' => [48, 38],
            ],
            [
                'b1' => [45, 30],
                'p9' => [60, 30],
                'p10' => [66, 40],
                'p11' => [50, 40],
                'p5' => [52, 52],
                'p6' => [42, 52],
            ],
        ]);
        $templates[] = [
            'title' => 'Bola parada (escanteio ofensivo)',
            'description' => 'Organização ofensiva para escanteio.',
            'field_type' => 'half_top_goal',
            'tags' => 'bola-parada,escanteio,ofensivo',
            'template_json' => json_encode($corner, JSON_UNESCAPED_UNICODE),
        ];

        return $templates;
    }
}
