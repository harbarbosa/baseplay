<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:1000px;">
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

    <div class="bp-ex-header">
        <div>
            <h1><?= esc($exercise['title']) ?></h1>
            <div class="bp-ex-subtitle">
                <?= esc($exercise['objective'] ?? '-') ?>
            </div>
        </div>
        <div class="bp-ex-actions">
            <?php if (has_permission('exercises.update')): ?>
                <a href="<?= base_url('/exercises/' . $exercise['id'] . '/edit') ?>" class="button secondary">Editar</a>
            <?php endif; ?>
            <a href="<?= base_url('/exercises') ?>" class="button secondary">Voltar</a>
        </div>
    </div>

    <div class="bp-ex-grid">
        <div class="bp-ex-stat">
            <span>Faixa etária</span>
            <strong><?= esc($ageLabels[$exercise['age_group']] ?? $exercise['age_group']) ?></strong>
        </div>
        <div class="bp-ex-stat">
            <span>Intensidade</span>
            <strong><?= esc($intensityLabels[$exercise['intensity']] ?? $exercise['intensity']) ?></strong>
        </div>
        <div class="bp-ex-stat">
            <span>Duração</span>
            <strong><?= esc($exercise['duration_min'] ?? '-') ?> min</strong>
        </div>
        <div class="bp-ex-stat">
            <span>Jogadores</span>
            <strong><?= esc($exercise['players_min'] ?? '-') ?> / <?= esc($exercise['players_max'] ?? '-') ?></strong>
        </div>
        <div class="bp-ex-stat">
            <span>Materiais</span>
            <strong><?= esc($exercise['materials'] ?? '-') ?></strong>
        </div>
        <div class="bp-ex-stat">
            <span>Vídeo</span>
            <?php if (!empty($exercise['video_url'])): ?>
                <strong><a href="<?= esc($exercise['video_url']) ?>" target="_blank" rel="noopener">Abrir link</a></strong>
            <?php else: ?>
                <strong>-</strong>
            <?php endif; ?>
        </div>
    </div>

    <div class="bp-ex-section">
        <h2>Descrição</h2>
        <div class="bp-ex-description">
            <?= esc($exercise['description'] ?? '') ?>
        </div>
    </div>
</div>

<style>
.bp-ex-header {display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:16px;}
.bp-ex-subtitle {color:var(--muted); font-size:14px;}
.bp-ex-actions {display:flex; gap:8px; flex-wrap:wrap;}
.bp-ex-grid {display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin:16px 0 8px;}
.bp-ex-stat {border:1px solid var(--border); border-radius:12px; padding:10px 12px; background:var(--surface); display:flex; flex-direction:column; gap:4px;}
.bp-ex-stat span {font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:.04em;}
.bp-ex-stat strong {font-size:15px;}
.bp-ex-section {margin-top:20px;}
.bp-ex-section h2 {margin-bottom:10px; font-size:18px;}
.bp-ex-description {border:1px dashed var(--border); border-radius:12px; padding:14px; background:#fff; white-space:pre-wrap;}
</style>
<?= $this->endSection() ?>
