<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card">
    <h1>Novo papel</h1>
    <form method="post" action="<?= base_url('/admin/roles') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="<?= esc(old('name')) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <input id="description" name="description" type="text" value="<?= esc(old('description')) ?>">
        </div>
        <div class="form-group">
            <label>Permissões</label>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:8px;">
                <?php foreach ($permissions as $permission): ?>
                    <label style="display:flex; gap:8px; align-items:center;">
                        <input type="checkbox" name="permissions[]" value="<?= esc($permission['id']) ?>">
                        <?= esc($permission['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit">Criar</button>
        <a href="<?= base_url('/admin/roles') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>

