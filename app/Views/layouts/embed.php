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
    <style>
        body {margin:0; background:var(--bg);}
        .bp-embed {min-height:100vh;}
        .bp-embed main {padding:12px;}
        .bp-embed .card {box-shadow:none; border:0;}
    </style>
</head>
<body class="bp-embed">
    <main>
        <?= $this->renderSection('content') ?>
    </main>
</body>
</html>
