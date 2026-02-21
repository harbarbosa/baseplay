<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="bp-card">
    <div class="bp-card-header">
        <h1 style="margin:0;">Novo papel</h1>
        <div class="bp-text-muted">Defina nome, descrição e permissoes.</div>
    </div>
    <div class="bp-card-body">
        <form method="post" action="<?= base_url('/admin/roles') ?>">
            <?= csrf_field() ?>
            <div style="display:grid; gap:16px; max-width:640px;">
                <div>
                    <label for="name">Nome</label>
                    <input id="name" name="name" type="text" class="bp-input" value="<?= esc(old('name')) ?>" required>
                </div>
                <div>
                    <label for="description">Descrição</label>
                    <input id="description" name="description" type="text" class="bp-input" value="<?= esc(old('description')) ?>">
                </div>
                <?php if (!empty($teams)): ?>
                    <?php if (!empty($showTeamSelect)): ?>
                        <div>
                            <label for="team_id">Equipe</label>
                            <select id="team_id" name="team_id" class="bp-select">
                                <option value="">Sistema (todas as equipes)</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?= esc($team['id']) ?>" <?= (string) ($selectedTeamId ?? '') === (string) $team['id'] ? 'selected' : '' ?>>
                                        <?= esc($team['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div>
                            <label>Equipe</label>
                            <input type="text" class="bp-input" value="<?= esc($teams[0]['name'] ?? 'Equipe') ?>" disabled>
                            <input type="hidden" name="team_id" value="<?= esc($selectedTeamId) ?>">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div style="margin-top:16px;">
                <label>Permissoes</label>
                <?php
                $groupLabels = [
                    'athletes' => 'Atletas',
                    'teams' => 'Equipes',
                    'categories' => 'Categorias',
                    'guardians' => 'Responsáveis',
                    'events' => 'Eventos',
                    'callups' => 'Convocações',
                    'attendance' => 'Presença',
                    'notices' => 'Avisos',
                    'alerts' => 'Alertas',
                    'documents' => 'Documentos',
                    'document_types' => 'Tipos de documento',
                    'exercises' => 'Exercícios',
                    'training_plans' => 'Planos de treino',
                    'training_sessions' => 'Sessões realizadas',
                    'matches' => 'Jogos',
                    'match_stats' => 'Estatisticas de jogo',
                    'match_lineup' => 'Escalação',
                    'match_reports' => 'Relatórios de jogo',
                    'tactical_boards' => 'Quadro tático',
                    'tactical_sequences' => 'Sequencias taticas',
                    'reports' => 'Relatórios',
                    'dashboard' => 'Dashboard',
                    'profile' => 'Perfil',
                    'admin' => 'Administração',
                    'users' => 'Usuários',
                    'roles' => 'Papéis',
                    'settings' => 'Configurações',
                    'invitations' => 'Convocações',
                ];
                $groupAliases = [
                    'tactical_board' => 'tactical_boards',
                    'tactical_sequence' => 'tactical_sequences',
                ];
                $actionLabels = [
                    'view' => 'Visualiza',
                    'create' => 'Cria',
                    'update' => 'Atualiza',
                    'delete' => 'Deleta',
                    'manage' => 'Gerencia',
                    'upload' => 'Envia',
                    'confirm' => 'Confirma',
                    'publish' => 'Publica',
                    'export' => 'Exporta',
                ];
                $actionOrder = array_keys($actionLabels);
                $grouped = [];
                foreach ($permissions as $permission) {
                    $name = (string) $permission['name'];
                    $parts = explode('.', $name, 2);
                    $groupKey = $parts[0] ?? $name;
                    if (isset($groupAliases[$groupKey])) {
                        $groupKey = $groupAliases[$groupKey];
                    }
                    $actionKey = $parts[1] ?? '';
                    $grouped[$groupKey][] = [
                        'id' => $permission['id'],
                        'name' => $name,
                        'action' => $actionKey,
                    ];
                }
                ksort($grouped);
                ?>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-top:8px;">
                    <?php foreach ($grouped as $groupKey => $items): ?>
                        <div style="border:1px solid var(--border); border-radius:12px; padding:12px;">
                            <div style="font-weight:600; margin-bottom:8px;"><?= esc($groupLabels[$groupKey] ?? ucfirst($groupKey)) ?></div>
                            <?php
                            usort($items, static function ($a, $b) use ($actionOrder) {
                                $ai = array_search($a['action'], $actionOrder, true);
                                $bi = array_search($b['action'], $actionOrder, true);
                                $ai = $ai === false ? 999 : $ai;
                                $bi = $bi === false ? 999 : $bi;
                                if ($ai === $bi) {
                                    return strcmp($a['action'], $b['action']);
                                }
                                return $ai <=> $bi;
                            });
                            ?>
                            <div style="display:grid; gap:6px;">
                                <?php
                                $byAction = [];
                                foreach ($items as $item) {
                                    $action = $item['action'];
                                    if ($action === '') {
                                        $byAction[] = $item;
                                        continue;
                                    }
                                    if (!isset($byAction[$action])) {
                                        $byAction[$action] = $item;
                                        continue;
                                    }
                                    $existing = $byAction[$action];
                                    $preferCurrent = str_starts_with((string) $item['name'], 'tactical_boards.')
                                        && str_starts_with((string) $existing['name'], 'tactical_board.');
                                    if ($preferCurrent) {
                                        $byAction[$action] = $item;
                                    }
                                }
                                $itemsToRender = [];
                                foreach ($byAction as $entry) {
                                    $itemsToRender[] = $entry;
                                }
                                ?>
                                <?php foreach ($itemsToRender as $item): ?>
                                    <?php $label = $actionLabels[$item['action']] ?? ($item['action'] !== '' ? ucfirst($item['action']) : $item['name']); ?>
                                    <label style="display:flex; gap:8px; align-items:center;">
                                        <input type="checkbox" name="permissions[]" value="<?= esc($item['id']) ?>">
                                        <?= esc($label) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-top:20px;">
                <button type="submit" class="bp-btn-primary">Criar</button>
                <a href="<?= base_url('/admin/roles') ?>" class="bp-btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
