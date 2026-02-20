<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Nova equipe</h1>
    <form method="post" action="<?= base_url('/teams') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name')) ?>" required>
        </div>
        <div class="form-group">
            <label for="short_name">Apelido</label>
            <input id="short_name" name="short_name" type="text" value="<?= esc(old('short_name')) ?>">
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <input id="description" name="description" type="text" value="<?= esc(old('description')) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <button type="submit">Criar</button>
        <a href="<?= base_url('/teams') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
