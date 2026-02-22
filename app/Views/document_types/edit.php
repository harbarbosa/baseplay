<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="card" style="max-width:700px;">
    <h1>Editar tipo de documento</h1>
    <form method="post" action="<?= base_url('/document-types/' . $type['id'] . '/update') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" value="<?= esc(old('name', $type['name'])) ?>">
        </div>

        <div class="form-group">
            <label>Exige vencimento</label>
            <select name="requires_expiration">
                <option value="0" <?= (string) old('requires_expiration', (string) $type['requires_expiration']) === '0' ? 'selected' : '' ?>>Não</option>
                <option value="1" <?= (string) old('requires_expiration', (string) $type['requires_expiration']) === '1' ? 'selected' : '' ?>>Sim</option>
            </select>
        </div>

        <div class="form-group">
            <label>Dias padrão de validade</label>
            <input type="number" name="default_valid_days" value="<?= esc(old('default_valid_days', $type['default_valid_days'])) ?>">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_required" value="1" <?= (string) old('is_required', (string) ($type['is_required'] ?? 0)) === '1' ? 'checked' : '' ?>>
                Obrigatório para todas as categorias
            </label>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= old('status', $type['status']) === 'active' ? 'selected' : '' ?>>Ativo</option>
                <option value="inactive" <?= old('status', $type['status']) === 'inactive' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>

        <button type="submit">Salvar</button>
        <a href="<?= base_url('/document-types') ?>" class="button secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
