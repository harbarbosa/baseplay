<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Editar equipe</h1>
    <form method="post" action="<?= base_url('/teams/' . $team['id'] . '/update') ?>">
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
        <button type="submit">Salvar</button>
        <a href="<?= base_url('/teams/' . $team['id']) ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
