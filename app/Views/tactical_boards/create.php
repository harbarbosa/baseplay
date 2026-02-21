<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:760px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
        <h1 style="margin:0;">Nova prancheta tática</h1>
        <?php if (has_permission('templates.view')): ?>
            <a href="<?= base_url('/tactical-boards/templates') ?>" class="button secondary">Ver modelos</a>
        <?php endif; ?>
    </div>
    <form method="post" action="<?= base_url('/tactical-boards') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="team_id">Equipe</label>
            <select id="team_id" name="team_id" onchange="window.location='<?= base_url('/tactical-boards/create') ?>?team_id=' + this.value" required>
                <option value="">Selecione</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= esc($team['id']) ?>" <?= ($team_id ?? old('team_id')) == $team['id'] ? 'selected' : '' ?>>
                        <?= esc($team['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($templates)): ?>
            <div class="form-group">
                <label for="template_id">Modelo</label>
                <select id="template_id" name="template_id">
                    <option value="">Criar em branco</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= esc($template['id']) ?>" <?= (old('template_id') ?: $template_id ?? '') == $template['id'] ? 'selected' : '' ?>>
                            <?= esc($template['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:var(--muted); display:block; margin-top:6px;">Selecione um modelo para iniciar com jogadores e elementos prontos.</small>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="title">Título</label>
            <input id="title" name="title" type="text" value="<?= esc(old('title')) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea id="description" name="description" rows="4"><?= esc(old('description')) ?></textarea>
        </div>

        <button type="submit">Criar prancheta</button>
        <a href="<?= base_url('/tactical-boards') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>