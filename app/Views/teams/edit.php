<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Editar equipe</h1>
    <form method="post" action="<?= base_url('/teams/' . $team['id'] . '/update') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name') ?? $team['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="short_name">Apelido</label>
            <input id="short_name" name="short_name" type="text" value="<?= esc(old('short_name') ?? $team['short_name']) ?>">
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <input id="description" name="description" type="text" value="<?= esc(old('description') ?? $team['description']) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= (old('status') ?? $team['status']) === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= (old('status') ?? $team['status']) === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="primary_color">Cor primária</label>
            <input id="primary_color" name="primary_color" type="color" class="bp-color-input" value="<?= esc(old('primary_color') ?? ($team['primary_color'] ?? '#7A1126')) ?>">
        </div>
        <div class="form-group">
            <label for="secondary_color">Cor secundária</label>
            <input id="secondary_color" name="secondary_color" type="color" class="bp-color-input" value="<?= esc(old('secondary_color') ?? ($team['secondary_color'] ?? '#F4D6DB')) ?>">
        </div>
        <div class="form-group">
            <label for="team_logo">Logo da equipe</label>
            <input id="team_logo" name="team_logo" type="file" accept="image/*">
        </div>
        <?php if (!empty($team['logo_path'])): ?>
            <div class="form-group">
                <img src="<?= base_url($team['logo_path']) ?>" alt="Logo atual" style="max-width:220px; max-height:120px; width:auto; height:auto; object-fit:contain;">
            </div>
        <?php endif; ?>

        <div class="form-group" style="margin-top:16px;">
            <label>Admin da equipe</label>
            <?php if (!empty($adminUser)): ?>
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div style="min-width:220px;">
                        <small style="color:var(--muted);">Nome</small>
                        <div><?= esc($adminUser['name'] ?? '-') ?></div>
                    </div>
                    <div style="min-width:220px;">
                        <small style="color:var(--muted);">Email</small>
                        <div><?= esc($adminUser['email'] ?? '-') ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div style="color:var(--muted);">Nenhum admin vinculado.</div>
            <?php endif; ?>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/teams/' . $team['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
