<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class RolePermissionsSeeder extends Seeder
{
    /**
     * @return array<string, list<string>>
     */
    public static function matrix(): array
    {
        $all = array_column(PermissionsSeeder::definitions(), 'name');

        $admin = $all;

        $treinador = [
            'dashboard.view',
            'teams.view',
            'categories.view',
            'athletes.view',
            'athletes.update',
            'guardians.view',
            'events.view', 'events.create', 'events.update', 'events.delete',
            'callups.view', 'callups.create', 'callups.update', 'callups.delete',
            'invitations.manage',
            'attendance.manage',
            'notices.view', 'notices.create', 'notices.update', 'notices.delete', 'notices.publish',
            'alerts.view',
            'documents.view', 'documents.upload', 'documents.update',
            'document_types.manage',
            'exercises.view', 'exercises.create', 'exercises.update', 'exercises.delete',
            'training_plans.view', 'training_plans.create', 'training_plans.update', 'training_plans.delete',
            'training_sessions.view', 'training_sessions.create', 'training_sessions.update', 'training_sessions.delete',
            'matches.view', 'matches.create', 'matches.update', 'matches.delete',
            'match_stats.manage', 'match_lineup.manage', 'match_reports.manage',
            'tactical_boards.view', 'tactical_boards.create', 'tactical_boards.update', 'tactical_boards.delete', 'tactical_boards.export',
            'tactical_sequences.manage',
            'templates.view',
            'tactical_board.view', 'tactical_board.create', 'tactical_board.update', 'tactical_board.delete', 'tactical_sequence.manage',
            'reports.view',
        ];

        $adminEquipe = array_merge($treinador, [
            'athletes.create', 'athletes.delete',
            'guardians.create', 'guardians.update', 'guardians.delete',
            'documents.delete',
            'teams.update',
        ]);

        $auxiliar = [
            'dashboard.view',
            'teams.view',
            'categories.view',
            'athletes.view',
            'guardians.view',
            'events.view',
            'callups.view',
            'attendance.manage',
            'notices.view', 'notices.create',
            'alerts.view',
            'documents.view', 'documents.upload',
            'exercises.view',
            'training_plans.view',
            'training_sessions.view', 'training_sessions.create', 'training_sessions.update',
            'matches.view',
            'match_stats.manage', 'match_lineup.manage',
            'tactical_boards.view', 'tactical_board.view', 'templates.view',
            'reports.view',
        ];

        $atleta = [
            'dashboard.view',
            'profile.view',
            'events.view',
            'callups.view',
            'notices.view',
            'documents.view',
            'tactical_boards.view', 'tactical_board.view', 'templates.view',
            'matches.view',
            'reports.view',
        ];

        $responsavel = [
            'dashboard.view',
            'profile.view',
            'events.view',
            'callups.view', 'callups.confirm',
            'notices.view',
            'documents.view', 'documents.upload',
            'tactical_boards.view', 'tactical_board.view', 'templates.view',
            'matches.view',
            'reports.view',
        ];

        return [
            'admin' => $admin,
            'admin_equipe' => $adminEquipe,
            'treinador' => $treinador,
            'auxiliar' => $auxiliar,
            'atleta' => $atleta,
            'responsavel' => $responsavel,
        ];
    }

    public function run()
    {
        $now = Time::now()->toDateTimeString();

        $roleByName = [];
        foreach ($this->db->table('roles')->get()->getResultArray() as $role) {
            $roleByName[(string) $role['name']] = (int) $role['id'];
        }

        $permissionByName = [];
        foreach ($this->db->table('permissions')->get()->getResultArray() as $permission) {
            $permissionByName[(string) $permission['name']] = (int) $permission['id'];
        }

        foreach (self::matrix() as $roleName => $permissionNames) {
            $roleId = $roleByName[$roleName] ?? null;
            if (!$roleId) {
                continue;
            }

            $this->db->table('role_permissions')->where('role_id', $roleId)->delete();

            foreach (array_unique($permissionNames) as $permissionName) {
                $permissionId = $permissionByName[$permissionName] ?? null;
                if (!$permissionId) {
                    continue;
                }

                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                ]);
            }
        }
    }
}
