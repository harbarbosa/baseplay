<?php
$current = trim(service('uri')->getPath(), '/');
$isActive = function (string $path) use ($current): bool {
    $path = trim($path, '/');
    if ($path === '') {
        return $current === '';
    }

    return $current === $path || str_starts_with($current, $path . '/');
};

$alertsUnread = 0;
if (has_permission('alerts.view')) {
    $alertsUnread = (new \App\Services\AlertService())->unreadCount();
}

$pendingCount = 0;
?>
<div class="bp-sidebar-overlay" id="bp-sidebar-overlay"></div>
<aside class="bp-sidebar" id="bp-sidebar">
    <div class="bp-sidebar-header">
        <div class="bp-brand">
            <img src="<?= base_url('assets/images/baseplay-logo.png') ?>" alt="BasePlay" class="brand-logo">
            <div class="bp-brand-text">
                <strong>BasePlay</strong>
                <span>MicroSaaS</span>
            </div>
        </div>
        <button type="button" class="bp-icon-btn bp-sidebar-close" id="bp-sidebar-close" aria-label="Fechar menu">✕</button>
    </div>

    <nav class="bp-nav">
        <div class="bp-nav-section">Visao Geral</div>
        <?php if (has_permission('dashboard.view')): ?>
            <a href="<?= base_url('/') ?>" class="bp-nav-link menu-item dashboard <?= $isActive('') ? 'active' : '' ?>"><span class="menu-icon"></span>Dashboard</a>
        <?php endif; ?>
        <?php if (has_permission('alerts.view')): ?>
            <a href="<?= base_url('/pending-center') ?>" class="bp-nav-link menu-item pending-center <?= $isActive('pending-center') ? 'active' : '' ?>">
                <span class="menu-icon"></span>Central de pendencias
                <span class="bp-badge bp-badge-info" style="margin-left:auto;"><?= (int) $pendingCount ?></span>
            </a>
            <a href="<?= base_url('/alerts') ?>" class="bp-nav-link menu-item alerts <?= $isActive('alerts') ? 'active' : '' ?>">
                <span class="menu-icon"></span>Alertas
                <?php if ($alertsUnread > 0): ?>
                    <span class="bp-badge bp-badge-danger" style="margin-left:auto;"><?= esc($alertsUnread) ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <div class="bp-nav-section">Clube</div>
        <?php if (has_permission('teams.view')): ?>
            <a href="<?= base_url('/teams') ?>" class="bp-nav-link menu-item teams <?= $isActive('teams') ? 'active' : '' ?>"><span class="menu-icon"></span>Equipes e categorias</a>
        <?php endif; ?>
        <?php if (has_permission('admin.access')): ?>
            <a href="<?= base_url('/admin/users') ?>" class="bp-nav-link menu-item admin-users <?= $isActive('admin/users') ? 'active' : '' ?>"><span class="menu-icon"></span>Usuarios</a>
            <a href="<?= base_url('/admin/roles') ?>" class="bp-nav-link menu-item admin-roles <?= $isActive('admin/roles') ? 'active' : '' ?>"><span class="menu-icon"></span>Papeis</a>
        <?php endif; ?>

        <div class="bp-nav-section">Elenco</div>
        <?php if (has_permission('athletes.view')): ?>
            <a href="<?= base_url('/athletes') ?>" class="bp-nav-link menu-item athletes <?= $isActive('athletes') ? 'active' : '' ?>"><span class="menu-icon"></span>Atletas</a>
        <?php endif; ?>
        <?php if (has_permission('guardians.view')): ?>
            <a href="<?= base_url('/guardians') ?>" class="bp-nav-link menu-item guardians <?= $isActive('guardians') ? 'active' : '' ?>"><span class="menu-icon"></span>Responsaveis</a>
        <?php endif; ?>
        <?php if (has_permission('documents.view')): ?>
            <a href="<?= base_url('/documents') ?>" class="bp-nav-link menu-item documents <?= $isActive('documents') ? 'active' : '' ?>"><span class="menu-icon"></span>Documentos</a>
            <div class="bp-subnav">
                <a href="<?= base_url('/documents/overview') ?>" class="<?= $isActive('documents/overview') ? 'active' : '' ?>">Visao geral</a>
            </div>
        <?php endif; ?>
        <?php if (has_permission('document_types.manage')): ?>
            <a href="<?= base_url('/document-types') ?>" class="bp-nav-link menu-item document-types <?= $isActive('document-types') ? 'active' : '' ?>"><span class="menu-icon"></span>Tipos de documento</a>
        <?php endif; ?>

        <div class="bp-nav-section">Operacao</div>
        <?php if (has_permission('events.view')): ?>
            <a href="<?= base_url('/events') ?>" class="bp-nav-link menu-item events <?= $isActive('events') ? 'active' : '' ?>"><span class="menu-icon"></span>Agenda</a>
        <?php endif; ?>
        <?php if (has_permission('attendance.manage') || has_permission('reports.view')): ?>
            <a href="<?= base_url('/reports/attendance') ?>" class="bp-nav-link menu-item reports-attendance <?= $isActive('reports/attendance') ? 'active' : '' ?>"><span class="menu-icon"></span>Presenca</a>
        <?php endif; ?>
        <?php if (has_permission('notices.view')): ?>
            <a href="<?= base_url('/notices') ?>" class="bp-nav-link menu-item notices <?= $isActive('notices') ? 'active' : '' ?>"><span class="menu-icon"></span>Avisos</a>
        <?php endif; ?>

        <div class="bp-nav-section">Tecnico</div>
        <?php if (has_permission('exercises.view')): ?>
            <a href="<?= base_url('/exercises') ?>" class="bp-nav-link menu-item exercises <?= $isActive('exercises') ? 'active' : '' ?>"><span class="menu-icon"></span>Exercicios</a>
        <?php endif; ?>
        <?php if (has_permission('training_plans.view')): ?>
            <a href="<?= base_url('/training-plans') ?>" class="bp-nav-link menu-item training-plans <?= $isActive('training-plans') ? 'active' : '' ?>"><span class="menu-icon"></span>Planos de treino</a>
        <?php endif; ?>
        <?php if (has_permission('training_sessions.view')): ?>
            <a href="<?= base_url('/training-sessions') ?>" class="bp-nav-link menu-item training-sessions <?= $isActive('training-sessions') ? 'active' : '' ?>"><span class="menu-icon"></span>Sessoes realizadas</a>
        <?php endif; ?>
        <?php if (has_permission('matches.view')): ?>
            <a href="<?= base_url('/matches') ?>" class="bp-nav-link menu-item matches <?= $isActive('matches') ? 'active' : '' ?>"><span class="menu-icon"></span>Jogos</a>
        <?php endif; ?>
        <?php if (has_permission('tactical_board.view') || has_permission('tactical_boards.view')): ?>
            <a href="<?= base_url('/tactical-boards') ?>" class="bp-nav-link menu-item tactical-boards <?= $isActive('tactical-boards') ? 'active' : '' ?>"><span class="menu-icon"></span>Quadro tatico</a>
        <?php endif; ?>

        <div class="bp-nav-section">Relatorios</div>
        <?php if (has_permission('reports.view')): ?>
            <a href="<?= base_url('/reports/attendance') ?>" class="bp-nav-link menu-item reports-attendance <?= $isActive('reports/attendance') ? 'active' : '' ?>"><span class="menu-icon"></span>Presenca</a>
            <a href="<?= base_url('/reports/trainings') ?>" class="bp-nav-link menu-item reports-trainings <?= $isActive('reports/trainings') ? 'active' : '' ?>"><span class="menu-icon"></span>Treinos</a>
            <a href="<?= base_url('/reports/matches') ?>" class="bp-nav-link menu-item reports-matches <?= $isActive('reports/matches') ? 'active' : '' ?>"><span class="menu-icon"></span>Jogos</a>
            <a href="<?= base_url('/reports/documents') ?>" class="bp-nav-link menu-item reports-documents <?= $isActive('reports/documents') ? 'active' : '' ?>"><span class="menu-icon"></span>Documentos</a>
            <a href="<?= base_url('/reports/athlete/0') ?>" class="bp-nav-link menu-item reports-athlete <?= $isActive('reports/athlete') ? 'active' : '' ?>"><span class="menu-icon"></span>Atleta</a>
        <?php endif; ?>
    </nav>
</aside>
