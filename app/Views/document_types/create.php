<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:700px;">
    <h1>Novo tipo de documento</h1>
    <form method="post" action="<?= base_url('/document-types') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" value="<?= esc(old('name')) ?>">
        </div>

        <div class="form-group">
            <label>Exige vencimento</label>
            <select name="requires_expiration">
                <option value="0" <?= old('requires_expiration', '0') === '0' ? 'selected' : '' ?>>Não</option>
                <option value="1" <?= old('requires_expiration') === '1' ? 'selected' : '' ?>>Sim</option>
            </select>
        </div>

        <div class="form-group">
            <label>Dias padrão de validade</label>
            <input type="number" name="default_valid_days" value="<?= esc(old('default_valid_days')) ?>">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/document-types') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
