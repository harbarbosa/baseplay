<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:760px;">
    <h1>Nova prancheta tÃ¡tica</h1>
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

        <div class="form-group">
            <label for="title">TÃ­tulo</label>
            <input id="title" name="title" type="text" value="<?= esc(old('title')) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">DescriÃ§Ã£o</label>
            <textarea id="description" name="description" rows="4"><?= esc(old('description')) ?></textarea>
        </div>

        <button type="submit">Criar prancheta</button>
        <a href="<?= base_url('/tactical-boards') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
