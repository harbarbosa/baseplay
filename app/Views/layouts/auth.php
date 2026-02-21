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
<body class="bp-body bp-auth">
<div class="bp-auth-shell">
    <div class="bp-auth-card bp-card">
        <div class="bp-auth-brand">
            <img src="<?= base_url('assets/images/baseplay-logo.png') ?>" alt="BasePlay" class="brand-logo">
            <div>
                <strong>BasePlay</strong>
                <span>MicroSaaS para gestão esportiva</span>
            </div>
        </div>
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('content') ?>
    </div>
</div>
</body>
</html>
