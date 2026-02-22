<?php
$quickAction = $quickAction ?? null;
?>
<header class="bp-topbar">
    <div class="bp-topbar-left">
        <button type="button" class="bp-icon-btn bp-mobile-toggle" id="bp-sidebar-toggle" aria-label="Abrir menu">☰</button>
        <?php $team = current_team(); ?>
        <?php $isAdmin = function_exists('has_permission') && has_permission('admin.access'); ?>
        <?php if (!$isAdmin): ?>
            <?php $teamLogo = !empty($team['logo_path']) ? base_url($team['logo_path']) : base_url('assets/images/baseplay-logo.png'); ?>
            <img src="<?= esc($teamLogo) ?>" alt="Logo" class="bp-topbar-logo">
        <?php endif; ?>
        <div class="bp-topbar-title">
            <strong><?= esc($title ?? 'Painel') ?></strong>
            <?= $this->include('partials/breadcrumbs') ?>
        </div>
    </div>
    <div class="bp-topbar-right">
        <details class="bp-quickmenu">
            <summary class="bp-quickmenu-toggle">Visão geral</summary>
            <div class="bp-quickmenu-menu">
                <?php if (has_permission('athletes.view')): ?>
                    <a href="<?= base_url('/squad') ?>" class="bp-quickmenu-item">Elenco</a>
                <?php endif; ?>
                <?php if (has_permission('documents.view')): ?>
                    <a href="<?= base_url('/documents/overview') ?>" class="bp-quickmenu-item">Documentos</a>
                <?php endif; ?>
                <?php if (has_permission('events.view')): ?>
                    <a href="<?= base_url('/ops') ?>" class="bp-quickmenu-item">Operação</a>
                <?php endif; ?>
            </div>
        </details>
        <?php if (is_array($quickAction) && !empty($quickAction['label'])): ?>
            <a href="<?= esc($quickAction['url'] ?? '#') ?>" class="bp-btn-primary"><?= esc($quickAction['label']) ?></a>
        <?php endif; ?>

        <details class="bp-user">
            <summary class="bp-user-toggle">
                <span class="bp-user-avatar"><?= esc(strtoupper(substr((string) (session('user_name') ?? 'V'), 0, 1))) ?></span>
                <span class="bp-user-name"><?= esc(session('user_name') ?? 'Usuário') ?></span>
                <span class="bp-user-caret"></span>
            </summary>
            <div class="bp-user-menu">
                <a href="<?= base_url('/') ?>" class="bp-user-item">Perfil</a>
                <?php if (has_permission('admin.access') || has_permission('users.manage') || has_permission('roles.manage')): ?>
                    <a href="<?= base_url('/admin/users') ?>" class="bp-user-item">Configurações</a>
                <?php endif; ?>
                <a href="<?= base_url('/logout') ?>" class="bp-user-item">Sair</a>
            </div>
        </details>
    </div>
</header>

