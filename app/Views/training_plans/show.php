<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$blockTypeLabels = [
    'warmup' => 'Aquecimento',
    'technical' => 'Técnico',
    'tactical' => 'Tático',
    'physical' => 'Físico',
    'small_sided' => 'Jogo reduzido',
    'match' => 'Coletivo',
    'other' => 'Outro',
];
?>
<div class="card">
    <h1><?= esc($plan['title']) ?></h1>
    <p><strong>Equipe:</strong> <?= esc($plan['team_name'] ?? '-') ?></p>
    <p><strong>Categoria:</strong> <?= esc($plan['category_name'] ?? '-') ?></p>
    <p><strong>Data:</strong> <?= esc(format_date_br($plan['planned_date'] ?? null)) ?></p>
    <p><strong>Objetivo:</strong> <?= esc($plan['goal'] ?? '-') ?></p>
    <p><strong>Status:</strong> <?= esc(enum_label($plan['status'], 'status')) ?></p>
    <p><strong>Total:</strong> <?= esc($plan['total_duration_min'] ?? 0) ?> min</p>

    <hr style="margin:16px 0;">

    <h3>Blocos</h3>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tipo</th>
                <th>Título</th>
                <th>Duração</th>
                <th>Exercício</th>
                <th>Mídia</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($blocks as $block): ?>
            <tr>
                <td><?= esc($block['order_index']) ?></td>
                <td><?= esc($blockTypeLabels[$block['block_type']] ?? $block['block_type']) ?></td>
                <td><?= esc($block['title']) ?></td>
                <td><?= esc($block['duration_min']) ?> min</td>
                <td><?= esc($block['exercise_id'] ?? '-') ?></td>
                <td>
                    <?php if (!empty($block['media_url'])): ?>
                        <a href="<?= esc($block['media_url']) ?>" target="_blank" rel="noopener">Abrir URL</a>
                    <?php elseif (!empty($block['media_path'])): ?>
                        <a href="<?= base_url('/training-plan-blocks/' . $block['id'] . '/media') ?>">Baixar arquivo</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (has_permission('training_plans.delete')): ?>
                        <a href="<?= base_url('/training-plan-blocks/' . $block['id'] . '/delete') ?>">Excluir</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (has_permission('training_plans.update')): ?>
        <h3 style="margin-top:16px;">Adicionar bloco</h3>
        <form method="post" action="<?= base_url('/training-plans/' . $plan['id'] . '/blocks') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <select name="block_type">
                    <?php foreach (array_keys($blockTypeLabels) as $type): ?>
                        <option value="<?= esc($type) ?>"><?= esc($blockTypeLabels[$type]) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="title" placeholder="Título">
                <input type="number" name="duration_min" placeholder="Min">
                <select name="exercise_id">
                    <option value="">Exercício (opcional)</option>
                    <?php foreach ($exercises as $exercise): ?>
                        <option value="<?= esc($exercise['id']) ?>"><?= esc($exercise['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="order_index" placeholder="#">
                <input type="text" name="instructions" placeholder="Instruções">
                <input type="url" name="media_url" placeholder="URL do vídeo (opcional)" style="min-width:260px;">
                <input type="file" name="media_file" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.webm,.mov">
                <button type="submit">Adicionar</button>
            </div>
            <small style="display:block; margin-top:8px; color:var(--muted);">
                Você pode anexar PDF/foto/vídeo (até 20MB) ou informar uma URL.
            </small>
        </form>
    <?php endif; ?>

    <div style="margin-top:16px;">
        <a href="<?= base_url('/training-plans') ?>" class="button secondary">Voltar</a>
    </div>
</div>
<?= $this->endSection() ?>
