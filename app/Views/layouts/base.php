<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'BASEPLAY') ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/baseplay-theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/baseplay-ui.css') ?>">
</head>
<?php $team = current_team(); ?>
<?php
    $teamPrimary = $team['primary_color'] ?? '#7A1126';
    $teamSecondary = $team['secondary_color'] ?? '#F4D6DB';
    $teamLogo = !empty($team['logo_path']) ? base_url($team['logo_path']) : '';
?>
<body class="bp-body<?= $team ? ' bp-team-theme' : '' ?>" style="<?= $team ? '--team-primary:' . esc($teamPrimary) . '; --team-secondary:' . esc($teamSecondary) . '; --team-logo:url(' . esc($teamLogo) . '); --primary:' . esc($teamPrimary) . '; --primary-hover:' . esc($teamSecondary) . ';' : '' ?>">
<div class="bp-layout">
    <?= $this->include('partials/sidebar') ?>
    <div class="bp-main">
        <?= $this->include('partials/header') ?>
        <main class="bp-container">
            <?= $this->include('partials/flash') ?>
            <?= $this->renderSection('content') ?>
            <?= $this->include('partials/footer') ?>
        </main>
    </div>
</div>
<script>
(() => {
    const body = document.body;
    const toggle = document.getElementById('bp-sidebar-toggle');
    const close = document.getElementById('bp-sidebar-close');
    const overlay = document.getElementById('bp-sidebar-overlay');

    const closeSidebar = () => body.classList.remove('bp-sidebar-open');
    const openSidebar = () => body.classList.add('bp-sidebar-open');

    toggle?.addEventListener('click', () => {
        body.classList.contains('bp-sidebar-open') ? closeSidebar() : openSidebar();
    });
    close?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    window.bpToast = (type, message, timeout = 3200) => {
        const container = document.getElementById('bp-toast-container') || (() => {
            const el = document.createElement('div');
            el.id = 'bp-toast-container';
            el.className = 'bp-toast-container';
            document.body.appendChild(el);
            return el;
        })();

        const toast = document.createElement('div');
        toast.className = `bp-toast bp-toast-${type || 'success'}`;
        toast.textContent = message || '';
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('bp-toast-hide');
            setTimeout(() => toast.remove(), 300);
        }, timeout);
    };
})();
</script>
</body>
</html>
