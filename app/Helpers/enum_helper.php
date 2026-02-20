<?php

if (!function_exists('enum_label')) {
    function enum_label(string $value, string $group = null): string
    {
        $value = (string) ($value ?? '');
        if ($value === '') {
            return '-';
        }

        $maps = [
            'status' => [
                'active' => 'Ativo',
                'inactive' => 'Inativo',
                'scheduled' => 'Agendado',
                'completed' => 'Concluído',
                'cancelled' => 'Cancelado',
                'draft' => 'Rascunho',
                'ready' => 'Pronto',
                'archived' => 'Arquivado',
                'published' => 'Publicado',
                'expired' => 'Vencido',
            ],
            'attendance' => [
                'present' => 'Presente',
                'late' => 'Atrasado',
                'absent' => 'Faltou',
                'justified' => 'Justificado',
            ],
            'invitation' => [
                'invited' => 'Convidado',
                'pending' => 'Pendente',
                'confirmed' => 'Confirmado',
                'declined' => 'Recusado',
            ],
            'dominant_foot' => [
                'right' => 'Direito',
                'left' => 'Esquerdo',
                'both' => 'Ambos',
            ],
            'home_away' => [
                'home' => 'Casa',
                'away' => 'Fora',
                'neutral' => 'Neutro',
            ],
            'match_event' => [
                'goal' => 'Gol',
                'assist' => 'Assistência',
                'yellow_card' => 'Cartão amarelo',
                'red_card' => 'Cartão vermelho',
                'sub_in' => 'Entrada',
                'sub_out' => 'Saída',
                'injury' => 'Lesão',
                'other' => 'Outro',
            ],
        ];

        if ($group !== null && isset($maps[$group][$value])) {
            return $maps[$group][$value];
        }

        foreach ($maps as $map) {
            if (isset($map[$value])) {
                return $map[$value];
            }
        }

        return ucfirst(str_replace('_', ' ', $value));
    }
}
