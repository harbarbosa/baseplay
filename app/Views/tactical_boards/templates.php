<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;">
        <div>
            <h1 style="margin:0;">Modelos de prancheta</h1>
            <p style="color:var(--muted); margin:6px 0 0;">Escolha um modelo para acelerar a criação.</p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <?php if (has_permission('templates.manage')): ?>
                <a href="<?= base_url('/tactical-boards/templates/create') ?>" class="button">Novo modelo</a>
            <?php endif; ?>
            <a href="<?= base_url('/tactical-boards/create') ?>" class="button secondary">Nova prancheta</a>
        </div>
    </div>

    <form method="get" action="<?= base_url('/tactical-boards/templates') ?>" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
        <select name="field_type">
            <option value="">Tipo de campo</option>
            <?php foreach (['full' => 'Campo inteiro', 'half_bottom_goal' => 'Meio campo (gol embaixo)', 'half_top_goal' => 'Meio campo (gol em cima)'] as $value => $label): ?>
                <option value="<?= esc($value) ?>" <?= ($filters['field_type'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="tag" placeholder="Filtrar por tag" value="<?= esc($filters['tag'] ?? '') ?>">
        <button type="submit">Filtrar</button>
        <a href="<?= base_url('/tactical-boards/templates') ?>" class="button secondary">Limpar</a>
    </form>

    <?php if (empty($templates)): ?>
        <div class="bp-empty-state">
            <strong>Nenhum modelo encontrado</strong>
            <div>Crie um novo ou ajuste os filtros.</div>
        </div>
    <?php else: ?>
        <div style="display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
            <?php foreach ($templates as $template): ?>
                <div class="bp-card" style="padding:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:6px;">
                        <div style="font-weight:600;"><?= esc($template['title']) ?></div>
                        <?php if (empty($template['is_active'])): ?>
                            <span class="bp-badge bp-badge-warn">Inativo</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($template['description'])): ?>
                        <div style="color:var(--muted); font-size:13px; margin-bottom:8px;"><?= esc($template['description']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($template['tags'])): ?>
                        <div style="font-size:12px; color:var(--muted); margin-bottom:10px;">Tags: <?= esc($template['tags']) ?></div>
                    <?php endif; ?>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <?php if (!empty($template['is_active'])): ?>
                            <a href="<?= base_url('/tactical-boards/create?template_id=' . (int) $template['id']) ?>" class="bp-btn-primary">Usar modelo</a>
                        <?php else: ?>
                            <span class="bp-btn-secondary" style="opacity:0.6; cursor:not-allowed;">Modelo inativo</span>
                        <?php endif; ?>
                        <?php if (has_permission('templates.manage')): ?>
                            <a href="<?= base_url('/tactical-boards/templates/' . (int) $template['id'] . '/editor') ?>" class="bp-btn-ghost">Editar no editor</a>
                            <a href="<?= base_url('/tactical-boards/templates/' . (int) $template['id'] . '/edit') ?>" class="bp-btn-secondary">Editar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
