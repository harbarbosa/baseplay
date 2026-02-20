<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class PermissionsSeeder extends Seeder
{
    /**
     * @return list<array{name:string,description:string}>
     */
    public static function definitions(): array
    {
        return [
            ['name' => 'dashboard.view', 'description' => 'Visualizar dashboards'],
            ['name' => 'admin.access', 'description' => 'Acessar área administrativa'],
            ['name' => 'users.manage', 'description' => 'Gerenciar usuários'],
            ['name' => 'roles.manage', 'description' => 'Gerenciar papéis e permissões'],
            ['name' => 'settings.manage', 'description' => 'Gerenciar configurações do sistema'],

            ['name' => 'teams.view', 'description' => 'Visualizar equipes'],
            ['name' => 'teams.create', 'description' => 'Criar equipes'],
            ['name' => 'teams.update', 'description' => 'Atualizar equipes'],
            ['name' => 'teams.delete', 'description' => 'Excluir equipes'],

            ['name' => 'categories.view', 'description' => 'Visualizar categorias'],
            ['name' => 'categories.create', 'description' => 'Criar categorias'],
            ['name' => 'categories.update', 'description' => 'Atualizar categorias'],
            ['name' => 'categories.delete', 'description' => 'Excluir categorias'],

            ['name' => 'athletes.view', 'description' => 'Visualizar atletas'],
            ['name' => 'athletes.create', 'description' => 'Criar atletas'],
            ['name' => 'athletes.update', 'description' => 'Atualizar atletas'],
            ['name' => 'athletes.delete', 'description' => 'Excluir atletas'],
            ['name' => 'profile.view', 'description' => 'Visualizar perfil próprio'],

            ['name' => 'guardians.view', 'description' => 'Visualizar responsáveis'],
            ['name' => 'guardians.create', 'description' => 'Criar responsáveis'],
            ['name' => 'guardians.update', 'description' => 'Atualizar responsáveis'],
            ['name' => 'guardians.delete', 'description' => 'Excluir responsáveis'],

            ['name' => 'events.view', 'description' => 'Visualizar agenda/eventos'],
            ['name' => 'events.create', 'description' => 'Criar eventos'],
            ['name' => 'events.update', 'description' => 'Atualizar eventos'],
            ['name' => 'events.delete', 'description' => 'Excluir eventos'],

            ['name' => 'callups.view', 'description' => 'Visualizar convocações'],
            ['name' => 'callups.create', 'description' => 'Criar convocações'],
            ['name' => 'callups.update', 'description' => 'Atualizar convocações'],
            ['name' => 'callups.delete', 'description' => 'Excluir convocações'],
            ['name' => 'callups.confirm', 'description' => 'Confirmar convocação'],
            ['name' => 'invitations.manage', 'description' => 'Gerenciar convocações (legado)'],

            ['name' => 'attendance.manage', 'description' => 'Gerenciar presença (modo campo)'],

            ['name' => 'notices.view', 'description' => 'Visualizar avisos'],
            ['name' => 'notices.create', 'description' => 'Criar avisos'],
            ['name' => 'notices.update', 'description' => 'Atualizar avisos'],
            ['name' => 'notices.delete', 'description' => 'Excluir avisos'],
            ['name' => 'notices.publish', 'description' => 'Publicar avisos'],

            ['name' => 'alerts.view', 'description' => 'Visualizar alertas'],

            ['name' => 'document_types.manage', 'description' => 'Gerenciar tipos de documento'],
            ['name' => 'documents.view', 'description' => 'Visualizar documentos'],
            ['name' => 'documents.upload', 'description' => 'Enviar documentos'],
            ['name' => 'documents.update', 'description' => 'Atualizar documentos'],
            ['name' => 'documents.delete', 'description' => 'Excluir documentos'],

            ['name' => 'exercises.view', 'description' => 'Visualizar biblioteca de exercícios'],
            ['name' => 'exercises.create', 'description' => 'Criar exercícios'],
            ['name' => 'exercises.update', 'description' => 'Atualizar exercícios'],
            ['name' => 'exercises.delete', 'description' => 'Excluir exercícios'],

            ['name' => 'training_plans.view', 'description' => 'Visualizar planos de treino'],
            ['name' => 'training_plans.create', 'description' => 'Criar planos de treino'],
            ['name' => 'training_plans.update', 'description' => 'Atualizar planos de treino'],
            ['name' => 'training_plans.delete', 'description' => 'Excluir planos de treino'],

            ['name' => 'training_sessions.view', 'description' => 'Visualizar sessões realizadas'],
            ['name' => 'training_sessions.create', 'description' => 'Criar sessões realizadas'],
            ['name' => 'training_sessions.update', 'description' => 'Atualizar sessões realizadas'],
            ['name' => 'training_sessions.delete', 'description' => 'Excluir sessões realizadas'],

            ['name' => 'matches.view', 'description' => 'Visualizar partidas'],
            ['name' => 'matches.create', 'description' => 'Criar partidas'],
            ['name' => 'matches.update', 'description' => 'Atualizar partidas'],
            ['name' => 'matches.delete', 'description' => 'Excluir partidas'],
            ['name' => 'match_stats.manage', 'description' => 'Gerenciar eventos/estatísticas de partidas'],
            ['name' => 'match_lineup.manage', 'description' => 'Gerenciar escalação'],
            ['name' => 'match_reports.manage', 'description' => 'Gerenciar relatórios de partidas'],

            ['name' => 'tactical_boards.view', 'description' => 'Visualizar pranchetas táticas'],
            ['name' => 'tactical_boards.create', 'description' => 'Criar pranchetas táticas'],
            ['name' => 'tactical_boards.update', 'description' => 'Atualizar pranchetas táticas'],
            ['name' => 'tactical_boards.delete', 'description' => 'Excluir pranchetas táticas'],
            ['name' => 'tactical_boards.export', 'description' => 'Exportar pranchetas táticas'],
            ['name' => 'tactical_sequences.manage', 'description' => 'Gerenciar sequências/etapas táticas'],

            // aliases de compatibilidade para código legado
            ['name' => 'tactical_board.view', 'description' => 'Alias legado: visualizar pranchetas táticas'],
            ['name' => 'tactical_board.create', 'description' => 'Alias legado: criar pranchetas táticas'],
            ['name' => 'tactical_board.update', 'description' => 'Alias legado: atualizar pranchetas táticas'],
            ['name' => 'tactical_board.delete', 'description' => 'Alias legado: excluir pranchetas táticas'],
            ['name' => 'tactical_sequence.manage', 'description' => 'Alias legado: gerenciar sequências táticas'],

            ['name' => 'reports.view', 'description' => 'Visualizar relatórios'],
        ];
    }

    public function run()
    {
        $now = Time::now()->toDateTimeString();

        foreach (self::definitions() as $permission) {
            $exists = $this->db->table('permissions')->where('name', $permission['name'])->get()->getRowArray();
            if ($exists) {
                $this->db->table('permissions')->where('id', (int) $exists['id'])->update([
                    'description' => $permission['description'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            $this->db->table('permissions')->insert([
                'name' => $permission['name'],
                'description' => $permission['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
