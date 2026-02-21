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
            ['name' => 'admin.access', 'description' => 'Acessar Ã¡rea administrativa'],
            ['name' => 'users.manage', 'description' => 'Gerenciar usuÃ¡rios'],
            ['name' => 'roles.manage', 'description' => 'Gerenciar papÃ©is e permissÃµes'],
            ['name' => 'settings.manage', 'description' => 'Gerenciar configuraÃ§Ãµes do sistema'],

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
            ['name' => 'profile.view', 'description' => 'Visualizar perfil prÃ³prio'],

            ['name' => 'guardians.view', 'description' => 'Visualizar responsÃ¡veis'],
            ['name' => 'guardians.create', 'description' => 'Criar responsÃ¡veis'],
            ['name' => 'guardians.update', 'description' => 'Atualizar responsÃ¡veis'],
            ['name' => 'guardians.delete', 'description' => 'Excluir responsÃ¡veis'],

            ['name' => 'events.view', 'description' => 'Visualizar agenda/eventos'],
            ['name' => 'events.create', 'description' => 'Criar eventos'],
            ['name' => 'events.update', 'description' => 'Atualizar eventos'],
            ['name' => 'events.delete', 'description' => 'Excluir eventos'],

            ['name' => 'callups.view', 'description' => 'Visualizar convocaÃ§Ãµes'],
            ['name' => 'callups.create', 'description' => 'Criar convocaÃ§Ãµes'],
            ['name' => 'callups.update', 'description' => 'Atualizar convocaÃ§Ãµes'],
            ['name' => 'callups.delete', 'description' => 'Excluir convocaÃ§Ãµes'],
            ['name' => 'callups.confirm', 'description' => 'Confirmar convocaÃ§Ã£o'],
            ['name' => 'invitations.manage', 'description' => 'Gerenciar convocaÃ§Ãµes (legado)'],

            ['name' => 'attendance.manage', 'description' => 'Gerenciar presenÃ§a (modo campo)'],

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

            ['name' => 'exercises.view', 'description' => 'Visualizar biblioteca de exercÃ­cios'],
            ['name' => 'exercises.create', 'description' => 'Criar exercÃ­cios'],
            ['name' => 'exercises.update', 'description' => 'Atualizar exercÃ­cios'],
            ['name' => 'exercises.delete', 'description' => 'Excluir exercÃ­cios'],

            ['name' => 'training_plans.view', 'description' => 'Visualizar planos de treino'],
            ['name' => 'training_plans.create', 'description' => 'Criar planos de treino'],
            ['name' => 'training_plans.update', 'description' => 'Atualizar planos de treino'],
            ['name' => 'training_plans.delete', 'description' => 'Excluir planos de treino'],

            ['name' => 'training_sessions.view', 'description' => 'Visualizar sessÃµes realizadas'],
            ['name' => 'training_sessions.create', 'description' => 'Criar sessÃµes realizadas'],
            ['name' => 'training_sessions.update', 'description' => 'Atualizar sessÃµes realizadas'],
            ['name' => 'training_sessions.delete', 'description' => 'Excluir sessÃµes realizadas'],

            ['name' => 'matches.view', 'description' => 'Visualizar partidas'],
            ['name' => 'matches.create', 'description' => 'Criar partidas'],
            ['name' => 'matches.update', 'description' => 'Atualizar partidas'],
            ['name' => 'matches.delete', 'description' => 'Excluir partidas'],
            ['name' => 'match_stats.manage', 'description' => 'Gerenciar eventos/estatÃ­sticas de partidas'],
            ['name' => 'match_lineup.manage', 'description' => 'Gerenciar escalaÃ§Ã£o'],
            ['name' => 'match_reports.manage', 'description' => 'Gerenciar relatÃ³rios de partidas'],

            ['name' => 'tactical_boards.view', 'description' => 'Visualizar pranchetas tÃ¡ticas'],
            ['name' => 'tactical_boards.create', 'description' => 'Criar pranchetas tÃ¡ticas'],
            ['name' => 'tactical_boards.update', 'description' => 'Atualizar pranchetas tÃ¡ticas'],
            ['name' => 'tactical_boards.delete', 'description' => 'Excluir pranchetas tÃ¡ticas'],
            ['name' => 'tactical_boards.export', 'description' => 'Exportar pranchetas tÃ¡ticas'],
            ['name' => 'tactical_sequences.manage', 'description' => 'Gerenciar sequÃªncias/etapas tÃ¡ticas'],
            ['name' => 'templates.view', 'description' => 'Visualizar modelos de prancheta'],
            ['name' => 'templates.manage', 'description' => 'Gerenciar modelos de prancheta'],
            ['name' => 'templates.view', 'description' => 'Visualizar modelos de prancheta'],
            ['name' => 'templates.manage', 'description' => 'Gerenciar modelos de prancheta'],

            // aliases de compatibilidade para cÃ³digo legado
            ['name' => 'tactical_board.view', 'description' => 'Alias legado: visualizar pranchetas tÃ¡ticas'],
            ['name' => 'tactical_board.create', 'description' => 'Alias legado: criar pranchetas tÃ¡ticas'],
            ['name' => 'tactical_board.update', 'description' => 'Alias legado: atualizar pranchetas tÃ¡ticas'],
            ['name' => 'tactical_board.delete', 'description' => 'Alias legado: excluir pranchetas tÃ¡ticas'],
            ['name' => 'tactical_sequence.manage', 'description' => 'Alias legado: gerenciar sequÃªncias tÃ¡ticas'],

            ['name' => 'reports.view', 'description' => 'Visualizar relatÃ³rios'],
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
