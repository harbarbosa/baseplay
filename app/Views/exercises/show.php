<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:900px;">
    <h1><?= esc($exercise['title']) ?></h1>

    <?php
    $ageLabels = [
        'u10' => 'Sub-10',
        'u11' => 'Sub-11',
        'u12' => 'Sub-12',
        'u13' => 'Sub-13',
        'u14' => 'Sub-14',
        'u15' => 'Sub-15',
        'u16' => 'Sub-16',
        'u17' => 'Sub-17',
        'u18' => 'Sub-18',
        'u19' => 'Sub-19',
        'u20' => 'Sub-20',
        'all' => 'Todas',
    ];
    $intensityLabels = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
    ];
    ?>

    <p><strong>Objetivo:</strong> <?= esc($exercise['objective'] ?? '-') ?></p>
    <p><strong>Faixa etária:</strong> <?= esc($ageLabels[$exercise['age_group']] ?? $exercise['age_group']) ?></p>
    <p><strong>Intensidade:</strong> <?= esc($intensityLabels[$exercise['intensity']] ?? $exercise['intensity']) ?></p>
    <p><strong>Duração:</strong> <?= esc($exercise['duration_min'] ?? '-') ?> min</p>
    <p><strong>Jogadores:</strong> <?= esc($exercise['players_min'] ?? '-') ?> / <?= esc($exercise['players_max'] ?? '-') ?></p>
    <p><strong>Materiais:</strong> <?= esc($exercise['materials'] ?? '-') ?></p>
    <p><strong>Vídeo:</strong> <?= esc($exercise['video_url'] ?? '-') ?></p>

    <div style="margin-top:16px; white-space:pre-wrap;">
        <?= esc($exercise['description'] ?? '') ?>
    </div>

    <div style="margin-top:16px;">
        <?php if (has_permission('exercises.update')): ?>
            <a href="<?= base_url('/exercises/' . $exercise['id'] . '/edit') ?>" class="button secondary">Editar</a>
        <?php endif; ?>
        <a href="<?= base_url('/exercises') ?>" class="button secondary">Voltar</a>
    </div>
</div>
<?= $this->endSection() ?>
